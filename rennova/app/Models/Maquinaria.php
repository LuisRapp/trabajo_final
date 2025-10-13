<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maquinaria extends Model
{
    protected $table = 'maquinarias';
    protected $primaryKey = 'id_maquinaria';
    protected $fillable = [
        'id_tipo_maquinaria',
        'modelo',
        'estado',
        'es_alquilada',
        'fecha_inicio_actividades'];
        
    public function tipoMaquinaria()
    {
        return $this->belongsTo(TipoMaquinaria::class, 'id_tipo_maquinaria');
    }
}
