<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Cliente extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'razon_social',
        'cuit',
        'direccion',
        'contacto',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }

    public function choferes()
    {
        return $this->hasMany(Chofer::class, 'id_cliente');
    }
}
