<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'reportes';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'tipo',
        'parametros',
        'formato',
        'ruta_archivo',
        'fecha_generacion',
    ];

    protected $casts = [
        'parametros' => 'array',
        'fecha_generacion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
