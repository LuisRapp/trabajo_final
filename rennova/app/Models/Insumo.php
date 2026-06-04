<?php

namespace App\Models;

use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Insumo extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'insumos';

    protected $primaryKey = 'id_insumo';

    protected $fillable = ['nombre', 'descripcion', 'id_unidad_medida', 'id_proveedor'];

    // NO usar appends para cálculos costosos
    // protected $appends = ['stock', 'precio_promedio'];

    /**
     * Scope para cargar stock y precio promedio de manera eficiente
     * Usa una única consulta con subqueries
     */
    public function scopeConStockYPrecio($query)
    {
        return $query->addSelect([
            'insumos.*',
            // Stock disponible: suma de cantidad_disponible de lotes no agotados
            'stock' => LoteInventario::selectRaw('COALESCE(SUM(cantidad_disponible), 0)')
                ->whereColumn('lotes_inventario.id_insumo', 'insumos.id_insumo')
                ->where('agotado', false),

            // Precio promedio ponderado: suma(cantidad * precio) / suma(cantidad)
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
     * Accessor para stock (cuando se carga con scopeConStockYPrecio)
     * O calcula dinámicamente si no está disponible
     */
    public function getStockAttribute($value = null)
    {
        // Si ya fue calculado en la query, retornarlo
        if (isset($this->attributes['stock'])) {
            return $this->attributes['stock'];
        }

        // Fallback: calcular dinámicamente (menos eficiente)
        return InventarioService::stockDisponible($this->id_insumo);
    }

    /**
     * Accessor para precio_promedio (cuando se carga con scopeConStockYPrecio)
     * O calcula dinámicamente si no está disponible
     */
    public function getPrecioPromedioAttribute($value = null)
    {
        // Si ya fue calculado en la query, retornarlo
        if (isset($this->attributes['precio_promedio'])) {
            return $this->attributes['precio_promedio'];
        }

        // Fallback: calcular dinámicamente (menos eficiente)
        return InventarioService::precioPromedio($this->id_insumo);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'id_unidad_medida');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function movimientoStocks()
    {
        return $this->hasMany(MovimientoStock::class, 'id_insumo');
    }
}
