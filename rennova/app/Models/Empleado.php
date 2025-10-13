<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';
    protected $fillable = [
        'id_rol_laboral',
        'dni',
        'apellido',
        'nombre',
        'fecha_nacimiento',
        'fecha_inicio_actividades',
        'fecha_fin_actividades'
    ];

    public function rolLaboral()
    {
        return $this->belongsTo(RolLaboral::class, 'id_rol_laboral', 'id_rol_laboral');
    }

    public function adelantos()
    {
        return $this->hasMany(Adelanto::class, 'id_empleado', 'id_empleado');
    }
}
