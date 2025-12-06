<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class Proveedor extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;
    
    protected $table = 'proveedors';
    protected $primaryKey = 'id_proveedor';
    protected $fillable = ['razon_social', 'cuit', 'direccion', 'telefono', 'email'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_proveedor');
    }
}
