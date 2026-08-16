<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
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
}
