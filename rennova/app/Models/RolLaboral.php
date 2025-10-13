<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolLaboral extends Model
{
    protected $table = 'rol_laborals';
    protected $primaryKey = 'id_rol_laboral';
    protected $fillable = ['nombre'];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_rol_laboral', 'id_rol_laboral');
    }
}
