<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Proveedor extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    protected $fillable = ['razon_social', 'cuit', 'direccion', 'telefono', 'email'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_proveedor');
    }
}
