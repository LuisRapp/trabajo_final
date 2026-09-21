<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropuestaCompraMantenimientoInsumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'propuestas_compra_mantenimiento_insumos';

    protected $primaryKey = 'id_propuesta_compra_mantenimiento_insumo';

    protected $fillable = [
        'id_propuesta_compra_mantenimiento',
        'id_insumo',
        'cantidad_requerida',
        'stock_disponible',
        'faltante',
    ];

    public function propuesta()
    {
        return $this->belongsTo(PropuestaCompraMantenimiento::class, 'id_propuesta_compra_mantenimiento');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }
}
