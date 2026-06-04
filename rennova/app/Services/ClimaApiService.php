<?php

namespace App\Services;

use App\Models\Lote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de API Climática
 * 
 * Responsable de obtener datos de Open-Meteo API
 */
class ClimaApiService
{
    const TIMEZONE = 'America/Argentina/Buenos_Aires';
    const DIAS_FORECAST = 7;

    /**
     * Obtiene pronóstico completo de Open-Meteo API
     * Incluye daily + hourly (precipitación horaria) para 7 días
     */
    public function obtenerPronosticoCompleto(Lote $lote): ?array
    {
        $url = "https://api.open-meteo.com/v1/forecast";

        $params = [
            'latitude' => $lote->latitud,
            'longitude' => $lote->longitud,
            'daily' => 'precipitation_sum,cloudcover_mean,wind_speed_10m_max,et0_fao_evapotranspiration',
            'hourly' => 'precipitation',
            'timezone' => self::TIMEZONE,
            'forecast_days' => self::DIAS_FORECAST,
        ];

        try {
            $response = Http::timeout(10)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("API respondió con status {$response->status()}");
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error("Error al consultar Open-Meteo API", [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene historial climático (observado/reanálisis) para un rango de fechas
     */
    public function obtenerHistoricoCompleto(Lote $lote, string $startDate, string $endDate): ?array
    {
        $url = "https://archive-api.open-meteo.com/v1/archive";

        $params = [
            'latitude' => $lote->latitud,
            'longitude' => $lote->longitud,
            'daily' => 'precipitation_sum,cloud_cover_mean,wind_speed_10m_max,et0_fao_evapotranspiration',
            'hourly' => 'precipitation',
            'timezone' => self::TIMEZONE,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        try {
            $response = Http::timeout(10)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("API respondió con status {$response->status()}");
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error al consultar Open-Meteo Histórico", [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene datos pasados usando el Forecast API (fallback del histórico)
     */
    public function obtenerPronosticoPasado(Lote $lote, int $pastDays, int $forecastDays): ?array
    {
        $url = "https://api.open-meteo.com/v1/forecast";

        $params = [
            'latitude' => $lote->latitud,
            'longitude' => $lote->longitud,
            'daily' => 'precipitation_sum,cloudcover_mean,wind_speed_10m_max,et0_fao_evapotranspiration',
            'hourly' => 'precipitation',
            'timezone' => self::TIMEZONE,
            'past_days' => $pastDays,
            'forecast_days' => $forecastDays,
        ];

        try {
            $response = Http::timeout(10)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("API respondió con status {$response->status()}");
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error al consultar Open-Meteo Forecast (past_days)", [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
