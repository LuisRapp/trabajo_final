<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Registra una salida de stock para un insumo, calculando el costo FIFO.
     *
     * Usa la función PostgreSQL calcular_costo_fifo() cuando está disponible;
     * si no, cae al cálculo FIFO manual (SQLite/testing).
     *
     * @param  int  $idInsumo  Identificador del insumo del cual extraer stock
     * @param  float  $cantidad  Cantidad a extraer (debe ser > 0)
     * @param  string  $motivo  Motivo de la extracción (p. ej. "Parte Diario #5 - Producción")
     * @param  string|null  $fecha  Fecha del movimiento (formato Y-m-d). Por defecto hoy
     * @param  int|null  $parteDiarioId  Identificador opcional del parte diario asociado
     * @return array{movimientos: \Illuminate\Database\Eloquent\Collection, costo_total: float, lotes_consumidos: array}
     *
     * @throws \Exception Si el stock es insuficiente o ocurre un error de base de datos
     *
     * @warning Este método corre dentro de una transacción. Si falla, todos los cambios se revierten.
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
     * Registra una entrada de stock creando un nuevo lote de inventario.
     *
     * @param  int  $idInsumo  Identificador del insumo al cual agregar stock
     * @param  float  $cantidad  Cantidad ingresada
     * @param  float  $precioUnitario  Precio unitario para el cálculo de costo
     * @param  array  $metadata  Metadatos opcionales: id_proveedor, numero_factura, tipo_movimiento, observaciones, motivo
     * @param  string|null  $fecha  Fecha del movimiento (formato Y-m-d). Por defecto hoy
     * @return array{movimiento: \App\Models\MovimientoStock, lote: \App\Models\LoteInventario}
     *
     * @throws \Exception Si ocurre un error de base de datos
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
     * Obtiene el stock total disponible de un insumo.
     *
     * Usa la función PostgreSQL obtener_stock_disponible() cuando está disponible.
     *
     * @param  int  $idInsumo  Identificador del insumo a consultar
     * @return float Cantidad de stock disponible
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
     * Obtiene el precio promedio ponderado de un insumo sobre todos los lotes disponibles.
     *
     * Usa la función PostgreSQL obtener_precio_promedio() cuando está disponible.
     *
     * @param  int  $idInsumo  Identificador del insumo a calcular
     * @return float Precio promedio ponderado (0 si no hay stock)
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
     * Obtiene el stock total disponible en todos los lotes no agotados de un insumo.
     *
     * @param  int  $idInsumo  Identificador del insumo
     * @return float Suma de cantidad_disponible de todos los lotes disponibles
     */
    public static function stockTotalDisponible($idInsumo)
    {
        return LoteInventario::porInsumo($idInsumo)
            ->disponibles()
            ->sum('cantidad_disponible');
    }

    /**
     * Calcula el valor total del inventario de un insumo.
     *
     * @param  int  $idInsumo  Identificador del insumo
     * @return float Suma de (cantidad_disponible * precio_unitario) de todos los lotes disponibles
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
     * Obtiene los lotes próximos a agotarse (menos del 20% restante).
     *
     * @param  int|null  $idInsumo  Identificador de insumo opcional para filtrar
     * @return \Illuminate\Support\Collection Colección de LoteInventario próximos a agotarse
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

    /**
     * Cuenta insumos cuyo stock está por debajo del umbral crítico.
     *
     * Utiliza la función PostgreSQL obtener_stock_disponible() cuando está disponible,
     * delegando el cálculo a la base de datos para evitar cargar modelos en memoria.
     * En otros motores (SQLite/testing) itera sobre IDs con el servicio como fallback.
     *
     * @param  int  $umbral  Umbral de stock crítico (default 10)
     * @return int Cantidad de insumos críticos
     */
    public static function contarInsumosCriticos(int $umbral = 10): int
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $resultado = DB::selectOne(
                'SELECT COUNT(*) as total FROM insumos WHERE obtener_stock_disponible(insumos.id_insumo) < ?',
                [$umbral]
            );

            return $resultado->total ?? 0;
        }

        // Fallback para SQLite/testing: itera IDs sin cargar modelos completos
        $ids = Insumo::pluck('id_insumo');
        $count = 0;
        foreach ($ids as $id) {
            if (self::stockDisponible($id) < $umbral) {
                $count++;
            }
        }

        return $count;
    }
}
