<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    protected $primaryKey = 'id_asignacion';
    protected $fillable = [
        'id_empleado',
        'id_proyecto',
        'fecha_inicio',
        'fecha_fin',
        'rol',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto');
    }
}
