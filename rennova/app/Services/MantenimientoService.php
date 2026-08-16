<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MantenimientoService
{
    /**
     * Verify if there is sufficient stock to approve a preventive maintenance.
     *
     * Checks the maintenance kit (prioritizing machine-specific kit, falling back
     * to machine-type kit) against current available stock for each input.
     *
     * @param  int  $mantenimientoId  The maintenance order ID to verify
     * @return array{
     *     puede_aprobar: bool,
     *     insuficientes: array<array{insumo_id: int, insumo: string, requerido: float, disponible: float, faltante: float}>,
     *     kit: array<array{insumo_id: int, nombre: string, cantidad_requerida: float, stock_disponible: float, es_obligatorio: bool}>
     * }
     */
    public function verificarStockParaAprobacion($mantenimientoId)
    {
        $mantenimiento = Mantenimiento::with(['maquinaria.tipoMaquinaria'])->findOrFail($mantenimientoId);

        // Obtener kit de insumos requeridos (prioriza kit por maquinaria)
        $kit = KitMantenimientoPreventivo::where('id_maquinaria', $mantenimiento->id_maquinaria)
            ->whereNull('deleted_at')
            ->with('insumo')
            ->get();

        if ($kit->isEmpty()) {
            $kit = KitMantenimientoPreventivo::where('id_tipo_maquinaria', $mantenimiento->maquinaria->id_tipo_maquinaria)
                ->whereNull('deleted_at')
                ->with('insumo')
                ->get();
        }

        $insuficientes = [];
        $kitCompleto = [];

        foreach ($kit as $item) {
            $stockDisponible = $item->insumo->stock; // Usa el accessor que calcula desde movimientos

            $kitCompleto[] = [
                'insumo_id' => $item->id_insumo,
                'nombre' => $item->insumo->nombre,
                'cantidad_requerida' => $item->cantidad_requerida,
                'stock_disponible' => $stockDisponible,
                'es_obligatorio' => $item->es_obligatorio,
            ];

            if ($stockDisponible < $item->cantidad_requerida) {
                $insuficientes[] = [
                    'insumo_id' => $item->id_insumo,
                    'insumo' => $item->insumo->nombre,
                    'requerido' => $item->cantidad_requerida,
                    'disponible' => $stockDisponible,
                    'faltante' => $item->cantidad_requerida - $stockDisponible,
                ];
            }
        }

        return [
            'puede_aprobar' => empty($insuficientes),
            'insuficientes' => $insuficientes,
            'kit' => $kitCompleto,
        ];
    }

    /**
     * Approve a preventive maintenance order: verify stock and change status.
     *
     * Runs inside a DB transaction. Only orders in 'programado' state can be approved.
     *
     * @param  int  $mantenimientoId  The maintenance order ID to approve
     * @return array{success: bool, mantenimiento?: \App\Models\Mantenimiento, message?: string, insumos_insuficientes?: array}
     */
    public function aprobarMantenimiento(int $mantenimientoId): array
    {
        DB::beginTransaction();

        try {
            $mantenimiento = Mantenimiento::lockForUpdate()->findOrFail($mantenimientoId);

            if ($mantenimiento->estado !== 'programado') {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Solo se pueden aprobar órdenes en estado "programado"',
                ];
            }

            $verificacion = $this->verificarStockParaAprobacion($mantenimientoId);

            if (! $verificacion['puede_aprobar']) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'No hay stock suficiente para aprobar esta orden',
                    'insumos_insuficientes' => $verificacion['insuficientes'],
                ];
            }

            $mantenimiento->update([
                'estado' => 'en curso',
            ]);

            DB::commit();

            Log::info('Orden de mantenimiento aprobada', [
                'mantenimiento_id' => $mantenimientoId,
                'maquinaria_id' => $mantenimiento->id_maquinaria,
            ]);

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error aprobando mantenimiento', [
                'mantenimiento_id' => $mantenimientoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al aprobar la orden: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Complete a maintenance order: validate stock, deduct inputs, calculate costs, update snapshot.
     *
     * Runs inside a DB transaction. Fails if any input has insufficient stock.
     *
     * @param  int  $mantenimientoId  The maintenance order ID to complete
     * @param  array  $insumos  Array of inputs used: [{id_insumo, cantidad_utilizada, costo_unitario?}]
     * @param  float  $costoManoObra  Labor cost for this maintenance
     * @return array{success: bool, mantenimiento?: \App\Models\Mantenimiento, costo_total?: float, message?: string}
     */
    public function completarMantenimiento(int $mantenimientoId, array $insumos, float $costoManoObra = 0): array
    {
        DB::beginTransaction();

        try {
            $mantenimiento = Mantenimiento::with('maquinaria')->lockForUpdate()->findOrFail($mantenimientoId);

            if ($mantenimiento->estado === 'completado') {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Este mantenimiento ya está completado',
                ];
            }

            $costoInsumos = 0;

            foreach ($insumos as $insumoData) {
                $insumo = Insumo::findOrFail($insumoData['id_insumo']);
                $cantidadUsada = (float) ($insumoData['cantidad_utilizada'] ?? 0);

                $stockDisponible = InventarioService::stockDisponible($insumo->id_insumo);

                if ($stockDisponible < $cantidadUsada) {
                    DB::rollBack();

                    return [
                        'success' => false,
                        'message' => "Stock insuficiente para {$insumo->nombre}. Disponible: {$stockDisponible}, requerido: {$cantidadUsada}",
                    ];
                }

                $resultadoSalida = InventarioService::registrarSalida(
                    $insumo->id_insumo,
                    $cantidadUsada,
                    "Mantenimiento ID: {$mantenimientoId}",
                    now()->toDateString()
                );

                $costoUnitario = $cantidadUsada > 0
                    ? $resultadoSalida['costo_total'] / $cantidadUsada
                    : 0;
                $subtotal = $resultadoSalida['costo_total'];

                MantenimientoInsumo::create([
                    'id_mantenimiento' => $mantenimientoId,
                    'id_insumo' => $insumo->id_insumo,
                    'cantidad_utilizada' => $cantidadUsada,
                    'costo_unitario' => $costoUnitario,
                    'subtotal' => $subtotal,
                ]);

                $costoInsumos += $subtotal;
            }

            $costoTotal = $costoInsumos + $costoManoObra;

            $mantenimiento->update([
                'estado' => 'completado',
                'fecha_fin' => now()->toDateString(),
                'costo_total' => $costoTotal,
                'costo_mano_obra' => $costoManoObra,
                'toneladas_snapshot' => $mantenimiento->maquinaria->toneladas_acumuladas,
            ]);

            DB::commit();

            Log::info('Mantenimiento completado', [
                'mantenimiento_id' => $mantenimientoId,
                'costo_total' => $costoTotal,
                'costo_insumos' => $costoInsumos,
                'costo_mano_obra' => $costoManoObra,
                'snapshot' => $mantenimiento->toneladas_snapshot,
            ]);

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
                'costo_total' => $costoTotal,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completando mantenimiento', [
                'mantenimiento_id' => $mantenimientoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al completar el mantenimiento: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get the preventive maintenance kit for a given machine type.
     *
     * @param  int  $tipoMaquinariaId  The machine type ID
     * @return \Illuminate\Database\Eloquent\Collection Collection of KitMantenimientoPreventivo with insumo relation
     */
    public function obtenerKitPreventivo($tipoMaquinariaId)
    {
        return KitMantenimientoPreventivo::where('id_tipo_maquinaria', $tipoMaquinariaId)
            ->with('insumo')
            ->get();
    }

    /**
     * Create a new maintenance order.
     *
     * @param  array  $datos  Validated data: id_maquinaria, id_tipo_mantenimiento, fecha_inicio, fecha_programada?, estado
     */
    public function crearMantenimiento(array $datos): Mantenimiento
    {
        return Mantenimiento::create([
            'id_maquinaria' => $datos['id_maquinaria'],
            'id_tipo_mantenimiento' => $datos['id_tipo_mantenimiento'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_programada' => $datos['fecha_programada'] ?? null,
            'estado' => $datos['estado'],
        ]);
    }

    /**
     * Complete a maintenance order with FIFO-based input consumption.
     *
     * Performs inside a DB transaction:
     * - Updates the order with fecha_fin, costo_total, estado='completado'
     * - For each input used: validates stock, exits via InventarioService::registrarSalida (FIFO)
     * - Records each input in the mantenimiento_insumos table
     *
     * @param  int  $mantenimientoId  The maintenance order ID
     * @param  string  $fechaFin  Completion date (Y-m-d)
     * @param  float  $costoBase  Base labor/additional cost
     * @param  array  $insumos  Array of ['id_insumo' => int, 'cantidad' => float]
     * @param  string  $tipoMantenimiento  'Preventivo' or 'Correctivo' for the movement reason
     * @return array{costo_total: float, costo_insumos: float}
     *
     * @throws \Exception If stock is insufficient or a DB error occurs
     */
    public function completarMantenimientoConFifo(int $mantenimientoId, string $fechaFin, float $costoBase, array $insumos, string $tipoMantenimiento = 'Preventivo'): array
    {
        $orden = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($mantenimientoId);

        $insumosValidados = [];
        $costoInsumos = 0;

        foreach ($insumos as $insumo) {
            if (empty($insumo['id_insumo']) || empty($insumo['cantidad'])) {
                continue;
            }

            $cantidad = floatval($insumo['cantidad']);

            $stockDisponible = InventarioService::stockDisponible($insumo['id_insumo']);
            if ($stockDisponible < $cantidad) {
                $nombreInsumo = Insumo::find($insumo['id_insumo'])->nombre ?? 'ID '.$insumo['id_insumo'];
                throw new \Exception("Stock insuficiente para {$nombreInsumo}. Disponible: {$stockDisponible}, Requerido: {$cantidad}");
            }

            $resultadoSimulado = DB::selectOne(
                'SELECT * FROM calcular_costo_fifo(?, ?)',
                [$insumo['id_insumo'], $cantidad]
            );

            $costoInsumos += $resultadoSimulado->v_costo_total;
            $insumosValidados[] = $insumo;
        }

        $costoTotal = $costoBase + $costoInsumos;

        DB::beginTransaction();

        try {
            $orden->fecha_fin = $fechaFin;
            $orden->costo_total = $costoTotal;
            $orden->estado = 'completado';
            $orden->save();

            foreach ($insumosValidados as $insumo) {
                $cantidad = floatval($insumo['cantidad']);
                $motivo = "Mantenimiento {$tipoMantenimiento} - Orden #".$orden->id_mantenimiento;

                $resultadoSalida = InventarioService::registrarSalida(
                    $insumo['id_insumo'],
                    $cantidad,
                    $motivo,
                    $fechaFin
                );

                $costoRealInsumo = $resultadoSalida['costo_total'];

                DB::table('mantenimiento_insumos')->insert([
                    'id_mantenimiento' => $orden->id_mantenimiento,
                    'id_insumo' => $insumo['id_insumo'],
                    'cantidad_utilizada' => $cantidad,
                    'costo_unitario' => $costoRealInsumo / $cantidad,
                    'subtotal' => $costoRealInsumo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Mantenimiento completado con FIFO', [
                'mantenimiento_id' => $mantenimientoId,
                'costo_total' => $costoTotal,
                'costo_insumos' => $costoInsumos,
            ]);

            return [
                'costo_total' => $costoTotal,
                'costo_insumos' => $costoInsumos,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * List maintenance orders, marking expired scheduled ones first.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Mantenimiento>
     */
    public function listarMantenimientos(?string $busqueda = null): Collection
    {
        Mantenimiento::where('estado', 'programado')
            ->whereNotNull('fecha_programada')
            ->where('fecha_programada', '<', now()->toDateString())
            ->update(['estado' => 'vencido']);

        $query = Mantenimiento::with(['maquinaria', 'tipoMantenimiento']);

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('estado', 'ILIKE', '%'.$busq.'%')
                    ->orWhereRaw('CAST(costo_total AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereHas('maquinaria', function ($qm) use ($busq) {
                        $qm->where('modelo', 'ILIKE', '%'.$busq.'%');
                    })
                    ->orWhereHas('tipoMantenimiento', function ($qt) use ($busq) {
                        $qt->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_mantenimiento', 'desc')->get();
    }

    /**
     * Get the preventive maintenance kit for a specific machine and maintenance type.
     *
     * @return array<int, array{nombre: string, cantidad_requerida: float}>
     */
    public function obtenerKitPreventivoParaMaquinaria(int $idMaquinaria, int $idTipoMantenimiento): array
    {
        $tipo = TipoMantenimiento::find($idTipoMantenimiento);

        if (! $tipo || ! str_contains(strtolower($tipo->nombre), 'preventivo')) {
            return [];
        }

        return KitMantenimientoPreventivo::where('kit_mantenimiento_preventivo.id_maquinaria', $idMaquinaria)
            ->join('insumos', 'kit_mantenimiento_preventivo.id_insumo', '=', 'insumos.id_insumo')
            ->select('insumos.nombre', 'kit_mantenimiento_preventivo.cantidad_requerida')
            ->get()
            ->toArray();
    }

    /**
     * Get active machines (not decommissioned) ordered by model.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Maquinaria>
     */
    public function obtenerMaquinariasActivas(): Collection
    {
        return Maquinaria::where('estado', '!=', 'dado_de_baja')->orderBy('modelo')->get();
    }

    /**
     * Get all maintenance types ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, TipoMantenimiento>
     */
    public function obtenerTiposMantenimiento(): Collection
    {
        return TipoMantenimiento::orderBy('nombre')->get();
    }

    /**
     * Get a maintenance order ready for editing.
     */
    public function obtenerMantenimientoParaEditar(int $id): Mantenimiento
    {
        return Mantenimiento::findOrFail($id);
    }

    /**
     * Save (create or update) a maintenance order and mark related notification as actioned.
     *
     * Runs inside a DB transaction.
     *
     * @param  array  $datos  Validated data
     * @return array{success: bool, mantenimiento?: Mantenimiento, tipoNombre: string, maquinaNombre: string, message?: string}
     */
    public function guardarMantenimiento(array $datos, ?int $mantenimientoId = null, ?int $usuarioId = null): array
    {
        DB::beginTransaction();

        try {
            if ($mantenimientoId) {
                $mantenimiento = Mantenimiento::findOrFail($mantenimientoId);
                $mantenimiento->update([
                    'id_maquinaria' => $datos['id_maquinaria'],
                    'id_tipo_mantenimiento' => $datos['id_tipo_mantenimiento'],
                    'fecha_inicio' => $datos['fecha_inicio'],
                    'fecha_programada' => $datos['fecha_programada'] ?? null,
                    'estado' => $datos['estado'],
                ]);
            } else {
                $mantenimiento = Mantenimiento::create([
                    'id_maquinaria' => $datos['id_maquinaria'],
                    'id_tipo_mantenimiento' => $datos['id_tipo_mantenimiento'],
                    'fecha_inicio' => $datos['fecha_inicio'],
                    'fecha_programada' => $datos['fecha_programada'] ?? null,
                    'estado' => $datos['estado'],
                ]);
            }

            if ($usuarioId) {
                $this->marcarNotificacionComoAccionada($mantenimiento->id_mantenimiento, $usuarioId);
            }

            $tipoNombre = TipoMantenimiento::find($datos['id_tipo_mantenimiento'])->nombre ?? 'Mantenimiento';
            $maquinaNombre = Maquinaria::find($datos['id_maquinaria'])->modelo ?? '';

            DB::commit();

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
                'tipoNombre' => $tipoNombre,
                'maquinaNombre' => $maquinaNombre,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando mantenimiento', [
                'mantenimiento_id' => $mantenimientoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'tipoNombre' => 'Mantenimiento',
                'maquinaNombre' => '',
                'message' => 'Error al guardar el mantenimiento: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Soft-delete a maintenance order.
     */
    public function eliminarMantenimiento(int $id): bool
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->delete();

        return true;
    }

    /**
     * Confirm a scheduled maintenance order and mark its notification as actioned.
     *
     * Runs inside a DB transaction.
     *
     * @return array{success: bool, mantenimiento?: Mantenimiento, message?: string}
     */
    public function confirmarMantenimiento(int $id, ?int $usuarioId = null): array
    {
        DB::beginTransaction();

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            if ($mantenimiento->estado !== 'programado') {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Solo se pueden confirmar mantenimientos en estado programado.',
                ];
            }

            $mantenimiento->update([
                'estado' => 'en curso',
                'fecha_inicio' => now()->toDateString(),
            ]);

            if ($usuarioId) {
                $this->marcarNotificacionComoAccionada($mantenimiento->id_mantenimiento, $usuarioId);
            }

            DB::commit();

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
                'message' => "Mantenimiento #{$id} confirmado y en curso.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirmando mantenimiento', [
                'mantenimiento_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al confirmar el mantenimiento: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Reprogram an expired maintenance order back to scheduled state.
     *
     * Runs inside a DB transaction.
     *
     * @return array{success: bool, mantenimiento?: Mantenimiento, message?: string}
     */
    public function reprogramarMantenimiento(int $id): array
    {
        DB::beginTransaction();

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            if ($mantenimiento->estado !== 'vencido') {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Solo se pueden reprogramar mantenimientos vencidos.',
                ];
            }

            $mantenimiento->update([
                'estado' => 'programado',
                'fecha_programada' => null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
                'message' => "Mantenimiento #{$id} reprogramado. Por favor, asigne una nueva fecha.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reprogramando mantenimiento', [
                'mantenimiento_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al reprogramar el mantenimiento: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validate that a scheduled date is within 7 days from the related notification.
     *
     * Used when editing an existing maintenance order.
     */
    public function validarFechaProgramadaEdicion(int $mantenimientoId, string $fechaProgramada): ?string
    {
        $notificacion = NotificacionSistema::where('mantenimiento_id', $mantenimientoId)
            ->where('tipo', 'umbral_alcanzado')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $notificacion) {
            return null;
        }

        $fechaNotificacion = $notificacion->created_at->toDateString();
        $fechaLimite = $notificacion->created_at->addDays(7)->toDateString();

        if ($fechaProgramada < $fechaNotificacion || $fechaProgramada > $fechaLimite) {
            return "La fecha programada debe estar entre {$fechaNotificacion} y {$fechaLimite} (dentro de los 7 días desde la notificación).";
        }

        return null;
    }

    /**
     * Validate that a scheduled date is within the next 7 days from today.
     *
     * Used when creating a new maintenance order without a previous notification.
     */
    public function validarFechaProgramadaNueva(string $fechaProgramada): ?string
    {
        $fechaHoy = now()->toDateString();
        $fechaLimite = now()->addDays(7)->toDateString();

        if ($fechaProgramada < $fechaHoy || $fechaProgramada > $fechaLimite) {
            return "La fecha programada debe estar entre {$fechaHoy} y {$fechaLimite} (dentro de los próximos 7 días).";
        }

        return null;
    }

    /**
     * Mark the current user's pending notification for a maintenance order as actioned.
     */
    public function marcarNotificacionComoAccionada(int $mantenimientoId, int $usuarioId): void
    {
        $notificacion = NotificacionSistema::where('user_id', $usuarioId)
            ->where('mantenimiento_id', $mantenimientoId)
            ->where('accionada', false)
            ->first();

        if ($notificacion) {
            NotificacionService::marcarComoAccionada($notificacion);
            Log::info("Notificación #{$notificacion->id} marcada como accionada para mantenimiento #{$mantenimientoId}");
        }
    }
}
