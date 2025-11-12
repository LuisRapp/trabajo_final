<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TipoMaquinaria extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'tipo_maquinarias';
    protected $primaryKey = 'id_tipo_maquinaria';
    protected $fillable = ['nombre', 'umbral_toneladas'];

    public function maquinarias()
    {
        return $this->hasMany(Maquinaria::class, 'id_tipo_maquinaria');
    }

    public function kitsPreventivos()
    {
        return $this->hasMany(KitMantenimientoPreventivo::class, 'id_tipo_maquinaria');
    }
}
