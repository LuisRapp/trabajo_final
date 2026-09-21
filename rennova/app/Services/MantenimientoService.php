<?php

namespace App\Services;

use App\Mail\MantenimientoOrdenGeneradaMail;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\PropuestaCompraMantenimiento;
use App\Models\PropuestaCompraMantenimientoInsumo;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use App\Notifications\MantenimientoProgramadoRecordatorio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class MantenimientoService
{
    private const CLIMA_VENTANA_HORAS = 72;

    private float $ultimoEnvioMail = 0.0;

    public function __construct(
        private readonly ClimaDecisionService $climaDecisionService,
        private readonly MantenimientoDocumentsService $documentsService,
    ) {}

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
     * List maintenance orders filtered by free-text search (read-only).
     *
     * El marcado de vencidos lo realiza el proceso automático programado
     * (marcarMantenimientosVencidos, cada 4 horas); el listado no muta estado.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Mantenimiento>
     */
    public function listarMantenimientos(?string $busqueda = null): Collection
    {
        $query = Mantenimiento::with(['maquinaria', 'tipoMantenimiento']);

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('estado', 'ILIKE', '%'.$busq.'%')
                    ->when(is_numeric($busq), fn ($q) => $q->orWhere('costo_total', (float) $busq))
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
     * Si un tipo de mantenimiento es preventivo.
     *
     * Regla única del dominio: el nombre del tipo contiene "preventivo".
     * Toda la aplicación (componentes, vistas y proceso automático) debe
     * resolver esta condición a través de este método.
     */
    public function esTipoPreventivo(TipoMantenimiento $tipo): bool
    {
        return str_contains(mb_strtolower($tipo->nombre), 'preventivo');
    }

    /**
     * Get the preventive maintenance kit for a specific machine and maintenance type.
     *
     * @return array<int, array{nombre: string, cantidad_requerida: float}>
     */
    public function obtenerKitPreventivoParaMaquinaria(int $idMaquinaria, int $idTipoMantenimiento): array
    {
        $tipo = TipoMantenimiento::find($idTipoMantenimiento);

        if (! $tipo || ! $this->esTipoPreventivo($tipo)) {
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

    // =========================================================================
    // Proceso automático de mantenimiento preventivo (PA-01)
    // =========================================================================

    /**
     * Maquinarias operativas con umbral configurado, candidatas a verificación.
     *
     * @return Collection<int, Maquinaria>
     */
    public function obtenerMaquinariasElegibles(?int $maquinariaId = null): Collection
    {
        return Maquinaria::query()
            ->with('tipoMaquinaria')
            ->whereNotNull('umbral_toneladas')
            ->whereIn('estado', ['operativa', 'activo'])
            ->when($maquinariaId, fn ($q) => $q->where('id_maquinaria', $maquinariaId))
            ->get();
    }

    /**
     * Tipo de mantenimiento preventivo configurado en el catálogo.
     */
    public function obtenerTipoPreventivo(): ?TipoMantenimiento
    {
        return TipoMantenimiento::query()
            ->whereLike('nombre', '%preventivo%')
            ->first();
    }

    /**
     * Caso de uso del proceso automático por umbral para una maquinaria.
     *
     * Si supera el umbral y no tiene orden abierta: resuelve la fecha por clima,
     * crea la orden en transacción (personal, stock del kit, propuesta de compra
     * y notificación interna) y envía el email con adjuntos después del commit.
     *
     * @param  Maquinaria  $maquinaria  Maquinaria a verificar
     * @param  TipoMantenimiento  $tipoPreventivo  Tipo preventivo del catálogo
     * @return array{generada: bool, motivo?: string, toneladas?: float, umbral?: float, mantenimiento?: Mantenimiento, programacion?: array, falta_stock?: bool, insumos?: array}
     *
     * @throws \Throwable Si falla la transacción de creación de la orden
     */
    public function procesarUmbralMaquinaria(Maquinaria $maquinaria, TipoMantenimiento $tipoPreventivo): array
    {
        $toneladasDesdeUltimo = $this->obtenerToneladasDesdeUltimoMantenimiento($maquinaria);
        $umbral = (float) $maquinaria->umbral_toneladas;

        if ($toneladasDesdeUltimo < $umbral) {
            return [
                'generada' => false,
                'motivo' => 'bajo_umbral',
                'toneladas' => $toneladasDesdeUltimo,
                'umbral' => $umbral,
            ];
        }

        if ($this->tieneOrdenAbierta($maquinaria)) {
            return ['generada' => false, 'motivo' => 'orden_abierta'];
        }

        $programacion = $this->resolverFechaProgramadaPorClima($maquinaria);

        [$faltaStock, $insumosConProblema, $propuesta, $mantenimiento] = DB::transaction(function () use ($maquinaria, $tipoPreventivo, $programacion, $toneladasDesdeUltimo) {
            $mantenimiento = $this->crearMantenimiento([
                'id_maquinaria' => $maquinaria->id_maquinaria,
                'id_tipo_mantenimiento' => $tipoPreventivo->id_tipo_mantenimiento,
                'fecha_inicio' => $programacion['fecha_programada']->toDateString(),
                'fecha_programada' => $programacion['fecha_programada']->toDateString(),
                'estado' => 'programado',
            ]);

            $asignacion = $this->asignarPersonalAutomatico(
                mantenimiento: $mantenimiento,
                fechaProgramada: $programacion['fecha_programada']
            );

            $verificacionStock = $this->verificarStockParaAprobacion($mantenimiento->id_mantenimiento);
            $faltaStock = ! $verificacionStock['puede_aprobar'];
            $insumosConProblema = $verificacionStock['insuficientes'];

            $propuesta = null;
            if ($faltaStock) {
                $propuesta = $this->crearPropuestaCompraMantenimiento($mantenimiento, $insumosConProblema);
            }

            $this->crearNotificacionInterna(
                mantenimiento: $mantenimiento,
                maquinaria: $maquinaria,
                toneladasDesdeUltimo: $toneladasDesdeUltimo,
                programacion: $programacion,
                asignacion: $asignacion
            );

            return [$faltaStock, $insumosConProblema, $propuesta, $mantenimiento];
        });

        $this->enviarCorreoOrdenConAdjuntos($mantenimiento, $propuesta);

        return [
            'generada' => true,
            'mantenimiento' => $mantenimiento,
            'programacion' => $programacion,
            'falta_stock' => $faltaStock,
            'insumos' => $insumosConProblema,
        ];
    }

    /**
     * Mantenimientos programados para hoy.
     *
     * @return Collection<int, Mantenimiento>
     */
    public function obtenerProgramadosDeHoy(): Collection
    {
        return Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])
            ->where('estado', 'programado')
            ->where('fecha_programada', now()->toDateString())
            ->get();
    }

    /**
     * Notificaciones de umbral pendientes de programar con fecha límite cercana.
     *
     * @param  string  $limiteAviso  Fecha límite máxima para el aviso
     * @return Collection<int, NotificacionSistema>
     */
    public function obtenerPendientesDeProgramar(string $limiteAviso): Collection
    {
        return NotificacionSistema::query()
            ->with(['mantenimiento.maquinaria', 'mantenimiento.tipoMantenimiento'])
            ->where('tipo', 'umbral_alcanzado')
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<=', $limiteAviso)
            ->where(function ($q) {
                $q->where('accionada', false)->orWhereNull('accionada');
            })
            ->whereHas('mantenimiento', function ($q) {
                $q->where('estado', 'programado')->whereNull('fecha_programada');
            })
            ->orderByDesc('created_at')
            ->get()
            ->unique('mantenimiento_id')
            ->values();
    }

    /**
     * Marca como vencidos los mantenimientos no confirmados cuya fecha pasó
     * y crea la notificación interna de respaldo para los usuarios configurados.
     *
     * @param  string  $hoy  Fecha de hoy (Y-m-d)
     * @return Collection<int, Mantenimiento> Mantenimientos marcados como vencidos
     */
    public function marcarMantenimientosVencidos(string $hoy): Collection
    {
        $vencidos = Mantenimiento::query()
            ->where('estado', 'programado')
            ->where('fecha_programada', '<', $hoy)
            ->get();

        if ($vencidos->isEmpty()) {
            return $vencidos;
        }

        DB::transaction(function () use ($vencidos) {
            foreach ($vencidos as $mantenimiento) {
                $mantenimiento->update(['estado' => 'vencido']);
                $this->crearNotificacionVencido($mantenimiento);

                Log::warning('Mantenimiento vencido', [
                    'id_mantenimiento' => $mantenimiento->id_mantenimiento,
                    'id_maquinaria' => $mantenimiento->id_maquinaria,
                    'fecha_programada' => $mantenimiento->fecha_programada,
                ]);
            }
        });

        return $vencidos;
    }

    /**
     * Envía el recordatorio de mantenimientos de hoy y pendientes de programar
     * por email a los usuarios configurados (con reintentos).
     *
     * @param  Collection<int, Mantenimiento>  $mantenimientosHoy
     * @param  Collection<int, NotificacionSistema>|null  $pendientesProgramar
     */
    public function enviarRecordatorioProgramados(Collection $mantenimientosHoy, ?Collection $pendientesProgramar = null): void
    {
        try {
            $pendientesProgramar = $pendientesProgramar ?? collect();

            if ($mantenimientosHoy->isEmpty() && $pendientesProgramar->isEmpty()) {
                return;
            }

            $idsUsuarios = app(NotificacionService::class)->cargarConfiguracionMantenimiento()['recordatorio'];

            if (empty($idsUsuarios)) {
                $correoAdmin = config('mail.admin_email', 'admin@example.com');
                $this->enviarConReintento(function () use ($correoAdmin, $mantenimientosHoy, $pendientesProgramar) {
                    $this->esperarParaEnviarMail();
                    Notification::route('mail', $correoAdmin)
                        ->notify(new MantenimientoProgramadoRecordatorio($mantenimientosHoy, $pendientesProgramar));
                });
                Log::info("Recordatorio de mantenimientos enviado a {$correoAdmin} (fallback)");

                return;
            }

            $usuarios = Usuario::whereIn('id', $idsUsuarios)->get();
            foreach ($usuarios as $usuario) {
                $this->enviarConReintento(function () use ($usuario, $mantenimientosHoy, $pendientesProgramar) {
                    $this->esperarParaEnviarMail();
                    $usuario->notify(new MantenimientoProgramadoRecordatorio($mantenimientosHoy, $pendientesProgramar));
                });
            }
            Log::info("Recordatorio de mantenimientos enviado a {$usuarios->count()} usuario(s)");
        } catch (\Throwable $e) {
            Log::error('Error enviando recordatorio de mantenimientos', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Toneladas procesadas por la maquinaria desde su último mantenimiento cerrado.
     */
    private function obtenerToneladasDesdeUltimoMantenimiento(Maquinaria $maquinaria): float
    {
        $ultimoMantenimiento = Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->whereNotNull('toneladas_snapshot')
            ->orderBy('fecha_fin', 'desc')
            ->first();

        if (! $ultimoMantenimiento) {
            return (float) $maquinaria->toneladas_acumuladas;
        }

        return (float) $maquinaria->toneladas_acumuladas - (float) $ultimoMantenimiento->toneladas_snapshot;
    }

    /**
     * Si la maquinaria ya tiene una orden de mantenimiento abierta.
     */
    private function tieneOrdenAbierta(Maquinaria $maquinaria): bool
    {
        return Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->whereIn('estado', ['programado', 'en curso'])
            ->exists();
    }

    /**
     * Resuelve la fecha programada del mantenimiento según el clima del lote.
     *
     * Regla:
     * - Si hay lluvia dentro de 72h -> usar ese dia exacto (se trabaja igual por clima).
     * - Si no hay lluvia o faltan datos -> fallback al dia siguiente.
     *
     * @return array{fecha_programada: Carbon, fuente: string, motivo: string, lote_id: ?int, lluvia_mm: ?float}
     */
    private function resolverFechaProgramadaPorClima(Maquinaria $maquinaria): array
    {
        $fallbackDate = now()->addDay()->startOfDay();
        $limite = now()->addHours(self::CLIMA_VENTANA_HORAS);

        $lote = Lote::query()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->whereHas('maquinarias', function ($q) use ($maquinaria) {
                $q->where('maquinarias.id_maquinaria', $maquinaria->id_maquinaria);
            })
            ->first();

        if (! $lote) {
            Log::warning('Fallback clima: maquinaria sin lote activo/en_proceso', [
                'maquinaria_id' => $maquinaria->id_maquinaria,
                'fallback_fecha' => $fallbackDate->toDateString(),
            ]);

            return [
                'fecha_programada' => $fallbackDate,
                'fuente' => 'fallback',
                'motivo' => 'sin_lote_asociado',
                'lote_id' => null,
                'lluvia_mm' => null,
            ];
        }

        $clima = $this->climaDecisionService->analizarYRecomendar($lote);
        $dias = $clima['pronostico'] ?? $clima['dias_detalle'] ?? [];

        if (! ($clima['success'] ?? false) || empty($dias)) {
            Log::warning('Fallback clima: sin datos validos de pronostico', [
                'maquinaria_id' => $maquinaria->id_maquinaria,
                'lote_id' => $lote->id_lote,
                'error' => $clima['error'] ?? null,
                'fallback_fecha' => $fallbackDate->toDateString(),
            ]);

            return [
                'fecha_programada' => $fallbackDate,
                'fuente' => 'fallback',
                'motivo' => 'sin_datos_clima',
                'lote_id' => $lote->id_lote,
                'lluvia_mm' => null,
            ];
        }

        foreach ($dias as $dia) {
            $fechaRaw = $dia['fecha'] ?? null;
            $fecha = $fechaRaw instanceof Carbon ? $fechaRaw->copy() : Carbon::parse((string) $fechaRaw);
            $mm = (float) ($dia['precipitacion_mm'] ?? 0);
            $razon = mb_strtolower((string) ($dia['razon'] ?? ''));

            if ($fecha->lt(now()->startOfDay()) || $fecha->gt($limite)) {
                continue;
            }

            if ($mm >= ClimaDecisionService::UMBRAL_LLUVIA || str_contains($razon, 'lluvia')) {
                return [
                    'fecha_programada' => $fecha->startOfDay(),
                    'fuente' => 'clima',
                    'motivo' => 'lluvia_detectada',
                    'lote_id' => $lote->id_lote,
                    'lluvia_mm' => $mm,
                ];
            }
        }

        Log::warning('Fallback clima: sin lluvia en ventana de 72h', [
            'maquinaria_id' => $maquinaria->id_maquinaria,
            'lote_id' => $lote->id_lote,
            'fallback_fecha' => $fallbackDate->toDateString(),
        ]);

        return [
            'fecha_programada' => $fallbackDate,
            'fuente' => 'fallback',
            'motivo' => 'sin_lluvia_72h',
            'lote_id' => $lote->id_lote,
            'lluvia_mm' => null,
        ];
    }

    /**
     * Asigna automáticamente personal disponible a la orden generada.
     *
     * Prioriza por rol (mantenimiento -> administrativo) y luego cualquier
     * empleado activo disponible en la fecha programada.
     *
     * @return array{empleado_id: ?int, rol_origen: ?string, nombre?: string}
     */
    private function asignarPersonalAutomatico(Mantenimiento $mantenimiento, Carbon $fechaProgramada): array
    {
        $fecha = $fechaProgramada->toDateString();

        $empleado = $this->buscarEmpleadoDisponiblePorRol('mantenimiento', $fecha);
        $origen = 'mantenimiento';

        if (! $empleado) {
            $empleado = $this->buscarEmpleadoDisponiblePorRol('administrativo', $fecha);
            $origen = 'administrativo';
        }

        if (! $empleado) {
            $empleado = $this->buscarEmpleadoDisponibleSinFiltro($fecha);
            $origen = 'fallback';
        }

        if (! $empleado) {
            Log::warning('No se encontro personal disponible para mantenimiento', [
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'fecha_programada' => $fecha,
            ]);

            return [
                'empleado_id' => null,
                'rol_origen' => null,
            ];
        }

        $mantenimiento->empleados()->syncWithoutDetaching([
            $empleado->id_empleado => ['rol_origen' => $origen],
        ]);

        return [
            'empleado_id' => (int) $empleado->id_empleado,
            'rol_origen' => $origen,
            'nombre' => trim($empleado->apellido.', '.$empleado->nombre),
        ];
    }

    /**
     * Empleado activo y disponible en la fecha, filtrado por rol laboral.
     */
    private function buscarEmpleadoDisponiblePorRol(string $keyword, string $fecha): ?Empleado
    {
        $ocupados = $this->idsEmpleadosOcupados($fecha);

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->whereHas('rolLaboral', function ($q) use ($keyword) {
                $q->whereLike('nombre', '%'.$keyword.'%');
            })
            ->when(! empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    /**
     * Empleado activo y disponible en la fecha, sin filtro de rol.
     */
    private function buscarEmpleadoDisponibleSinFiltro(string $fecha): ?Empleado
    {
        $ocupados = $this->idsEmpleadosOcupados($fecha);

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->when(! empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    /**
     * @return array<int, int>
     */
    private function idsEmpleadosOcupados(string $fecha): array
    {
        return DB::table('mantenimiento_empleado as me')
            ->join('mantenimientos as m', 'm.id_mantenimiento', '=', 'me.id_mantenimiento')
            ->whereIn('m.estado', ['programado', 'en curso'])
            ->whereNull('m.deleted_at')
            ->whereDate('m.fecha_programada', $fecha)
            ->pluck('me.id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Crea o regenera la propuesta de compra de insumos para la orden.
     *
     * Los ítems anteriores de la propuesta quedan con baja lógica (SoftDeletes)
     * para preservar el historial de qué insumos contenía.
     */
    private function crearPropuestaCompraMantenimiento(
        Mantenimiento $mantenimiento,
        array $insumosConProblema
    ): PropuestaCompraMantenimiento {
        $propuesta = PropuestaCompraMantenimiento::firstOrCreate(
            ['id_mantenimiento' => $mantenimiento->id_mantenimiento],
            [
                'id_maquinaria' => $mantenimiento->id_maquinaria,
                'status' => 'pending',
            ]
        );

        if ($propuesta->id_maquinaria !== $mantenimiento->id_maquinaria) {
            $propuesta->id_maquinaria = $mantenimiento->id_maquinaria;
            $propuesta->save();
        }

        PropuestaCompraMantenimientoInsumo::query()
            ->where('id_mantenimiento_purchase_proposal', $propuesta->id_mantenimiento_purchase_proposal)
            ->delete();

        foreach ($insumosConProblema as $ins) {
            if (empty($ins['insumo_id'])) {
                continue;
            }
            PropuestaCompraMantenimientoInsumo::create([
                'id_mantenimiento_purchase_proposal' => $propuesta->id_mantenimiento_purchase_proposal,
                'id_insumo' => (int) $ins['insumo_id'],
                'cantidad_requerida' => (float) ($ins['requerido'] ?? 0),
                'stock_disponible' => (float) ($ins['disponible'] ?? 0),
                'faltante' => (float) ($ins['faltante'] ?? 0),
            ]);
        }

        return $propuesta->fresh(['insumos.insumo.unidadMedida', 'maquinaria.tipoMaquinaria', 'mantenimiento']);
    }

    /**
     * Envía por email la orden generada (y su propuesta de compra) con adjuntos.
     *
     * Con reintentos ante rate-limit; si falla, queda registrado en log y la
     * notificación interna (creada en la transacción) persiste como canal de registro.
     */
    private function enviarCorreoOrdenConAdjuntos(
        Mantenimiento $mantenimiento,
        ?PropuestaCompraMantenimiento $propuesta
    ): void {
        $destinatarios = $this->obtenerDestinatariosMail();
        if (empty($destinatarios)) {
            return;
        }

        try {
            $mantenimiento->loadMissing(['maquinaria.tipoMaquinaria', 'tipoMantenimiento', 'empleados.rolLaboral']);

            $adjuntos = [];
            $adjuntos[] = $this->documentsService->generateMaintenanceOrderPdf($mantenimiento);

            if ($propuesta) {
                $adjuntos[] = $this->documentsService->generatePurchaseOrderPdf($propuesta);
            }

            $this->enviarConReintento(function () use ($destinatarios, $mantenimiento, $propuesta, $adjuntos) {
                $this->esperarParaEnviarMail();
                Mail::to($destinatarios)->send(new MantenimientoOrdenGeneradaMail($mantenimiento, $propuesta, $adjuntos));
            });

            if ($propuesta) {
                $metadatos = is_array($propuesta->meta) ? $propuesta->meta : [];
                $metadatos['purchase_order'] = [
                    'sent_at' => now()->toISOString(),
                    'recipients' => $destinatarios,
                    'attachments' => array_map(fn ($adjunto) => $adjunto['path'] ?? null, $adjuntos),
                ];
                $propuesta->meta = $metadatos;
                $propuesta->status = 'sent';
                $propuesta->save();
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando mail de mantenimiento con adjuntos', [
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Destinatarios configurados para los mails del proceso de mantenimiento.
     *
     * @return array<int, string>
     */
    private function obtenerDestinatariosMail(): array
    {
        $destinatarios = array_values(array_filter((array) config('mail.purchase_order_emails', [])));
        $correoAdmin = trim((string) config('mail.admin_email', ''));
        if ($correoAdmin !== '') {
            $destinatarios[] = $correoAdmin;
        }

        return array_values(array_unique(array_filter($destinatarios)));
    }

    /**
     * Ejecuta el envío con reintentos ante rate-limit del servidor de mail.
     */
    private function enviarConReintento(callable $enviar): void
    {
        $intentos = 0;
        $maxIntentos = 3;
        $espera = 2;

        while (true) {
            try {
                $enviar();

                return;
            } catch (\Exception $e) {
                $intentos++;
                $mensaje = $e->getMessage();
                $esRateLimit = stripos($mensaje, 'Too many emails per second') !== false || stripos($mensaje, '550') !== false;
                if (! $esRateLimit || $intentos >= $maxIntentos) {
                    throw $e;
                }
                sleep($espera);
                $espera *= 2;
            }
        }
    }

    /**
     * Espera el intervalo mínimo entre envíos (rate-limit manual compartido).
     */
    private function esperarParaEnviarMail(): void
    {
        $minInterval = 1.5;
        $ahora = microtime(true);
        $ultimoGlobal = cache()->get('mantenimiento_mail_last_sent_at');
        $referencia = max((float) $this->ultimoEnvioMail, (float) $ultimoGlobal);

        if ($referencia > 0) {
            $delta = $ahora - $referencia;
            if ($delta < $minInterval) {
                usleep((int) (($minInterval - $delta) * 1000000));
            }
        }

        $this->ultimoEnvioMail = microtime(true);
        cache()->put('mantenimiento_mail_last_sent_at', $this->ultimoEnvioMail, 60);
    }

    /**
     * Crea la notificación interna de umbral alcanzado (canal de registro)
     * para los usuarios configurados.
     */
    private function crearNotificacionInterna(
        Mantenimiento $mantenimiento,
        Maquinaria $maquinaria,
        float $toneladasDesdeUltimo,
        array $programacion,
        array $asignacion
    ): void {
        try {
            $idsUsuarios = app(NotificacionService::class)
                ->cargarConfiguracionMantenimiento()['umbral'];

            if (empty($idsUsuarios)) {
                Log::warning('No hay usuarios configurados para notificacion interna de umbral.');

                return;
            }

            $fechaLimite = now()->addDays(7)->toDateString();
            $fechaProgramada = $programacion['fecha_programada']->toDateString();
            $origen = $programacion['fuente'];
            $asignado = $asignacion['nombre'] ?? 'Sin asignacion';

            $titulo = "Mantenimiento Preventivo - Maquinaria {$maquinaria->id_maquinaria}";
            $mensaje = "Se genero la orden #{$mantenimiento->id_mantenimiento}. ".
                "Toneladas detectadas: {$toneladasDesdeUltimo} (umbral {$maquinaria->umbral_toneladas}). ".
                "Fecha programada: {$fechaProgramada} (fuente {$origen}). ".
                "Personal asignado: {$asignado}.";

            foreach ($idsUsuarios as $userId) {
                NotificacionSistema::create([
                    'user_id' => $userId,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'umbral_alcanzado',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => $fechaLimite,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error en crearNotificacionInterna', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Crea la notificación interna de mantenimiento vencido (respaldo del
     * recordatorio por email) para los usuarios configurados.
     */
    private function crearNotificacionVencido(Mantenimiento $mantenimiento): void
    {
        try {
            $idsUsuarios = app(NotificacionService::class)
                ->cargarConfiguracionMantenimiento()['recordatorio'];

            if (empty($idsUsuarios)) {
                return;
            }

            $titulo = "Mantenimiento Vencido - Orden #{$mantenimiento->id_mantenimiento}";
            $mensaje = "La orden #{$mantenimiento->id_mantenimiento} programada para {$mantenimiento->fecha_programada} ".
                'no fue confirmada y quedo marcada como vencida. Requiere reprogramacion.';

            foreach ($idsUsuarios as $userId) {
                NotificacionSistema::create([
                    'user_id' => $userId,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'mantenimiento_vencido',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => now()->addDays(7)->toDateString(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error creando notificacion de mantenimiento vencido', ['error' => $e->getMessage()]);
        }
    }
}
