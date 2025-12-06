<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class UnidadMedida extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;
    
    protected $table = 'unidad_medidas';
    protected $primaryKey = 'id_unidad_medida';
    protected $fillable = ['nombre', 'abreviatura'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_unidad_medida');
    }
    

}
