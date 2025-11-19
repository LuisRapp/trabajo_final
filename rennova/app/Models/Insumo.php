<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Insumo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'insumos';
    protected $primaryKey = 'id_insumo';
    protected $fillable = ['nombre', 'descripcion', 'id_unidad_medida', 'id_proveedor', 'costo_unitario'];
    
    // Agregar stock como atributo calculado
    protected $appends = ['stock'];

    /**
     * Calcula el stock actual basado en movimientos
     */
    public function getStockAttribute()
    {
        $entradas = $this->movimientoStocks()
            ->where('tipo', 'entrada')
            ->sum('cantidad');
            
        $salidas = $this->movimientoStocks()
            ->where('tipo', 'salida')
            ->sum('cantidad');
            
        return $entradas - $salidas;
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
