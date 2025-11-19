<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UnidadMedida extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'unidad_medidas';
    protected $primaryKey = 'id_unidad_medida';
    protected $fillable = ['nombre', 'abreviatura'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_unidad_medida');
    }
    

}
