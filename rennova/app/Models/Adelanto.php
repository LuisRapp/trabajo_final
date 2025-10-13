<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adelanto extends Model
{
    protected $table = 'adelantos';
    protected $primaryKey = 'id_adelanto';
    protected $fillable = [
        'id_empleado',
        'monto',
        'fecha_adelanto'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
