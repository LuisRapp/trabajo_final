<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    protected $fillable = [
        'propietario',
        'condicion_compra',
        'estado',
        'ubicacion',
        'especie',
        'superficie',
    ];
    public function parteDiarios()
    {
        return $this->hasMany(ParteDiario::class, 'id_lote');
    }

    
}
