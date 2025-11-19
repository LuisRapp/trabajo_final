<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Maquinaria extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'maquinarias';
    protected $primaryKey = 'id_maquinaria';
    protected $fillable = [
        'id_tipo_maquinaria',
        'modelo',
        'estado',
        'es_alquilada',
        'fecha_inicio_actividades',
        'toneladas_acumuladas',
        'umbral_toneladas'
    ];
        
    public function tipoMaquinaria()
    {
        return $this->belongsTo(TipoMaquinaria::class, 'id_tipo_maquinaria');
    }
}
