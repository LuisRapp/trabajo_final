<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RolLaboral extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'roles_laborales';

    protected $primaryKey = 'id_rol_laboral';

    protected $fillable = ['nombre', 'costo_diario'];
}
