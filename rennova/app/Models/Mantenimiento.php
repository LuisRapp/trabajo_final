<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Mantenimiento extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'mantenimientos';
    protected $primaryKey = 'id_mantenimiento';
    protected $fillable = [
        'id_maquinaria',
        'id_tipo_mantenimiento',
        'fecha_inicio',
        'fecha_programada',
        'fecha_fin',
        'costo_total',
        'estado',
        'toneladas_snapshot',
        'costo_mano_obra'
    ];

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }

    public function tipoMantenimiento()
    {
        return $this->belongsTo(TipoMantenimiento::class, 'id_tipo_mantenimiento');
    }

    public function mantenimientoInsumos()
    {
        return $this->hasMany(MantenimientoInsumo::class, 'id_mantenimiento');
    }
}
