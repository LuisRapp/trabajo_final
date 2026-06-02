<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClimaDiaLote extends Model
{
    use HasFactory;

    protected $table = 'clima_dias_lote';
    protected $primaryKey = 'id_clima_dia_lote';

    protected $fillable = [
        'id_lote',
        'fecha',
        'estado_operativo',
        'razon',
        'fuente',
        'api_error',
        'snapshot',
        'estado_pronostico',
        'razon_pronostico',
        'fuente_pronostico',
        'api_error_pronostico',
        'pronostico_actualizado_at',
        'estado_real',
        'razon_real',
        'fuente_real',
        'api_error_real',
        'real_actualizado_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'snapshot' => 'array',
        'pronostico_actualizado_at' => 'datetime',
        'real_actualizado_at' => 'datetime',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }
}
