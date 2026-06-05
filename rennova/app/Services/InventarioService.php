<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Register a stock exit (output) for a given input, calculating FIFO cost.
     *
     * Uses the PostgreSQL calcular_costo_fifo() function when available,
     * otherwise falls back to manual FIFO calculation (SQLite/testing).
     *
     * @param  int  $idInsumo  The input (insumo) ID to extract stock from
     * @param  float  $cantidad  Amount to extract (must be > 0)
     * @param  string  $motivo  Reason for the extraction (e.g. "Parte Diario #5 - Producción")
     * @param  string|null  $fecha  Date of the movement (Y-m-d format). Defaults to today
     * @param  int|null  $parteDiarioId  Optional related daily report ID
     * @return array{movimientos: \Illuminate\Database\Eloquent\Collection, costo_total: float, lotes_consumidos: array}
     *
     * @throws \Exception If there is insufficient stock or a database error occurs
     *
     * @warning This method runs inside a DB transaction. On failure, all changes are rolled back.
     */
    public static function registrarSalida($idInsumo, $cantidad, $motivo, $fecha = null, $parteDiarioId = null)
    {
        DB::beginTransaction();

        try {
            $fecha = $fecha ?? now()->format('Y-m-d');

            if (DB::connection()->getDriverName() === 'pgsql') {
                $resultado = DB::selectOne(
                    'SELECT * FROM calcular_costo_fifo(?, ?)',
                    [$idInsumo, $cantidad]
                );

                $costoTotal = $resultado->v_costo_total;
                $lotesConsumidos = json_decode($resultado->v_lotes_consumidos, true);
            } else {
                $lotesConsumidos = self::calcularCostoFifoManual($idInsumo, $cantidad);
                $costoTotal = collect($lotesConsumidos)->sum('costo_parcial');

                // Actualizar lotes en SQLite (la función PostgreSQL lo hace automáticamente)
                foreach ($lotesConsumidos as $loteConsumido) {
                    $lote = LoteInventario::find($loteConsumido['id_lote_inventario']);
                    if ($lote) {
                        self::consumirLote($lote, $loteConsumido['cantidad_consumida']);
                    }
                }
            }

            $movimientos = [];
            foreach ($lotesConsumidos as $lote) {
                $movimiento = MovimientoStock::create([
                    'id_insumo' => $idInsumo,
                    'tipo' => 'salida',
                    'cantidad' => $lote['cantidad_consumida'],
                    'fecha' => $fecha,
                    'motivo' => $motivo,
                    'precio_unitario' => $lote['precio_unitario'],
                    'id_lote_inventario' => $lote['id_lote_inventario'],
                    'costo_total_movimiento' => $lote['costo_parcial'],
                    'id_parte_diario' => $parteDiarioId,
                ]);

                $movimientos[] = $movimiento;
            }

            DB::commit();

            return [
                'movimientos' => $movimientos,
                'costo_total' => $costoTotal,
                'lotes_consumidos' => $lotesConsumidos,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Register a stock entry (input) creating a new inventory lot.
     *
     * @param  int  $idInsumo  The input (insumo) ID to add stock to
     * @param  float  $cantidad  Amount being entered
     * @param  float  $precioUnitario  Unit price for cost calculation
     * @param  array  $metadata  Optional metadata: id_proveedor, numero_factura, tipo_movimiento, observaciones, motivo
     * @param  string|null  $fecha  Date of the movement (Y-m-d format). Defaults to today
     * @return array{movimiento: \App\Models\MovimientoStock, lote: \App\Models\LoteInventario}
     *
     * @throws \Exception If a database error occurs
     */
    public static function registrarEntrada($idInsumo, $cantidad, $precioUnitario, $metadata = [], $fecha = null)
    {
        DB::beginTransaction();

        try {
            $fecha = $fecha ?? now()->format('Y-m-d');

            $lote = LoteInventario::create([
                'id_insumo' => $idInsumo,
                'id_proveedor' => $metadata['id_proveedor'] ?? null,
                'cantidad_inicial' => $cantidad,
                'cantidad_disponible' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'costo_total' => $cantidad * $precioUnitario,
                'fecha_compra' => $fecha,
                'numero_factura' => $metadata['numero_factura'] ?? null,
                'tipo_movimiento' => $metadata['tipo_movimiento'] ?? 'compra',
                'observaciones' => $metadata['observaciones'] ?? null,
                'agotado' => false,
            ]);

            $movimiento = MovimientoStock::create([
                'id_insumo' => $idInsumo,
                'tipo' => 'entrada',
                'cantidad' => $cantidad,
                'fecha' => $fecha,
                'motivo' => $metadata['motivo'] ?? 'Compra - Factura '.($metadata['numero_factura'] ?? 'S/N'),
                'precio_unitario' => $precioUnitario,
                'id_lote_inventario' => $lote->id_lote_inventario,
                'costo_total_movimiento' => $cantidad * $precioUnitario,
            ]);

            DB::commit();

            return [
                'movimiento' => $movimiento,
                'lote' => $lote,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get the total available stock for a given input.
     *
     * Uses PostgreSQL function obtener_stock_disponible() when available.
     *
     * @param  int  $idInsumo  The input ID to check stock for
     * @return float Available stock quantity
     */
    public static function stockDisponible($idInsumo)
    {
        // Usar función PostgreSQL si está disponible, sino calcular con Eloquent
        if (DB::connection()->getDriverName() === 'pgsql') {
            $resultado = DB::selectOne(
                'SELECT obtener_stock_disponible(?) as stock',
                [$idInsumo]
            );

            return $resultado->stock ?? 0;
        }

        return self::stockTotalDisponible($idInsumo);
    }

    /**
     * Get the weighted average price for a given input across all available lots.
     *
     * Uses PostgreSQL function obtener_precio_promedio() when available.
     *
     * @param  int  $idInsumo  The input ID to calculate average price for
     * @return float Weighted average price (0 if no stock available)
     */
    public static function precioPromedio($idInsumo)
    {
        // Usar función PostgreSQL si está disponible, sino calcular con Eloquent
        if (DB::connection()->getDriverName() === 'pgsql') {
            $resultado = DB::selectOne(
                'SELECT obtener_precio_promedio(?) as precio',
                [$idInsumo]
            );

            return $resultado->precio ?? 0;
        }

        return self::calcularPrecioPromedio($idInsumo);
    }

    private static function calcularPrecioPromedio($idInsumo)
    {
        $lotes = LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->get();

        $totalCantidad = $lotes->sum('cantidad_disponible');
        $totalValor = $lotes->sum(function ($lote) {
            return $lote->cantidad_disponible * $lote->precio_unitario;
        });

        return $totalCantidad > 0 ? $totalValor / $totalCantidad : 0;
    }

    /**
     * Consume a specific amount from an inventory lot.
     *
     * Marks the lot as exhausted if remaining quantity reaches zero.
     *
     * @param  \App\Models\LoteInventario  $lote  The inventory lot to consume from
     * @param  float  $cantidad  Amount to consume
     * @return bool Whether the lot was saved successfully
     *
     * @throws \Exception If the requested amount exceeds available quantity
     */
    public static function consumirLote(LoteInventario $lote, $cantidad)
    {
        if ($cantidad > $lote->cantidad_disponible) {
            throw new \Exception(
                "No se puede consumir {$cantidad} unidades del lote {$lote->id_lote_inventario}. ".
                "Disponible: {$lote->cantidad_disponible}"
            );
        }

        $lote->cantidad_disponible -= $cantidad;

        if ($lote->cantidad_disponible <= 0) {
            $lote->cantidad_disponible = 0;
            $lote->agotado = true;
        }

        return $lote->save();
    }

    private static function calcularCostoFifoManual($idInsumo, $cantidad)
    {
        $lotes = LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->orderBy('fecha_compra')
            ->get();

        $cantidadRestante = $cantidad;
        $lotesConsumidos = [];

        foreach ($lotes as $lote) {
            if ($cantidadRestante <= 0) {
                break;
            }

            $cantidadConsumida = min($cantidadRestante, $lote->cantidad_disponible);
            $costoParcial = $cantidadConsumida * $lote->precio_unitario;

            $lotesConsumidos[] = [
                'id_lote_inventario' => $lote->id_lote_inventario,
                'cantidad_consumida' => $cantidadConsumida,
                'precio_unitario' => $lote->precio_unitario,
                'costo_parcial' => $costoParcial,
            ];

            $cantidadRestante -= $cantidadConsumida;
        }

        return $lotesConsumidos;
    }

    /**
     * Get the total available stock across all non-exhausted lots for an input.
     *
     * @param  int  $idInsumo  The input ID
     * @return float Sum of cantidad_disponible across all available lots
     */
    public static function stockTotalDisponible($idInsumo)
    {
        return LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->sum('cantidad_disponible');
    }

    /**
     * Calculate the total inventory value for a given input.
     *
     * @param  int  $idInsumo  The input ID
     * @return float Sum of (cantidad_disponible * precio_unitario) for all available lots
     */
    public static function valorInventario($idInsumo)
    {
        $lotes = LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->get();

        return $lotes->sum(function ($lote) {
            return $lote->cantidad_disponible * $lote->precio_unitario;
        });
    }

    /**
     * Get lots that are close to being exhausted (less than 20% remaining).
     *
     * @param  int|null  $idInsumo  Optional input ID to filter by
     * @return \Illuminate\Support\Collection Collection of LoteInventario models near exhaustion
     */
    public static function proximosAgotar($idInsumo = null)
    {
        $query = LoteInventario::disponibles();

        if ($idInsumo) {
            $query->porInsumo($idInsumo);
        }

        return $query->get()->filter(function ($lote) {
            return self::estaProximoAgotar($lote);
        });
    }

    /**
     * Verifica si un lote está próximo a agotarse (menos del 20% disponible)
     */
    public static function estaProximoAgotar(LoteInventario $lote): bool
    {
        if ($lote->cantidad_inicial <= 0) {
            return false;
        }

        $porcentajeDisponible = ($lote->cantidad_disponible / $lote->cantidad_inicial) * 100;

        return $porcentajeDisponible < 20 && ! $lote->agotado;
    }

    /**
     * Retorna query builder de Insumo con stock y precio promedio precalculados.
     * Reemplaza el scope Insumo::conStockYPrecio() para centralizar lógica en Servicios.
     */
    public static function queryInsumosConStockYPrecio()
    {
        return Insumo::query()->addSelect([
            'insumos.*',
            'stock' => LoteInventario::selectRaw('COALESCE(SUM(cantidad_disponible), 0)')
                ->whereColumn('lotes_inventario.id_insumo', 'insumos.id_insumo')
                ->where('agotado', false),
            'precio_promedio' => LoteInventario::selectRaw('
                CASE 
                    WHEN SUM(cantidad_disponible) > 0 THEN 
                        SUM(cantidad_disponible * precio_unitario) / SUM(cantidad_disponible)
                    ELSE 0 
                END
            ')
                ->whereColumn('lotes_inventario.id_insumo', 'insumos.id_insumo')
                ->where('agotado', false),
        ]);
    }
}
