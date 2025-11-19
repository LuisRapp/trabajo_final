<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    protected $table = 'movimiento_stocks';
    protected $primaryKey = 'id_movimiento_stock';
    protected $fillable = ['id_insumo', 'tipo', 'cantidad', 'fecha', 'motivo'];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    
}
