<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CategoriaMadera extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;
    
    protected $table = 'categoria_maderas';
    protected $primaryKey = 'id_categoria_madera';
    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    
}
