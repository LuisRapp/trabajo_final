<?php

namespace App\Services;

use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MantenimientoService
{
    /**
     * Verifica si hay stock suficiente para aprobar un mantenimiento preventivo
     */
    public function verificarStockParaAprobacion($mantenimientoId)
    {
        $mantenimiento = Mantenimiento::with(['maquinaria.tipoMaquinaria'])->findOrFail($mantenimientoId);
        
        // Obtener kit de insumos requeridos
        $kit = KitMantenimientoPreventivo::where('id_tipo_maquinaria', $mantenimiento->maquinaria->id_tipo_maquinaria)
            ->with('insumo')
            ->get();

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
                    'faltante' => $item->cantidad_requerida - $stockDisponible
                ];
            }
        }

        return [
            'puede_aprobar' => empty($insuficientes),
            'insuficientes' => $insuficientes,
            'kit' => $kitCompleto
        ];
    }

    /**
     * Completa un mantenimiento: descuenta insumos, calcula costos y actualiza snapshot
     */
    public function completarMantenimiento($mantenimientoId, array $insumos, $costoManoObra = 0)
    {
        DB::beginTransaction();
        try {
            $mantenimiento = Mantenimiento::with('maquinaria')->findOrFail($mantenimientoId);
            
            $costoInsumos = 0;

            foreach ($insumos as $insumoData) {
                $insumo = Insumo::findOrFail($insumoData['insumo_id']);
                $cantidadUsada = $insumoData['cantidad'];
                $costoUnitario = $insumo->costo_unitario;
                $subtotal = $cantidadUsada * $costoUnitario;

                // Verificar stock disponible usando el accessor
                $stockDisponible = $insumo->stock;
                
                if ($stockDisponible >= $cantidadUsada) {
                    // Registrar salida de stock
                    MovimientoStock::create([
                        'id_insumo' => $insumo->id_insumo,
                        'tipo' => 'salida',
                        'cantidad' => $cantidadUsada,
                        'motivo' => "Mantenimiento ID: {$mantenimientoId}",
                        'fecha' => now()
                    ]);
                } else {
                    Log::warning("Stock insuficiente al completar mantenimiento", [
                        'mantenimiento_id' => $mantenimientoId,
                        'insumo' => $insumo->nombre,
                        'requerido' => $cantidadUsada,
                        'disponible' => $stockDisponible
                    ]);
                    
                    // Igual registramos el uso, pero el stock quedará negativo
                    MovimientoStock::create([
                        'id_insumo' => $insumo->id_insumo,
                        'tipo' => 'salida',
                        'cantidad' => $cantidadUsada,
                        'motivo' => "Mantenimiento ID: {$mantenimientoId} (STOCK INSUFICIENTE)",
                        'fecha' => now()
                    ]);
                }

                // Registrar insumo usado en el mantenimiento
                MantenimientoInsumo::create([
                    'id_mantenimiento' => $mantenimientoId,
                    'id_insumo' => $insumo->id_insumo,
                    'cantidad' => $cantidadUsada,
                    'costo_unitario' => $costoUnitario,
                    'subtotal' => $subtotal
                ]);

                $costoInsumos += $subtotal;
            }

            // Calcular costo total
            $costoTotal = $costoInsumos + $costoManoObra;

            // Actualizar mantenimiento
            $mantenimiento->update([
                'estado' => 'completado',
                'fecha_fin' => now()->toDateString(),
                'costo_total' => $costoTotal,
                'costo_mano_obra' => $costoManoObra,
                'toneladas_snapshot' => $mantenimiento->maquinaria->toneladas_acumuladas
            ]);

            DB::commit();

            Log::info("Mantenimiento completado", [
                'mantenimiento_id' => $mantenimientoId,
                'costo_total' => $costoTotal,
                'costo_insumos' => $costoInsumos,
                'costo_mano_obra' => $costoManoObra,
                'snapshot' => $mantenimiento->toneladas_snapshot
            ]);

            return [
                'success' => true,
                'mantenimiento' => $mantenimiento->fresh(),
                'costo_total' => $costoTotal
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error completando mantenimiento", [
                'mantenimiento_id' => $mantenimientoId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene el kit preventivo para un tipo de maquinaria
     */
    public function obtenerKitPreventivo($tipoMaquinariaId)
    {
        return KitMantenimientoPreventivo::where('id_tipo_maquinaria', $tipoMaquinariaId)
            ->with('insumo')
            ->get();
    }
}
