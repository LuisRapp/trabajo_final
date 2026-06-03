<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HistoricoCostosMaquinaria extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'historico_costos_maquinarias';

    protected $primaryKey = 'id_costo';

    protected $fillable = [
        'id_maquinaria',
        'costo_por_tonelada',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
    ];

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }
}
