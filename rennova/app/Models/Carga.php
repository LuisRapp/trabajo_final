<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    protected $table = 'cargas';
    protected $primaryKey = 'id_carga';
    protected $fillable = [
        'id_parte_diario',
        'id_categoria_madera',
        'volumen',
        'peso',
    ];

    public function parteDiario()
    {
        return $this->belongsTo(ParteDiario::class, 'id_parte_diario');
    }

    public function categoriaMadera()
    {
        return $this->belongsTo(CategoriaMadera::class, 'id_categoria_madera');
    }
}
