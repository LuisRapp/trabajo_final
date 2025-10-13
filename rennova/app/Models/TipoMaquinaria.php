<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMaquinaria extends Model
{
    protected $table = 'tipo_maquinarias';
    protected $primaryKey = 'id_tipo_maquinaria';
    protected $fillable = ['nombre'];

    public function maquinarias()
    {
        return $this->hasMany(Maquinaria::class, 'id_tipo_maquinaria');
    }
}
