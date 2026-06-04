<?php

namespace App\Services;

use App\Models\ClimaDiaLote;
use App\Models\Lote;
use Carbon\Carbon;

/**
 * Servicio de Persistencia Climática
 * 
 * Responsable de guardar datos climáticos en la base de datos
 */
class ClimaPersistenciaService
{
    const TIMEZONE = 'America/Argentina/Buenos_Aires';
    const DIAS_FORECAST = 7;

    /**
     * Persiste fallback cuando no hay datos de clima
     */
    public function persistirFallback(Lote $lote, ?string $apiError): void
    {
        $hoy = Carbon::today(self::TIMEZONE);
        $diasDetalle = [];

        for ($i = 0; $i < self::DIAS_FORECAST; $i++) {
            $fecha = $hoy->copy()->addDays($i);
            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'estado' => 'OPERATIVO',
                'razon' => 'Fallback: sin datos de clima',
                'precipitacion_mm' => null,
                'nubosidad' => null,
                'viento_max' => null,
                'et0' => null,
                'lluvia_madrugada_mm' => null,
                'lluvia_diurna_mm' => null,
                'lluvia_nocturna_mm' => null,
                'saturacion_index' => null,
            ];
        }

        $this->persistirDiasDetalle($lote, $diasDetalle, 'fallback', $apiError);
    }

    /**
     * Persiste datos reales de un día específico
     */
    public function persistirDiaReal(Lote $lote, array $diasDetalle, Carbon $fechaObjetivo, string $fuente, ?string $apiError): void
    {
        $fechaStr = $fechaObjetivo->toDateString();
        $diaObjetivo = null;

        foreach ($diasDetalle as $dia) {
            $fecha = $dia['fecha'] ?? null;
            $diaStr = $fecha instanceof Carbon
                ? $fecha->toDateString()
                : Carbon::parse((string) $fecha)->toDateString();

            if ($diaStr === $fechaStr) {
                $diaObjetivo = $dia;
                break;
            }
        }

        if (!$diaObjetivo) {
            $this->persistirRealFallback($lote, $fechaObjetivo, 'No se encontro el dia en el historico.');
            return;
        }

        $estado = strtoupper((string) ($diaObjetivo['estado'] ?? 'OPERATIVO'));
        $razon = $diaObjetivo['razon'] ?? null;
        $precipitacionMm = $diaObjetivo['precipitacion_mm'] ?? null;

        $registro = ClimaDiaLote::where('id_lote', $lote->id_lote)
            ->whereDate('fecha', $fechaStr)
            ->first();

        $snapshot = is_array($registro?->snapshot) ? $registro->snapshot : [];
        $snapshot['real_precipitacion_mm'] = $precipitacionMm;

        $data = [
            'estado_real' => $estado,
            'razon_real' => $razon,
            'fuente_real' => $fuente,
            'api_error_real' => $apiError,
            'real_actualizado_at' => now(),
            'snapshot' => $snapshot,
        ];

        if ($registro) {
            $registro->fill($data)->save();
            return;
        }

        ClimaDiaLote::create([
            'id_lote' => $lote->id_lote,
            'fecha' => $fechaStr,
            'estado_operativo' => $estado,
            'razon' => $razon,
            'fuente' => $fuente,
            'api_error' => null,
            'snapshot' => [
                'real_precipitacion_mm' => $precipitacionMm,
            ],
            'estado_pronostico' => $estado,
            'razon_pronostico' => $razon,
            'fuente_pronostico' => $fuente,
            'api_error_pronostico' => null,
            'pronostico_actualizado_at' => now(),
            'estado_real' => $estado,
            'razon_real' => $razon,
            'fuente_real' => $fuente,
            'api_error_real' => $apiError,
            'real_actualizado_at' => now(),
        ]);
    }

    /**
     * Persiste fallback para datos reales cuando no hay información
     */
    public function persistirRealFallback(Lote $lote, $fecha, ?string $apiError): void
    {
        $fechaObjetivo = $fecha ? Carbon::parse($fecha) : Carbon::now(self::TIMEZONE)->subDay();
        $fechaStr = $fechaObjetivo->toDateString();

        $registro = ClimaDiaLote::where('id_lote', $lote->id_lote)
            ->whereDate('fecha', $fechaStr)
            ->first();

        if ($registro && $registro->estado_real) {
            return;
        }

        $data = [
            'estado_real' => null,
            'razon_real' => 'Sin datos reales',
            'fuente_real' => 'fallback',
            'api_error_real' => $apiError,
            'real_actualizado_at' => now(),
        ];

        if ($registro) {
            $registro->fill($data)->save();
            return;
        }

        ClimaDiaLote::create([
            'id_lote' => $lote->id_lote,
            'fecha' => $fechaStr,
            'estado_operativo' => 'OPERATIVO',
            'razon' => 'Fallback real: sin datos',
            'fuente' => 'fallback',
            'api_error' => $apiError,
            'snapshot' => null,
            'estado_pronostico' => 'OPERATIVO',
            'razon_pronostico' => 'Fallback real: sin datos',
            'fuente_pronostico' => 'fallback',
            'api_error_pronostico' => $apiError,
            'pronostico_actualizado_at' => now(),
            'estado_real' => null,
            'razon_real' => 'Sin datos reales',
            'fuente_real' => 'fallback',
            'api_error_real' => $apiError,
            'real_actualizado_at' => now(),
        ]);
    }

    /**
     * Persiste detalle de días de pronóstico
     */
    public function persistirDiasDetalle(Lote $lote, array $diasDetalle, string $fuente, ?string $apiError): void
    {
        foreach ($diasDetalle as $dia) {
            $fecha = $dia['fecha'] ?? null;
            $fechaStr = $fecha instanceof Carbon
                ? $fecha->toDateString()
                : Carbon::parse((string) $fecha)->toDateString();

            $estado = strtoupper((string) ($dia['estado'] ?? 'OPERATIVO'));
            $razon = $dia['razon'] ?? null;

            $snapshot = [
                'precipitacion_mm' => $dia['precipitacion_mm'] ?? null,
                'nubosidad' => $dia['nubosidad'] ?? null,
                'viento_max' => $dia['viento_max'] ?? null,
                'et0' => $dia['et0'] ?? null,
                'lluvia_madrugada_mm' => $dia['lluvia_madrugada_mm'] ?? null,
                'lluvia_diurna_mm' => $dia['lluvia_diurna_mm'] ?? null,
                'lluvia_nocturna_mm' => $dia['lluvia_nocturna_mm'] ?? null,
                'saturacion_index' => $dia['saturacion_index'] ?? null,
            ];

            $existente = ClimaDiaLote::where('id_lote', $lote->id_lote)
                ->whereDate('fecha', $fechaStr)
                ->first();

            $fuenteActual = $existente?->fuente_pronostico ?? $existente?->fuente;
            if ($existente && $fuenteActual === 'api' && $fuente === 'fallback') {
                continue;
            }

            $data = [
                'id_lote' => $lote->id_lote,
                'fecha' => $fechaStr,
                'estado_operativo' => $estado,
                'razon' => $razon,
                'fuente' => $fuente,
                'api_error' => $fuente === 'fallback' ? $apiError : null,
                'snapshot' => $snapshot,
                'estado_pronostico' => $estado,
                'razon_pronostico' => $razon,
                'fuente_pronostico' => $fuente,
                'api_error_pronostico' => $fuente === 'fallback' ? $apiError : null,
                'pronostico_actualizado_at' => now(),
            ];

            if ($existente) {
                $existente->fill($data)->save();
            } else {
                ClimaDiaLote::create($data);
            }
        }
    }
}
