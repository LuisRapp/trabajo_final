<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Venta extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'ventas';

    protected $primaryKey = 'id_recibo';

    protected $fillable = [
        'id_empleado',
        'id_cliente',
        'id_proveedor',
        'fecha_emision',
        'monto',
        'observaciones',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function cargas()
    {
        return $this->belongsToMany(Carga::class, 'venta_cargas', 'id_venta', 'id_carga')
            ->withPivot('precio_unitario', 'peso_toneladas', 'subtotal')
            ->withTimestamps();
    }
}
