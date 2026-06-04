<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ParteDiario extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'parte_diarios';

    protected $primaryKey = 'id_parte_diario';

    protected $fillable = [
        'id_lote',
        'id_lote_tarea',
        'fecha',
        'tipo_tarea',
        'es_dia_caido',
        'clima_override',
        'clima_override_motivo',
        'clima_override_confirmado_por',
        'clima_override_confirmado_at',
        'observaciones',
        'costo_mano_obra',
        'costo_insumos',
        'costo_maquinaria',
        'costo_total_dia',
        'costo_unitario_calculado',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function loteTarea()
    {
        return $this->belongsTo(LoteTarea::class, 'id_lote_tarea');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'parte_diario_empleado', 'id_parte_diario', 'id_empleado')->withTimestamps();
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class, 'id_parte_diario');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'id_parte_diario');
    }
}
