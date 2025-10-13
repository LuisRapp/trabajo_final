<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParteDiario extends Model
{
    protected $table = 'partes_diarios';
    protected $primaryKey = 'id_parte_diario';
    protected $fillable = [
        'id_lote',
        'fecha',
        'actividad_realizada',
        'cantidad_trabajadores',
        'horas_trabajadas',
        'observaciones',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }
}
