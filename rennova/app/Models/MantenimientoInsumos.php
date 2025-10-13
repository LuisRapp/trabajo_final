<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoInsumos extends Model
{
    protected $table = 'mantenimiento_insumos';
    protected $primaryKey = 'id_mantenimiento_insumo';
    protected $fillable = [
        'id_mantenimiento',
        'id_insumo',
        'id_movimiento',
        'cantidad_utilizada'
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'id_mantenimiento');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    public function movimiento()
    {
        return $this->belongsTo(MovimientoStock::class, 'id_movimiento');
    }

}
