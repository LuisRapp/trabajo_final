<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class ParteDiario extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'parte_diarios';
    protected $primaryKey = 'id_parte_diario';
    protected $fillable = [
        'id_lote',
        'fecha',
        'es_dia_caido',
        'observaciones',
        'activo'
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'parte_diario_empleado', 'id_parte_diario', 'id_empleado')->withTimestamps();
    }
}
