<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class LoteInventario extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

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
        'agotado',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'cantidad_inicial' => 'decimal:2',
        'cantidad_disponible' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'agotado' => 'boolean',
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
     * @deprecated Use InventarioService::consumirLote() instead
     */

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
     *
     * @deprecated Use InventarioService::estaProximoAgotar($lote) instead
     */
    public function estaProximoAgotar()
    {
        return \App\Services\InventarioService::estaProximoAgotar($this);
    }

    /**
     * @deprecated Use InventarioService::stockTotalDisponible() instead
     */

    /**
     * @deprecated Use InventarioService::valorInventario() instead
     */

    /**
     * @deprecated Use InventarioService::proximosAgotar() instead
     */
}
