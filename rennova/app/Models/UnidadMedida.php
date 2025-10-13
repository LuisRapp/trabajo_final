<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidad_medidas';
    protected $primaryKey = 'id_unidad_medida';
    protected $fillable = ['nombre', 'abreviatura'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_unidad_medida');
    }
    

}
