<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class KitMantenimientoPreventivo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    
    protected $table = 'kit_mantenimiento_preventivo';
    protected $primaryKey = 'id_kit';
    protected $fillable = [
        'id_tipo_maquinaria', // legado (se mantendrá temporalmente)
        'id_maquinaria',      // nuevo: kit por maquinaria específica
        'id_insumo',
        'cantidad_requerida',
        'es_obligatorio'
    ];

    public function tipoMaquinaria()
    {
        return $this->belongsTo(TipoMaquinaria::class, 'id_tipo_maquinaria');
    }

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }
}
