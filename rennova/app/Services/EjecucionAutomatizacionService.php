<?php

namespace App\Services;

use App\Models\EjecucionAutomatizacion;
use Illuminate\Console\Scheduling\Event;
use Throwable;

/**
 * Registra el resultado de la ultima ejecucion de cada automatizacion.
 *
 * Se alimenta de los eventos del scheduler y de las ejecuciones manuales.
 */
class EjecucionAutomatizacionService
{
    public function registrar(string $comando, string $estado, ?string $motivo = null, ?int $duracionMs = null): void
    {
        EjecucionAutomatizacion::updateOrCreate(
            ['comando' => $comando],
            [
                'ultima_ejecucion_at' => now(),
                'estado' => $estado,
                'motivo_error' => $motivo,
                'duracion_ms' => $duracionMs,
            ]
        );
    }

    /**
     * @return array<string, EjecucionAutomatizacion>
     */
    public function porComando(): array
    {
        return EjecucionAutomatizacion::query()->get()->keyBy('comando')->all();
    }

    /**
     * Nombre estable de la tarea a partir del evento del scheduler.
     */
    public function comandoDe(Event $tarea): string
    {
        $comando = trim((string) $tarea->command);

        // El scheduler arma la linea como '<php>' 'artisan' <comando> [args].
        if (preg_match('/artisan[\'"]?\s+[\'"]?([^\s\'"]+)/i', $comando, $m)) {
            return $m[1];
        }

        $comando = trim($comando, "'\"");
        $partes = explode(' ', trim($comando));

        return $partes[0] ?? '';
    }

    public function motivoDe(Throwable $excepcion): string
    {
        $mensaje = trim($excepcion->getMessage());

        return mb_substr($mensaje !== '' ? $mensaje : $excepcion::class, 0, 500);
    }
}
