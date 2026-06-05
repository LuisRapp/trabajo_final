<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HistoricoRolLaboral extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

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

    /**
     * Filter to records active on a given date.
     */
    public function scopeVigenteEnFecha($query, $fecha): void
    {
        $query->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha);
            })
            ->orderBy('fecha_inicio', 'desc');
    }
}
