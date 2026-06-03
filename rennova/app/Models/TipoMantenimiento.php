<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TipoMantenimiento extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'tipo_mantenimientos';

    protected $primaryKey = 'id_tipo_mantenimiento';

    protected $fillable = ['nombre'];

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_tipo_mantenimiento');
    }
}
