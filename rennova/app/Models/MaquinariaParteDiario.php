<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaquinariaParteDiario extends Model
{
    protected $table = 'maquinarias_partes_diarios';
    protected $primaryKey = 'id_maquinaria_parte_diario';
    protected $fillable = [
        'id_parte_diario',
        'tipo_maquinaria',
        'horas_uso',
        'operador',
    ];

    public function parteDiario()
    {
        return $this->belongsTo(ParteDiario::class, 'id_parte_diario');
    }
}
