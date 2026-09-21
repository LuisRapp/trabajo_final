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

/**
 * Ciclo de vida interactivo de las órdenes de mantenimiento (UC-62/UC-63).
 *
 * Casos de uso que la persona dispara desde la interfaz: alta y edición de
 * órdenes, aprobación con verificación de stock, cierre unificado con costo
 * FIFO de insumos y snapshot del odómetro, kits preventivos y validaciones.
 * El proceso automático (PA-01) vive en ProcesoMantenimientoService.
 */
class MantenimientoService
{
    /**
     * Verifica si hay stock suficiente para aprobar un mantenimiento preventivo.
     *
     * Compara el kit de mantenimiento (prioriza el kit por maquinaria, con
     * fallback al kit por tipo de maquinaria) contra el stock disponible
     * de cada insumo.
     *
     * @param  int  $mantenimientoId  Identificador de la orden a verificar
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
     * Aprueba una orden de mantenimiento preventivo: verifica stock y cambia el estado.
     *
     * Ejecuta dentro de una transacción. Solo órdenes en estado 'programado' pueden aprobarse.
     *
     * @param  int  $mantenimientoId  Identificador de la orden a aprobar
     * @return array{success: bool, mantenimiento?: \App\Models\Mantenimiento, message?: string, insumos_insuficientes?: array}
     */
    public function aprobarMantenimiento(int $mantenimientoId): array
    {
        try {
            return DB::transaction(function () use ($mantenimientoId) {
                $mantenimiento = Mantenimiento::lockForUpdate()->findOrFail($mantenimientoId);

                if ($mantenimiento->estado !== 'programado') {
                    return [
                        'success' => false,
                        'message' => 'Solo se pueden aprobar órdenes en estado "programado"',
                    ];
                }

                $verificacion = $this->verificarStockParaAprobacion($mantenimientoId);

                if (! $verificacion['puede_aprobar']) {
                    return [
                        'success' => false,
                        'message' => 'No hay stock suficiente para aprobar esta orden',
                        'insumos_insuficientes' => $verificacion['insuficientes'],
                    ];
                }

                $mantenimiento->update([
                    'estado' => 'en curso',
                    'fecha_inicio' => now()->toDateString(),
                ]);

                Log::info('Orden de mantenimiento aprobada', [
                    'mantenimiento_id' => $mantenimientoId,
                    'maquinaria_id' => $mantenimiento->id_maquinaria,
                ]);

                return [
                    'success' => true,
                    'mantenimiento' => $mantenimiento->fresh(),
                ];
            });
        } catch (\Exception $e) {
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
     * Completar una orden de mantenimiento: valida stock, descuenta insumos
     * por FIFO, calcula costos y registra el snapshot del odómetro.
     *
     * Regla única del dominio para el cierre de órdenes (UC-62): toda orden
     * se cierra por este método, sin importar la pantalla de origen. El costo
     * de cada insumo surge de la salida FIFO (InventarioService::registrarSalida)
     * y el cierre registra costo_mano_obra y toneladas_snapshot de la maquinaria.
     *
     * Valida stock suficiente de todos los insumos antes de la transacción
     * (nunca genera stock negativo) y falla si la orden ya está completada.
     *
     * @param  int  $mantenimientoId  Identificador de la orden a completar
     * @param  array  $insumos  Insumos utilizados: [{id_insumo: int, cantidad_utilizada: float}]
     * @param  float  $costoManoObra  Costo de mano de obra / costo base del cierre
     * @param  string|null  $fechaFin  Fecha de cierre (Y-m-d); por defecto hoy
     * @return array{success: bool, mantenimiento?: Mantenimiento, costo_total?: float, costo_insumos?: float, message?: string}
     */
    public function completarMantenimiento(int $mantenimientoId, array $insumos, float $costoManoObra = 0, ?string $fechaFin = null): array
    {
        $fechaFin = $fechaFin ?? now()->toDateString();

        $insumosValidados = [];

        foreach ($insumos as $insumoData) {
            if (empty($insumoData['id_insumo']) || empty($insumoData['cantidad_utilizada'])) {
                continue;
            }

            $insumo = Insumo::findOrFail($insumoData['id_insumo']);
            $cantidadUsada = (float) $insumoData['cantidad_utilizada'];

            $stockDisponible = InventarioService::stockDisponible($insumo->id_insumo);

            if ($stockDisponible < $cantidadUsada) {
                return [
                    'success' => false,
                    'message' => "Stock insuficiente para {$insumo->nombre}. Disponible: {$stockDisponible}, requerido: {$cantidadUsada}",
                ];
            }

            $insumosValidados[] = ['insumo' => $insumo, 'cantidad' => $cantidadUsada];
        }

        try {
            return DB::transaction(function () use ($mantenimientoId, $insumosValidados, $costoManoObra, $fechaFin) {
                $mantenimiento = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])
                    ->lockForUpdate()
                    ->findOrFail($mantenimientoId);

                if ($mantenimiento->estado === 'completado') {
                    return [
                        'success' => false,
                        'message' => 'Este mantenimiento ya está completado',
                    ];
                }

                $tipoNombre = $mantenimiento->tipoMantenimiento?->nombre ?? 'Mantenimiento';
                $costoInsumos = 0;

                foreach ($insumosValidados as $validado) {
                    $insumo = $validado['insumo'];
                    $cantidadUsada = $validado['cantidad'];

                    $resultadoSalida = InventarioService::registrarSalida(
                        $insumo->id_insumo,
                        $cantidadUsada,
                        "Mantenimiento {$tipoNombre} - Orden #{$mantenimientoId}",
                        $fechaFin
                    );

                    $costoUnitario = $cantidadUsada > 0
                        ? $resultadoSalida['costo_total'] / $cantidadUsada
                        : 0;

                    MantenimientoInsumo::create([
                        'id_mantenimiento' => $mantenimientoId,
                        'id_insumo' => $insumo->id_insumo,
                        'cantidad_utilizada' => $cantidadUsada,
                        'costo_unitario' => $costoUnitario,
                        'subtotal' => $resultadoSalida['costo_total'],
                    ]);

                    $costoInsumos += $resultadoSalida['costo_total'];
                }

                $costoTotal = $costoInsumos + $costoManoObra;

                $mantenimiento->update([
                    'estado' => 'completado',
                    'fecha_fin' => $fechaFin,
                    'costo_total' => $costoTotal,
                    'costo_mano_obra' => $costoManoObra,
                    'toneladas_snapshot' => $mantenimiento->maquinaria->toneladas_acumuladas,
                ]);

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
                    'costo_insumos' => $costoInsumos,
                ];
            });
        } catch (\Exception $e) {
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
     * Obtiene el kit de mantenimiento preventivo para un tipo de maquinaria.
     *
     * @param  int  $tipoMaquinariaId  Identificador del tipo de maquinaria
     * @return \Illuminate\Database\Eloquent\Collection Colección de KitMantenimientoPreventivo con la relación insumo
     */
    public function obtenerKitPreventivo($tipoMaquinariaId)
    {
        return KitMantenimientoPreventivo::where('id_tipo_maquinaria', $tipoMaquinariaId)
            ->with('insumo')
            ->get();
    }

    /**
     * Crea una nueva orden de mantenimiento.
     *
     * @param  array  $datos  Datos validados: id_maquinaria, id_tipo_mantenimiento, fecha_inicio, fecha_programada?, estado
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
     * Lista órdenes de mantenimiento con búsqueda de texto libre (solo lectura).
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
                $q->whereLike('estado', '%'.$busq.'%')
                    ->when(is_numeric($busq), fn ($q) => $q->orWhere('costo_total', (float) $busq))
                    ->orWhereHas('maquinaria', function ($qm) use ($busq) {
                        $qm->whereLike('modelo', '%'.$busq.'%');
                    })
                    ->orWhereHas('tipoMantenimiento', function ($qt) use ($busq) {
                        $qt->whereLike('nombre', '%'.$busq.'%');
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
     * Si un tipo de mantenimiento es correctivo.
     *
     * Regla única del dominio: el nombre del tipo contiene "correctivo".
     */
    public function esTipoCorrectivo(TipoMantenimiento $tipo): bool
    {
        return str_contains(mb_strtolower($tipo->nombre), 'correctivo');
    }

    /**
     * Obtiene una orden con sus relaciones para el modal de cierre.
     */
    public function obtenerOrdenParaCompletar(int $id): Mantenimiento
    {
        return Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($id);
    }

    /**
     * Obtiene una orden con sus relaciones para el modal de detalle.
     */
    public function obtenerOrdenParaDetalle(int $id): Mantenimiento
    {
        return Mantenimiento::with([
            'maquinaria.tipoMaquinaria',
            'mantenimientoInsumos.insumo',
        ])->findOrFail($id);
    }

    /**
     * Obtiene una orden con su maquinaria para el modal de aprobación.
     */
    public function obtenerOrdenParaAprobar(int $id): Mantenimiento
    {
        return Mantenimiento::with(['maquinaria.tipoMaquinaria'])->findOrFail($id);
    }

    /**
     * Maquinarias en estado activo para los filtros de la gestión de mantenimientos.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Maquinaria>
     */
    public function obtenerMaquinariasActivasParaFiltro(): Collection
    {
        return Maquinaria::with('tipoMaquinaria')
            ->where('estado', 'activo')
            ->orderBy('modelo')
            ->get();
    }

    /**
     * Insumos disponibles para el cierre de una orden, con stock y precio.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Insumo>
     */
    public function obtenerInsumosParaCierre(): Collection
    {
        return Insumo::orderBy('nombre')->get()->map(function (Insumo $insumo) {
            $insumo->stock_disponible = InventarioService::stockDisponible($insumo->id_insumo);
            $insumo->precio_promedio = InventarioService::precioPromedio($insumo->id_insumo);

            return $insumo;
        });
    }

    /**
     * Obtiene un insumo por su identificador (para actualización del modal de cierre).
     */
    public function obtenerInsumo(int $idInsumo): ?Insumo
    {
        return Insumo::find($idInsumo);
    }

    /**
     * Lista las órdenes para la pantalla de gestión según pestaña y filtros.
     *
     * @param  array{estado?: string, maquinaria?: string, tipo?: string, fecha_desde?: string, fecha_hasta?: string}  $filtros
     * @param  string  $tab  Pestaña activa: 'ordenes', 'completadas' o 'kits'
     * @return \Illuminate\Database\Eloquent\Collection<int, Mantenimiento>
     */
    public function listarOrdenesGestion(array $filtros, string $tab): Collection
    {
        $query = Mantenimiento::with(['maquinaria.tipoMaquinaria', 'tipoMantenimiento'])
            ->whereBetween('fecha_inicio', [
                $filtros['fecha_desde'] ?: now()->subYear(),
                $filtros['fecha_hasta'] ?: now(),
            ]);

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['maquinaria'])) {
            $query->where('id_maquinaria', $filtros['maquinaria']);
        }

