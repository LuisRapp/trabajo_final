<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class HistoricoRolLaboral extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'historico_rol_laborals';
    protected $fillable = [
        'rol_laboral_id',
        'precio_tonelada',
        'jornal_diario',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cambio'
    ];
    public function rolLaboral()
    {
        return $this->belongsTo(RolLaboral::class, 'rol_laboral_id', 'id_rol_laboral');
    }
}
