<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaAsignacionEmpleado extends Model
{
    use HasFactory;

    protected $table = 'allocation_proposal_employees';

    protected $primaryKey = 'id_allocation_proposal_employee';

    protected $fillable = [
        'id_allocation_proposal',
        'id_empleado',
        'rol_sugerido',
        'score',
        'selected',
    ];

    protected $casts = [
        'selected' => 'boolean',
        'score' => 'decimal:4',
    ];

    public function proposal()
    {
        return $this->belongsTo(PropuestaAsignacion::class, 'id_allocation_proposal');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
