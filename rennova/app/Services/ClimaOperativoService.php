<?php

namespace App\Services;

use App\Models\ClimaDiaLote;
use App\Models\Lote;
use Carbon\Carbon;

class ClimaOperativoService
{
    public function obtenerEstadoDia(Lote $lote, $fecha): ClimaDiaLote
    {
        $fechaStr = Carbon::parse($fecha)->toDateString();

        $registro = ClimaDiaLote::where('id_lote', $lote->id_lote)
            ->whereDate('fecha', $fechaStr)
            ->first();

        $esHoy = Carbon::parse($fechaStr)->isToday();
        $pronosticoActualizado = $registro?->pronostico_actualizado_at;
        $pronosticoVigente = $pronosticoActualizado && Carbon::parse($pronosticoActualizado)->isToday();

        if ($registro && (!$esHoy || $pronosticoVigente)) {
            return $registro;
        }

        $sync = app(ClimaDecisionService::class)->sincronizarPronostico($lote);

        if ($sync['success']) {
            $registro = ClimaDiaLote::where('id_lote', $lote->id_lote)
                ->whereDate('fecha', $fechaStr)
                ->first();
        }

        if (!$registro) {
            $registro = $this->crearFallbackDia($lote, $fechaStr, $sync['error'] ?? null);
        }

        return $registro;
    }

    private function crearFallbackDia(Lote $lote, string $fechaStr, ?string $error): ClimaDiaLote
    {
        return ClimaDiaLote::updateOrCreate(
            [
                'id_lote' => $lote->id_lote,
                'fecha' => $fechaStr,
            ],
            [
                'estado_operativo' => 'OPERATIVO',
                'razon' => 'Fallback: sin datos de clima',
                'fuente' => 'fallback',
                'api_error' => $error,
                'snapshot' => null,
                'estado_pronostico' => 'OPERATIVO',
                'razon_pronostico' => 'Fallback: sin datos de clima',
                'fuente_pronostico' => 'fallback',
                'api_error_pronostico' => $error,
                'pronostico_actualizado_at' => now(),
            ]
        );
    }
}
