<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Adelanto extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'adelantos';

    protected $primaryKey = 'id_adelanto';

    protected $fillable = [
        'id_empleado',
        'fecha_emision',
        'monto',
        'monto_descontado',
        'estado',
    ];

    /**
     * Saldo que falta descontar del adelanto.
     */
    public function saldoPendiente(): float
    {
        return max(0, round((float) $this->monto - (float) $this->monto_descontado, 2));
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
