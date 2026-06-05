<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\DB;

class InventarioService
{
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

    public static function stockTotalDisponible($idInsumo)
    {
        return LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->sum('cantidad_disponible');
    }

    public static function valorInventario($idInsumo)
    {
        $lotes = LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->get();

        return $lotes->sum(function ($lote) {
            return $lote->cantidad_disponible * $lote->precio_unitario;
        });
    }

    public static function proximosAgotar($idInsumo = null)
    {
        $query = LoteInventario::disponibles();

        if ($idInsumo) {
            $query->porInsumo($idInsumo);
        }

        return $query->get()->filter(function ($lote) {
            return $lote->estaProximoAgotar();
        });
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
