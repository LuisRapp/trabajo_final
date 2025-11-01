<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Adelanto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'adelantos';
    protected $primaryKey = 'id_adelanto';
    protected $fillable = [
        'id_empleado',
        'fecha_emision',
        'monto',
        'estado',
        'activo'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
