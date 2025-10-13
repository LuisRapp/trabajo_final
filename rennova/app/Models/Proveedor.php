<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedors';
    protected $primaryKey = 'id_proveedor';
    protected $fillable = ['razon_social', 'cuit', 'direccion', 'telefono', 'email'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_proveedor');
    }
}
