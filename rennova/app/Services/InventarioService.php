<?php

namespace App\Services;

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

            $resultado = DB::selectOne(
                'SELECT * FROM calcular_costo_fifo(?, ?)',
                [$idInsumo, $cantidad]
            );

            $costoTotal = $resultado->v_costo_total;
            $lotesConsumidos = json_decode($resultado->v_lotes_consumidos, true);

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
        $resultado = DB::selectOne(
            'SELECT obtener_stock_disponible(?) as stock',
            [$idInsumo]
        );

        return $resultado->stock ?? 0;
    }

    public static function precioPromedio($idInsumo)
    {
        $resultado = DB::selectOne(
            'SELECT obtener_precio_promedio(?) as precio',
            [$idInsumo]
        );

        return $resultado->precio ?? 0;
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
}
