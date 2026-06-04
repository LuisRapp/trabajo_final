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
     * Accessor para stock
     * Calcula dinámicamente usando el servicio de inventario
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
