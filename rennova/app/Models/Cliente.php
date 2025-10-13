<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    protected $fillable = [
        'razon_social',
        'cuit',
        'direccion',
        'contacto'
    ];
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }
}
