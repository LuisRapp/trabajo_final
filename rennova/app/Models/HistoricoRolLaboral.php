<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoRolLaboral extends Model
{
    protected $table = 'historico_rol_laborals';
    protected $fillable = [
        'empleado_id',
        'rol_laboral_id',
        'costo_diario',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cambio'
    ];
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'id_empleado');
    }
    public function rolLaboral()
        {
        return $this->belongsTo(RolLaboral::class, 'rol_laboral_id', 'id_rol_laboral');}
}
