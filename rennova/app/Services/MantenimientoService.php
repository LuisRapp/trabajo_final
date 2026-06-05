<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
use App\Models\MovimientoStock;
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
     * Complete a maintenance order: deduct inputs, calculate costs, update snapshot.
     *
     * For each input used:
     * - Registers a stock exit movement
     * - Creates a MantenimientoInsumo record with cost
     * Updates the maintenance order status to 'completado' with final costs
     * and a machinery tonnage snapshot.
     *
     * @param  int  $mantenimientoId  The maintenance order ID to complete
     * @param  array  $insumos  Array of inputs used: [{id_insumo, cantidad_utilizada, costo_unitario?}]
     * @param  float  $costoManoObra  Labor cost for this maintenance
     * @return array{success: bool, mantenimiento?: \App\Models\Mantenimiento, costo_total?: float, message?: string}
     *
     * @warning Runs inside a DB transaction. On failure, all changes are rolled back.
     */
    public function completarMantenimiento($mantenimientoId, array $insumos, $costoManoObra = 0)
    {
        DB::beginTransaction();
        try {
            $mantenimiento = Mantenimiento::with('maquinaria')->findOrFail($mantenimientoId);

            $costoInsumos = 0;

            foreach ($insumos as $insumoData) {
                $insumo = Insumo::findOrFail($insumoData['id_insumo']);
                $cantidadUsada = (float) ($insumoData['cantidad_utilizada'] ?? 0);
                $costoUnitario = isset($insumoData['costo_unitario'])
                    ? (float) $insumoData['costo_unitario']
                    : (float) $insumo->costo_unitario;
                $subtotal = $cantidadUsada * $costoUnitario;

                $stockDisponible = $insumo->stock;

                if ($stockDisponible >= $cantidadUsada) {
                    MovimientoStock::create([
                        'id_insumo' => $insumo->id_insumo,
                        'tipo' => 'salida',
                        'cantidad' => $cantidadUsada,
                        'motivo' => "Mantenimiento ID: {$mantenimientoId}",
                        'fecha' => now(),
                    ]);
                } else {
                    Log::warning('Stock insuficiente al completar mantenimiento', [
                        'mantenimiento_id' => $mantenimientoId,
                        'insumo' => $insumo->nombre,
                        'requerido' => $cantidadUsada,
                        'disponible' => $stockDisponible,
                    ]);

                    MovimientoStock::create([
                        'id_insumo' => $insumo->id_insumo,
                        'tipo' => 'salida',
                        'cantidad' => $cantidadUsada,
                        'motivo' => "Mantenimiento ID: {$mantenimientoId} (STOCK INSUFICIENTE)",
                        'fecha' => now(),
                    ]);
                }

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
                'message' => $e->getMessage(),
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
}