        if (! empty($filtros['tipo'])) {
            $query->where('id_tipo_mantenimiento', $filtros['tipo']);
        }

        if ($tab === 'ordenes') {
            $query->whereIn('estado', ['programado', 'en curso']);
        } elseif ($tab === 'completadas') {
            $query->where('estado', 'completado');
        }

        return $query->orderBy('fecha_inicio', 'desc')->get();
    }

    /**
     * Obtiene el kit de mantenimiento preventivo para una maquinaria y tipo específicos.
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
            ->select('kit_mantenimiento_preventivo.id_insumo', 'insumos.nombre', 'kit_mantenimiento_preventivo.cantidad_requerida')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene las maquinarias activas (no dadas de baja) ordenadas por modelo.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Maquinaria>
     */
    public function obtenerMaquinariasActivas(): Collection
    {
        return Maquinaria::where('estado', '!=', 'dado_de_baja')->orderBy('modelo')->get();
    }

    /**
     * Obtiene todos los tipos de mantenimiento ordenados por nombre.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, TipoMantenimiento>
     */
    public function obtenerTiposMantenimiento(): Collection
    {
        return TipoMantenimiento::orderBy('nombre')->get();
    }

    /**
     * Obtiene una orden de mantenimiento lista para editar.
     */
    public function obtenerMantenimientoParaEditar(int $id): Mantenimiento
    {
        return Mantenimiento::findOrFail($id);
    }

    /**
     * Guarda (crea o actualiza) una orden de mantenimiento y marca la notificación
     * asociada como accionada.
     *
     * Ejecuta dentro de una transacción.
     *
     * @param  array  $datos  Datos validados
     * @return array{success: bool, mantenimiento?: Mantenimiento, tipoNombre: string, maquinaNombre: string, message?: string}
     */
    public function guardarMantenimiento(array $datos, ?int $mantenimientoId = null, ?int $usuarioId = null): array
    {
        try {
            return DB::transaction(function () use ($datos, $mantenimientoId, $usuarioId) {
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

                return [
                    'success' => true,
                    'mantenimiento' => $mantenimiento->fresh(),
                    'tipoNombre' => $tipoNombre,
                    'maquinaNombre' => $maquinaNombre,
                ];
            });
        } catch (\Exception $e) {
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
     * Elimina (baja lógica) una orden de mantenimiento.
     */
    public function eliminarMantenimiento(int $id): bool
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->delete();

        return true;
    }

    /**
     * Confirma una orden programada y marca su notificación como accionada.
     *
     * Ejecuta dentro de una transacción.
     *
     * @return array{success: bool, mantenimiento?: Mantenimiento, message?: string}
     */
    public function confirmarMantenimiento(int $id, ?int $usuarioId = null): array
    {
        try {
            return DB::transaction(function () use ($id, $usuarioId) {
                $mantenimiento = Mantenimiento::findOrFail($id);

                if ($mantenimiento->estado !== 'programado') {
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

                return [
                    'success' => true,
                    'mantenimiento' => $mantenimiento->fresh(),
                    'message' => "Mantenimiento #{$id} confirmado y en curso.",
                ];
            });
        } catch (\Exception $e) {
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
     * Reprograma una orden vencida devolviéndola al estado programado.
     *
     * Ejecuta dentro de una transacción.
     *
     * @return array{success: bool, mantenimiento?: Mantenimiento, message?: string}
     */
    public function reprogramarMantenimiento(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $mantenimiento = Mantenimiento::findOrFail($id);

                if ($mantenimiento->estado !== 'vencido') {
                    return [
                        'success' => false,
                        'message' => 'Solo se pueden reprogramar mantenimientos vencidos.',
                    ];
                }

                $mantenimiento->update([
                    'estado' => 'programado',
                    'fecha_programada' => null,
                ]);

                return [
                    'success' => true,
                    'mantenimiento' => $mantenimiento->fresh(),
                    'message' => "Mantenimiento #{$id} reprogramado. Por favor, asigne una nueva fecha.",
                ];
            });
        } catch (\Exception $e) {
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
     * Valida que una fecha programada esté dentro de 7 días desde la notificación
     * asociada.
     *
     * Se usa al editar una orden de mantenimiento existente.
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
     * Valida que una fecha programada esté dentro de los próximos 7 días desde hoy.
     *
     * Se usa al crear una orden nueva sin notificación previa.
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
     * Marca como accionada la notificación pendiente del usuario actual para una orden.
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
