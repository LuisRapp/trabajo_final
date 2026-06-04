<?php

namespace App\Models;

use App\Models\Traits\CalculaCostosLaborales;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Empleado extends Model implements Auditable
{
    use CalculaCostosLaborales;
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'empleados';

    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'id_rol_laboral',
        'dni',
        'apellido',
        'nombre',
        'email',
        'fecha_nacimiento',
        'fecha_inicio_actividades',
        'fecha_fin_actividades',
    ];

    public function rolLaboral()
    {
        return $this->belongsTo(RolLaboral::class, 'id_rol_laboral', 'id_rol_laboral');
    }

    public function adelantos()
    {
        return $this->hasMany(Adelanto::class, 'id_empleado', 'id_empleado');
    }

    public function cargas()
    {
        return $this->belongsToMany(Carga::class, 'carga_empleado', 'id_empleado', 'id_carga')->withTimestamps();
    }

    public function partesDiarios()
    {
        return $this->belongsToMany(ParteDiario::class, 'parte_diario_empleado', 'id_empleado', 'id_parte_diario')->withTimestamps();
    }

    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'id_empleado', 'id_empleado');
    }

    public function mantenimientos()
    {
        return $this->belongsToMany(
            Mantenimiento::class,
            'mantenimiento_empleado',
            'id_empleado',
            'id_mantenimiento'
        )->withPivot('rol_origen')->withTimestamps();
    }

    /**
     * @deprecated Use EmpleadoPagoService::calcularPagoRango() instead
     */
}
