<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ultima ejecucion registrada de una tarea automatizada.
 *
 * Una fila por comando: se pisa en cada corrida. El historial completo
 * queda en los logs; aca solo interesa el estado actual.
 */
class EjecucionAutomatizacion extends Model
{
    protected $table = 'ejecuciones_automatizacion';

    protected $fillable = [
        'comando',
        'ultima_ejecucion_at',
        'estado',
        'motivo_error',
        'duracion_ms',
    ];

    protected $casts = [
        'ultima_ejecucion_at' => 'datetime',
        'duracion_ms' => 'integer',
    ];
}
