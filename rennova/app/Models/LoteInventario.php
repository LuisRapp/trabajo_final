<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class LoteInventario extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'lotes_inventario';
    protected $primaryKey = 'id_lote_inventario';
    
    protected $fillable = [
        'id_insumo',
        'id_proveedor',
        'cantidad_inicial',
        'cantidad_disponible',
        'precio_unitario',
        'costo_total',
        'fecha_compra',
        'numero_factura',
        'tipo_movimiento',
        'observaciones',
        'agotado'
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'cantidad_inicial' => 'decimal:2',
        'cantidad_disponible' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'agotado' => 'boolean'
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoStock::class, 'id_lote_inventario');
    }

    // Scopes
    
    /**
     * Filtrar solo lotes disponibles (no agotados)
     */
    public function scopeDisponibles($query)
    {
        return $query->where('agotado', false);
    }

    /**
     * Filtrar solo lotes agotados
     */
    public function scopeAgotados($query)
    {
        return $query->where('agotado', true);
    }

    /**
     * Filtrar por insumo específico
     */
    public function scopePorInsumo($query, $idInsumo)
    {
        return $query->where('id_insumo', $idInsumo);
    }

    /**
     * Filtrar por proveedor específico
     */
    public function scopePorProveedor($query, $idProveedor)
    {
        return $query->where('id_proveedor', $idProveedor);
    }

    /**
     * Ordenar por FIFO (primero los más antiguos)
     */
    public function scopeOrdenFifo($query)
    {
        return $query->orderBy('fecha_compra', 'asc')
                    ->orderBy('id_lote_inventario', 'asc');
    }

    /**
     * Filtrar por rango de fechas de compra
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
    }

    // Métodos de instancia

    /**
     * Consumir cantidad del lote (actualiza cantidad_disponible y flag agotado)
     * 
     * @param float $cantidad Cantidad a consumir
     * @return bool
     * @throws \Exception Si se intenta consumir más de lo disponible
     */
    public function consumir($cantidad)
    {
        if ($cantidad > $this->cantidad_disponible) {
            throw new \Exception(
                "No se puede consumir {$cantidad} unidades del lote {$this->id_lote_inventario}. " .
                "Disponible: {$this->cantidad_disponible}"
            );
        }

        $this->cantidad_disponible -= $cantidad;
        
        if ($this->cantidad_disponible <= 0) {
            $this->cantidad_disponible = 0;
            $this->agotado = true;
        }

        return $this->save();
    }

    /**
     * Obtiene el valor total del lote (cantidad_disponible × precio_unitario)
     * 
     * @return float
     */
    public function getValorDisponibleAttribute()
    {
        return $this->cantidad_disponible * $this->precio_unitario;
    }

    /**
     * Obtiene el porcentaje consumido del lote
     * 
     * @return float
     */
    public function getPorcentajeConsumidoAttribute()
    {
        if ($this->cantidad_inicial <= 0) {
            return 0;
        }

        $cantidadConsumida = $this->cantidad_inicial - $this->cantidad_disponible;
        return ($cantidadConsumida / $this->cantidad_inicial) * 100;
    }

    /**
     * Verifica si el lote está próximo a agotarse (menos del 20% disponible)
     * 
     * @return bool
     */
    public function estaProximoAgotar()
    {
        if ($this->cantidad_inicial <= 0) {
            return false;
        }

        $porcentajeDisponible = ($this->cantidad_disponible / $this->cantidad_inicial) * 100;
        return $porcentajeDisponible < 20 && !$this->agotado;
    }

    // Métodos estáticos

    /**
     * Obtiene el stock total disponible de un insumo sumando todos sus lotes
     * 
     * @param int $idInsumo
     * @return float
     */
    public static function stockDisponible($idInsumo)
    {
        return static::porInsumo($idInsumo)
            ->disponibles()
            ->sum('cantidad_disponible');
    }

    /**
     * Obtiene el valor total del inventario de un insumo
     * 
     * @param int $idInsumo
     * @return float
     */
    public static function valorInventario($idInsumo)
    {
        $lotes = static::porInsumo($idInsumo)
            ->disponibles()
            ->get();

        return $lotes->sum(function ($lote) {
            return $lote->cantidad_disponible * $lote->precio_unitario;
        });
    }

    /**
     * Obtiene lotes próximos a agotarse de un insumo
     * 
     * @param int|null $idInsumo Si es null, busca en todos los insumos
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function proximosAgotar($idInsumo = null)
    {
        $query = static::disponibles();

        if ($idInsumo) {
            $query->porInsumo($idInsumo);
        }

        return $query->get()->filter(function ($lote) {
            return $lote->estaProximoAgotar();
        });
    }
}
