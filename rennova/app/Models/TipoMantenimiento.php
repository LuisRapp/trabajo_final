<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TipoMantenimiento extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'tipo_mantenimientos';
    protected $primaryKey = 'id_tipo_mantenimiento';
    protected $fillable = ['nombre', 'activo'];
    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_tipo_mantenimiento');
    }
}
