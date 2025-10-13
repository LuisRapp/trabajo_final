<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $table = 'recibos';
    protected $primaryKey = 'id_recibo';
    protected $fillable = [
        'id_empleado',
        'periodo',
        'monto_bruto',
        'monto_neto',
        'fecha_emision'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
