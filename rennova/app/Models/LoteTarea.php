<?php

namespace App\Models;

use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoteTarea extends Model
{
    use HasFactory;

    protected $table = 'lote_tareas';
    protected $primaryKey = 'id_lote_tarea';

    protected $fillable = [
        'id_lote',
        'tipo_tarea',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'superficie_afectada_ha',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'superficie_afectada_ha' => 'decimal:2',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function partesDiarios()
    {
        return $this->hasMany(ParteDiario::class, 'id_lote_tarea');
    }

    public function getTipoTareaLabelAttribute(): string
    {
        $enum = TaskType::tryFrom((string) $this->tipo_tarea);
        return $enum ? $enum->label() : (string) $this->tipo_tarea;
    }
}
