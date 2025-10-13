<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaMadera extends Model
{
    protected $table = 'categorias_madera';
    protected $primaryKey = 'id_categoria_madera';
    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    
}
