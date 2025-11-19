<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class RolLaboral extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'rol_laborals';
    protected $primaryKey = 'id_rol_laboral';
    protected $fillable = ['nombre', 'costo_diario', 'activo'];
}
