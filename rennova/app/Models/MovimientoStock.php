<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class MovimientoStock extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'movimiento_stocks';
    protected $primaryKey = 'id_movimiento_stock';
    protected $fillable = [
        'id_insumo', 
        'tipo', 
        'cantidad', 
        'fecha', 
        'motivo',
        'precio_unitario',
        'id_lote_inventario',
        'costo_total_movimiento',
        'id_parte_diario'
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'costo_total_movimiento' => 'decimal:2'
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    public function loteInventario()
    {
        return $this->belongsTo(LoteInventario::class, 'id_lote_inventario');
    }

    public function parteDiario()
    {
        return $this->belongsTo(ParteDiario::class, 'id_parte_diario');
    }

    /**
     * Registra una salida de stock usando FIFO automático
     * 
     * @param int $idInsumo ID del insumo
     * @param float $cantidad Cantidad a sacar
     * @param string $motivo Descripción del movimiento
     * @param string|null $fecha Fecha del movimiento (null = hoy)
     * @return array ['movimientos' => MovimientoStock[], 'costo_total' => float]
     * @throws \Exception Si hay stock insuficiente
     */
    public static function registrarSalida($idInsumo, $cantidad, $motivo, $fecha = null, $parteDiarioId = null)
    {
        DB::beginTransaction();
        
        try {
            $fecha = $fecha ?? now()->format('Y-m-d');
            
            // Llamar a la función FIFO de PostgreSQL
            $resultado = DB::selectOne(
                'SELECT * FROM calcular_costo_fifo(?, ?)',
                [$idInsumo, $cantidad]
            );
            
            $costoTotal = $resultado->v_costo_total;
            $lotesConsumidos = json_decode($resultado->v_lotes_consumidos, true);
            
            // Crear movimientos de stock por cada lote consumido
            $movimientos = [];
            foreach ($lotesConsumidos as $lote) {
                $movimiento = self::create([
                    'id_insumo' => $idInsumo,
                    'tipo' => 'salida',
                    'cantidad' => $lote['cantidad_consumida'],
                    'fecha' => $fecha,
                    'motivo' => $motivo,
                    'precio_unitario' => $lote['precio_unitario'],
                    'id_lote_inventario' => $lote['id_lote_inventario'],
                    'costo_total_movimiento' => $lote['costo_parcial'],
                    'id_parte_diario' => $parteDiarioId
                ]);
                
                $movimientos[] = $movimiento;
            }
            
            DB::commit();
            
            return [
                'movimientos' => $movimientos,
                'costo_total' => $costoTotal,
                'lotes_consumidos' => $lotesConsumidos
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Registra una entrada de stock creando un nuevo lote de inventario
     * 
     * @param int $idInsumo ID del insumo
     * @param float $cantidad Cantidad a ingresar
     * @param float $precioUnitario Precio de compra por unidad
     * @param array $metadata ['id_proveedor', 'numero_factura', 'tipo_movimiento', 'observaciones']
     * @param string|null $fecha Fecha del movimiento (null = hoy)
     * @return array ['movimiento' => MovimientoStock, 'lote' => LoteInventario]
     */
    public static function registrarEntrada($idInsumo, $cantidad, $precioUnitario, $metadata = [], $fecha = null)
    {
        DB::beginTransaction();
        
        try {
            $fecha = $fecha ?? now()->format('Y-m-d');
            
            // Crear lote de inventario
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
                'agotado' => false
            ]);
            
            // Crear movimiento de stock
            $movimiento = self::create([
                'id_insumo' => $idInsumo,
                'tipo' => 'entrada',
                'cantidad' => $cantidad,
                'fecha' => $fecha,
                'motivo' => $metadata['motivo'] ?? 'Compra - Factura ' . ($metadata['numero_factura'] ?? 'S/N'),
                'precio_unitario' => $precioUnitario,
                'id_lote_inventario' => $lote->id_lote_inventario,
                'costo_total_movimiento' => $cantidad * $precioUnitario
            ]);
            
            DB::commit();
            
            return [
                'movimiento' => $movimiento,
                'lote' => $lote
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el stock disponible actual de un insumo
     * 
     * @param int $idInsumo
     * @return float
     */
    public static function stockDisponible($idInsumo)
    {
        $resultado = DB::selectOne(
            'SELECT obtener_stock_disponible(?) as stock',
            [$idInsumo]
        );
        
        return $resultado->stock ?? 0;
    }

    /**
     * Obtiene el precio promedio ponderado actual de un insumo
     * 
     * @param int $idInsumo
     * @return float
     */
    public static function precioPromedio($idInsumo)
    {
        $resultado = DB::selectOne(
            'SELECT obtener_precio_promedio(?) as precio',
            [$idInsumo]
        );
        
        return $resultado->precio ?? 0;
    }
}
