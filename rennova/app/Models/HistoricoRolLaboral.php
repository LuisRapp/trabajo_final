<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HistoricoRolLaboral extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'historico_roles_laborales';

    protected $fillable = [
        'rol_laboral_id',
        'precio_tonelada',
        'jornal_diario',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cambio',
    ];

    public function rolLaboral()
    {
        return $this->belongsTo(RolLaboral::class, 'rol_laboral_id', 'id_rol_laboral');
    }
}
