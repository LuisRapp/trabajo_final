<?php

namespace App\Services;

use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Decisiones Climáticas (Orquestador)
 * 
 * Coordina los servicios especializados para analizar clima y generar recomendaciones
 * operativas para lotes forestales en tres fases: Anticipación, Bloqueo y Reacción.
 */
class ClimaDecisionService
{
    const TIMEZONE = 'America/Argentina/Buenos_Aires';

    protected ClimaApiService $apiService;
    protected ClimaAnalisisService $analisisService;
    protected ClimaEstrategiaService $estrategiaService;
    protected ClimaPersistenciaService $persistenciaService;

    public function __construct(
        ClimaApiService $apiService,
        ClimaAnalisisService $analisisService,
        ClimaEstrategiaService $estrategiaService,
        ClimaPersistenciaService $persistenciaService
    ) {
        $this->apiService = $apiService;
        $this->analisisService = $analisisService;
        $this->estrategiaService = $estrategiaService;
        $this->persistenciaService = $persistenciaService;
    }

    /**
     * Método principal: Analiza clima y genera recomendaciones
     * 
     * @param Lote $lote Lote a analizar
     * @return array [
     *   'success' => bool,
     *   'pronostico' => array,
     *   'dias_inactivos' => array,
     *   'estrategia' => string,
     *   'recomendacion' => string,
     *   'datos_calculados' => array
     * ]
     */
    public function analizarYRecomendar(Lote $lote): array
    {
        try {
            // Validar que el lote tenga coordenadas
            if (!$lote->latitud || !$lote->longitud) {
                $this->persistenciaService->persistirFallback($lote, 'Lote sin coordenadas GPS configuradas.');
                return [
                    'success' => false,
                    'error' => 'El lote no tiene coordenadas GPS configuradas.',
                    'sugerencia' => 'Agregue latitud y longitud desde el menú de gestión de lotes.',
                ];
            }

            // 1. Obtener pronóstico de Open-Meteo
            $pronostico = $this->apiService->obtenerPronosticoCompleto($lote);

            if (!$pronostico) {
                $this->persistenciaService->persistirFallback($lote, 'No se pudo obtener el pronóstico climático.');
                return [
                    'success' => false,
                    'error' => 'No se pudo obtener el pronóstico climático.',
                    'sugerencia' => 'Verifique la conexión a internet y las coordenadas del lote.',
                ];
            }

            // 2. PASO A: Mapeo de Días Inactivos (Futuro)
            $analisisDias = $this->analisisService->mapearDiasInactivos($pronostico);
            $this->persistenciaService->persistirDiasDetalle($lote, $analisisDias['dias_detalle'], 'api', null);

            // 3. Determinar estrategia según ventanas operativas disponibles
            return $this->estrategiaService->determinarEstrategia($lote, $analisisDias);

        } catch (\Exception $e) {
            Log::error("Error en ClimaDecisionService::analizarYRecomendar", [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Error interno al analizar clima.',
                'detalle' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza pronóstico de un lote
     */
    public function sincronizarPronostico(Lote $lote): array
    {
        if (!$lote->latitud || !$lote->longitud) {
            $this->persistenciaService->persistirFallback($lote, 'Lote sin coordenadas GPS configuradas.');
            return [
                'success' => false,
                'error' => 'El lote no tiene coordenadas GPS configuradas.',
            ];
        }

        $pronostico = $this->apiService->obtenerPronosticoCompleto($lote);

        if (!$pronostico) {
            $this->persistenciaService->persistirFallback($lote, 'No se pudo obtener el pronostico climatico.');
            return [
                'success' => false,
                'error' => 'No se pudo obtener el pronostico climatico.',
            ];
        }

        $analisisDias = $this->analisisService->mapearDiasInactivos($pronostico);
        $this->persistenciaService->persistirDiasDetalle($lote, $analisisDias['dias_detalle'], 'api', null);

        return [
            'success' => true,
            'dias_detalle' => $analisisDias['dias_detalle'],
        ];
    }

    /**
     * Sincroniza datos reales de un lote
     */
    public function sincronizarReal(Lote $lote, $fecha = null): array
    {
        if (!$lote->latitud || !$lote->longitud) {
            $this->persistenciaService->persistirRealFallback($lote, $fecha, 'Lote sin coordenadas GPS configuradas.');
            return [
                'success' => false,
                'error' => 'El lote no tiene coordenadas GPS configuradas.',
            ];
        }

        $fechaObjetivo = $fecha
            ? Carbon::parse($fecha)
            : Carbon::now(self::TIMEZONE)->subDay();

        $startDate = $fechaObjetivo->copy()->subDay()->toDateString();
        $endDate = $fechaObjetivo->toDateString();

        $historico = $this->apiService->obtenerHistoricoCompleto($lote, $startDate, $endDate);
        $fuente = 'archive';

        if (!$historico) {
            $historico = $this->apiService->obtenerPronosticoPasado($lote, 2, 1);
            $fuente = 'forecast';
        }

        if (!$historico) {
            $this->persistenciaService->persistirRealFallback($lote, $fechaObjetivo, 'No se pudo obtener el historico climatico.');
            return [
                'success' => false,
                'error' => 'No se pudo obtener el historico climatico.',
            ];
        }

        $analisisDias = $this->analisisService->mapearDiasInactivos($historico, true);
        $this->persistenciaService->persistirDiaReal($lote, $analisisDias['dias_detalle'], $fechaObjetivo, $fuente, null);

        return [
            'success' => true,
            'fecha' => $fechaObjetivo->toDateString(),
        ];
    }
}
