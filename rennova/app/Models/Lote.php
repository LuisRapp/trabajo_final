<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Lote extends Model implements AuditableContract
{
    use Auditable;
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    protected $fillable = [
        'propietario',
        'condicion_compra',
        'estado',
        'ubicacion',
        'especie',
        'superficie',
    ];
    public function parteDiarios()
    {
        return $this->hasMany(ParteDiario::class, 'id_lote');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'asignacions', 'id_lote', 'id_empleado')
                    ->withTimestamps();
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class, 'id_lote');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_lote');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'id_lote');
    }
}
