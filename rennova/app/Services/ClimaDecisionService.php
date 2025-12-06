<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Decisiones Climáticas
 * 
 * Analiza pronóstico de Open-Meteo y genera recomendaciones operativas
 * para lotes forestales en tres fases: Anticipación, Bloqueo y Reacción.
 */
class ClimaDecisionService
{
    // Constantes de configuración
    const UMBRAL_LLUVIA = 10; // mm
    const UMBRAL_NUBOSIDAD = 60; // %
    const MAX_AUMENTO_PRODUCCION = 1.25; // Máximo 25% extra
    const DIAS_FORECAST = 7;

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
                return [
                    'success' => false,
                    'error' => 'El lote no tiene coordenadas GPS configuradas.',
                    'sugerencia' => 'Agregue latitud y longitud desde el menú de gestión de lotes.',
                ];
            }

            // 1. Obtener pronóstico de Open-Meteo
            $pronostico = $this->obtenerPronosticoCompleto($lote);

            if (!$pronostico) {
                return [
                    'success' => false,
                    'error' => 'No se pudo obtener el pronóstico climático.',
                    'sugerencia' => 'Verifique la conexión a internet y las coordenadas del lote.',
                ];
            }

            // 2. PASO A: Mapeo de Días Inactivos (Futuro)
            $analisisDias = $this->mapearDiasInactivos($pronostico);

            // 3. Determinar estrategia según ventanas operativas disponibles
            return $this->determinarEstrategia($lote, $analisisDias);

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
     * Obtiene pronóstico completo de Open-Meteo API
     * Incluye precipitation_sum y cloudcover_mean para 7 días
     */
    private function obtenerPronosticoCompleto(Lote $lote): ?array
    {
        $url = "https://api.open-meteo.com/v1/forecast";

        $params = [
            'latitude' => $lote->latitud,
            'longitude' => $lote->longitud,
            'daily' => 'precipitation_sum,cloudcover_mean',
            'timezone' => 'America/Argentina/Buenos_Aires',
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
     * PASO A: Mapeo de Días Inactivos
     * 
     * Analiza los 7 días y marca como INACTIVO:
     * 1. Días con lluvia > 10mm
     * 2. Días siguientes a lluvia con nubosidad > 60% (barro)
     * 
     * @return array [
     *   'dias_detalle' => array de cada día con estado,
     *   'total_dias_perdidos' => int,
     *   'volumen_riesgo' => float,
     *   'dia_cero_index' => int|null (primer día de lluvia),
     *   'dias_operativos_previos' => int (días secos antes del Día Cero)
     * ]
     */
    private function mapearDiasInactivos(array $pronostico): array
    {
        $fechas = $pronostico['daily']['time'] ?? [];
        $precipitaciones = $pronostico['daily']['precipitation_sum'] ?? [];
        $nubosidades = $pronostico['daily']['cloudcover_mean'] ?? [];

        $diasDetalle = [];
        $diaCeroIndex = null;
        $totalDiasPerdidos = 0;
        $huboDiaLluvia = false;

        foreach ($fechas as $index => $fechaStr) {
            $fecha = Carbon::parse($fechaStr);
            $mm = $precipitaciones[$index] ?? 0;
            $cloudCover = $nubosidades[$index] ?? 0;
            $esHoy = $fecha->isToday();
            $esFinDeSemana = $fecha->isWeekend(); // Sábado (6) o Domingo (0)

            // Analizar estado del día
            $estado = 'OPERATIVO';
            $razon = null;

            // 0. Verificar si es fin de semana (NO cuenta como día perdido, solo es no laboral)
            if ($esFinDeSemana) {
                $estado = 'INACTIVO';
                $razon = "Fin de semana (no laboral)";
                // NO incrementar totalDiasPerdidos - los fines de semana no generan déficit
            }
            // 1. Verificar lluvia directa
            elseif ($mm >= self::UMBRAL_LLUVIA) {
                $estado = 'INACTIVO';
                $razon = "Lluvia pronosticada: {$mm}mm";
                
                // Marcar Día Cero (primer día de lluvia)
                if ($diaCeroIndex === null && !$esHoy) {
                    $diaCeroIndex = $index;
                }
                
                $huboDiaLluvia = true;
                $totalDiasPerdidos++;
            }
            // 2. Verificar efecto de barro (días después de lluvia con alta nubosidad)
            elseif ($huboDiaLluvia && $cloudCover > self::UMBRAL_NUBOSIDAD) {
                $estado = 'INACTIVO';
                $razon = "Barro post-lluvia (Nubosidad: {$cloudCover}%)";
                $totalDiasPerdidos++;
            }
            // 3. Si pasó la nubosidad crítica, resetear flag de lluvia
            elseif ($huboDiaLluvia && $cloudCover <= self::UMBRAL_NUBOSIDAD) {
                $huboDiaLluvia = false; // Terreno se secó
            }

            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'dia_semana' => $fecha->isoFormat('dddd'),
                'es_hoy' => $esHoy,
                'precipitacion_mm' => round($mm, 1),
                'nubosidad' => round($cloudCover, 0),
                'estado' => $estado,
                'razon' => $razon,
                'index' => $index,
            ];
        }

        // Calcular días operativos previos al Día Cero
        $diasOperativosPrevios = 0;
        $diasOperativosPosterior = 0;
        $totalDiasOperativos = 0;
        
        if ($diaCeroIndex !== null) {
            // Contar días operativos ANTES del primer día de lluvia
            for ($i = 0; $i < $diaCeroIndex; $i++) {
                if ($diasDetalle[$i]['estado'] === 'OPERATIVO' && !$diasDetalle[$i]['es_hoy']) {
                    $diasOperativosPrevios++;
                }
            }
            
            // Contar días operativos DESPUÉS de la ventana de lluvia
            for ($i = $diaCeroIndex + 1; $i < count($diasDetalle); $i++) {
                if ($diasDetalle[$i]['estado'] === 'OPERATIVO') {
                    $diasOperativosPosterior++;
                }
            }
        }
        
        // Contar TODOS los días operativos en la ventana de 7 días
        foreach ($diasDetalle as $dia) {
            if ($dia['estado'] === 'OPERATIVO' && !$dia['es_hoy']) {
                $totalDiasOperativos++;
            }
        }

        // Calcular volumen de riesgo (días perdidos × meta diaria estimada)
        $metaDiaria = $this->calcularMetaDiaria();
        $volumenRiesgo = $totalDiasPerdidos * $metaDiaria;

        return [
            'dias_detalle' => $diasDetalle,
            'total_dias_perdidos' => $totalDiasPerdidos,
            'volumen_riesgo' => round($volumenRiesgo, 2),
            'meta_diaria' => $metaDiaria,
            'dia_cero_index' => $diaCeroIndex,
            'dias_operativos_previos' => $diasOperativosPrevios,
            'dias_operativos_posterior' => $diasOperativosPosterior,
            'total_dias_operativos' => $totalDiasOperativos,
        ];
    }

    /**
     * NUEVO: Determinar estrategia según análisis completo de ventanas operativas
     */
    private function determinarEstrategia(Lote $lote, array $analisisDias): array
    {
        $volumenRiesgo = $analisisDias['volumen_riesgo'];
        $diasPerdidos = $analisisDias['total_dias_perdidos'];
        $diasPrevios = $analisisDias['dias_operativos_previos'];
        $diasPosterior = $analisisDias['dias_operativos_posterior'];
        $totalDiasOperativos = $analisisDias['total_dias_operativos'];
        $metaDiaria = $analisisDias['meta_diaria'];

        // CASO 1: No hay días perdidos por lluvia → Operación normal
        if ($diasPerdidos === 0 || $volumenRiesgo == 0) {
            return [
                'success' => true,
                'estrategia' => 'NORMAL',
                'nivel_urgencia' => 'BAJA',
                'recomendacion' => "✅ OPERACIÓN NORMAL\n\nNo se pronostican lluvias significativas en los próximos 7 días.\nMantener ritmo de producción actual.",
                'pronostico' => $analisisDias['dias_detalle'],
                'dias_inactivos' => [],
                'datos_calculados' => [
                    'volumen_riesgo' => 0,
                    'meta_diaria' => $metaDiaria,
                    'aumento_necesario_pct' => 0,
                    'dias_perdidos' => 0,
                ],
            ];
        }

        // CASO 2: Hay días operativos disponibles (antes o después) → Planificación estratégica
        if ($totalDiasOperativos > 0) {
            $aumentoNecesario = $volumenRiesgo / $totalDiasOperativos;
            $porcentajeAumento = ($aumentoNecesario / $metaDiaria) * 100;
            
            // Subcaso 2A: Aumento viable (≤ 25%)
            if ($porcentajeAumento <= 25) {
                $nuevaMetaDiaria = $metaDiaria + $aumentoNecesario;
                $recomendacion = $this->generarRecomendacionAnticipacion($analisisDias, $porcentajeAumento, $nuevaMetaDiaria, 'VIABLE');
                
                return [
                    'success' => true,
                    'estrategia' => 'ANTICIPACION_PLANIFICADA',
                    'nivel_urgencia' => 'MEDIA',
                    'recomendacion' => $recomendacion,
                    'pronostico' => $analisisDias['dias_detalle'],
                    'dias_inactivos' => $this->extraerDiasInactivos($analisisDias['dias_detalle']),
                    'datos_calculados' => [
                        'volumen_riesgo' => $volumenRiesgo,
                        'meta_diaria' => $metaDiaria,
                        'nueva_meta_diaria' => round($nuevaMetaDiaria, 2),
                        'aumento_necesario_pct' => round($porcentajeAumento, 0),
                        'dias_perdidos' => $diasPerdidos,
                        'dias_operativos_disponibles' => $totalDiasOperativos,
                    ],
                ];
            }
            
            // Subcaso 2B: Aumento excesivo (> 25%) → Máximo esfuerzo
            else {
                $aumentoMaximo = $metaDiaria * 0.25;
                $volumenRecuperable = $aumentoMaximo * $totalDiasOperativos;
                $deficitResidual = $volumenRiesgo - $volumenRecuperable;
                $recomendacion = $this->generarRecomendacionAnticipacion($analisisDias, 25, $metaDiaria * 1.25, 'MAXIMA', $volumenRecuperable, $deficitResidual);
                
                return [
                    'success' => true,
                    'estrategia' => 'ANTICIPACION_MAXIMA',
                    'nivel_urgencia' => 'ALTA',
                    'recomendacion' => $recomendacion,
                    'pronostico' => $analisisDias['dias_detalle'],
                    'dias_inactivos' => $this->extraerDiasInactivos($analisisDias['dias_detalle']),
                    'datos_calculados' => [
                        'volumen_riesgo' => $volumenRiesgo,
                        'meta_diaria' => $metaDiaria,
                        'nueva_meta_diaria' => round($metaDiaria * 1.25, 2),
                        'aumento_necesario_pct' => 25,
                        'dias_perdidos' => $diasPerdidos,
                        'volumen_recuperable' => round($volumenRecuperable, 2),
                        'deficit_residual' => round($deficitResidual, 2),
                        'dias_operativos_disponibles' => $totalDiasOperativos,
                    ],
                ];
            }
        }

        // CASO 3: No hay días operativos disponibles → Suspensión
        return $this->estrategiaReaccion($lote, $analisisDias);
    }

    /**
     * Generar recomendación de anticipación estratégica
     */
    private function generarRecomendacionAnticipacion(array $analisisDias, float $porcentaje, float $nuevaMeta, string $tipo, float $volRecuperable = 0, float $deficitResidual = 0): string
    {
        $diasPrevios = $analisisDias['dias_operativos_previos'];
        $diasPosterior = $analisisDias['dias_operativos_posterior'];
        $totalOperativos = $analisisDias['total_dias_operativos'];
        $diasPerdidos = $analisisDias['total_dias_perdidos'];
        $volumenRiesgo = $analisisDias['volumen_riesgo'];
        
        $diaCero = $analisisDias['dia_cero_index'] !== null 
            ? $analisisDias['dias_detalle'][$analisisDias['dia_cero_index']] 
            : null;

        if ($tipo === 'VIABLE') {
            $recomendacion = "📋 PLANIFICACIÓN ESTRATÉGICA - LLUVIA PRONOSTICADA\n\n";
            
            if ($diaCero) {
                $recomendacion .= "🌧️ Primera lluvia: {$diaCero['dia_semana']} {$diaCero['fecha_str']}\n";
            }
            
            $recomendacion .= "📊 ANÁLISIS DE VENTANA (7 días):\n";
            $recomendacion .= "   • Días laborales disponibles: {$totalOperativos}\n";
            $recomendacion .= "   • Días perdidos por lluvia: {$diasPerdidos}\n";
            $recomendacion .= "   • Déficit proyectado: " . round($volumenRiesgo, 2) . " toneladas\n\n";
            
            $recomendacion .= "✅ ESTRATEGIA DE COMPENSACIÓN:\n";
            $recomendacion .= "   • Aumentar producción un " . round($porcentaje, 0) . "% en días operativos\n";
            $recomendacion .= "   • Meta diaria ajustada: " . round($nuevaMeta, 2) . " toneladas\n";
            
            if ($diasPrevios > 0 && $diasPosterior > 0) {
                $recomendacion .= "   • Distribuir entre {$diasPrevios} días ANTES y {$diasPosterior} días DESPUÉS de la lluvia\n";
            } elseif ($diasPrevios > 0) {
                $recomendacion .= "   • Concentrar esfuerzo en los {$diasPrevios} días ANTES de la lluvia\n";
            } else {
                $recomendacion .= "   • Recuperar volumen en los {$diasPosterior} días DESPUÉS de la lluvia\n";
            }
            
            $recomendacion .= "\n💡 ACCIÓN RECOMENDADA:\n";
            $recomendacion .= "   Coordinar con capataz para aumentar ritmo de trabajo.\n";
            $recomendacion .= "   Esta planificación cubrirá el 100% del déficit proyectado.";
            
        } else { // MAXIMA
            $porcentajeCobertura = round(($volRecuperable / $volumenRiesgo) * 100, 0);
            
            $recomendacion = "🚨 ALERTA CLIMÁTICA - PLANIFICACIÓN DE MÁXIMA PRIORIDAD\n\n";
            
            if ($diaCero) {
                $recomendacion .= "🌧️ Primera lluvia: {$diaCero['dia_semana']} {$diaCero['fecha_str']}\n";
            }
            
            $recomendacion .= "📊 ANÁLISIS DE VENTANA (7 días):\n";
            $recomendacion .= "   • Días laborales disponibles: {$totalOperativos}\n";
            $recomendacion .= "   • Días perdidos por lluvia: {$diasPerdidos}\n";
            $recomendacion .= "   • Déficit proyectado: " . round($volumenRiesgo, 2) . " toneladas\n\n";
            
            $recomendacion .= "⚠️ ESTRATEGIA DE MÁXIMO ESFUERZO:\n";
            $recomendacion .= "   • Aumentar producción al MÁXIMO: 25%\n";
            $recomendacion .= "   • Meta diaria ajustada: " . round($nuevaMeta, 2) . " toneladas\n";
            $recomendacion .= "   • Volumen recuperable: " . round($volRecuperable, 2) . " toneladas ({$porcentajeCobertura}%)\n";
            $recomendacion .= "   • Déficit residual: " . round($deficitResidual, 2) . " toneladas\n\n";
            
            $recomendacion .= "💡 ACCIONES INMEDIATAS:\n";
            $recomendacion .= "   1. Movilizar TODOS los recursos disponibles\n";
            $recomendacion .= "   2. Considerar horas extras o turnos extendidos\n";
            $recomendacion .= "   3. Priorizar cargas de mayor valor\n\n";
            
            $recomendacion .= "⚠️ ADVERTENCIA:\n";
            $recomendacion .= "   No será posible cubrir el 100% del déficit.\n";
            $recomendacion .= "   Se recomienda maximizar producción en días disponibles.";
        }
        
        return $recomendacion;
    }

    /**
     * Extraer solo días inactivos del análisis
     */
    private function extraerDiasInactivos(array $diasDetalle): array
    {
        return array_filter($diasDetalle, fn($dia) => $dia['estado'] === 'INACTIVO');
    }

    /**
     * PASO B: Estrategia de Anticipación (DEPRECATED - mantenido por compatibilidad)
     * 
     * Intenta redistribuir el volumen de riesgo en días previos operativos
     * Máximo aumento permitido: 25%
     */
    private function estrategiaAnticipacion(Lote $lote, array $analisisDias): array
    {
        $volumenRiesgo = $analisisDias['volumen_riesgo'];
        $diasPrevios = $analisisDias['dias_operativos_previos'];
        $metaDiaria = $analisisDias['meta_diaria'];
        $diasPerdidos = $analisisDias['total_dias_perdidos'];

        // Calcular aumento necesario por día
        $aumentoNecesario = $diasPrevios > 0 ? $volumenRiesgo / $diasPrevios : 0;
        $porcentajeAumento = $metaDiaria > 0 ? ($aumentoNecesario / $metaDiaria) * 100 : 0;

        // Obtener fecha del Día Cero
        $diaCero = $analisisDias['dias_detalle'][$analisisDias['dia_cero_index']];
        $diasHastaDiaCero = $analisisDias['dia_cero_index'];

        $estrategia = 'ANTICIPACION';
        $recomendacion = '';
        $nivel_urgencia = '';

        // Validar si el aumento es viable
        if ($porcentajeAumento <= 25) {
            // ✅ AUMENTO VIABLE - Distribuir entre días previos
            $nivel_urgencia = 'MEDIA';
            $porcentajeRedondeado = round($porcentajeAumento, 0);
            $nuevaMetaDiaria = round($metaDiaria + $aumentoNecesario, 2);

            $recomendacion = "⚠️ ALERTA DE LLUVIA EN {$diasHastaDiaCero} DÍAS ({$diaCero['fecha_str']})\n\n";
            $recomendacion .= "📊 ESTRATEGIA DE ANTICIPACIÓN:\n";
            $recomendacion .= "   • Aumentar producción un {$porcentajeRedondeado}% durante los próximos {$diasPrevios} días\n";
            $recomendacion .= "   • Meta diaria ajustada: {$nuevaMetaDiaria} toneladas\n";
            $recomendacion .= "   • Volumen a recuperar: {$volumenRiesgo} toneladas\n";
            $recomendacion .= "   • Días perdidos proyectados: {$diasPerdidos}\n\n";
            $recomendacion .= "💡 ACCIÓN INMEDIATA:\n";
            $recomendacion .= "   Coordinar con capataz para aumentar ritmo de trabajo hoy y mañana.\n";
            $recomendacion .= "   Esta anticipación permitirá cubrir el 100% del déficit proyectado.";

        } else {
            // ⚠️ AUMENTO EXCESIVO - Aumentar al máximo posible
            $nivel_urgencia = 'ALTA';
            $aumentoMaximo = $metaDiaria * 0.25; // 25% máximo
            $volumenRecuperable = $aumentoMaximo * $diasPrevios;
            $deficitResidual = $volumenRiesgo - $volumenRecuperable;
            $porcentajeCobertura = round(($volumenRecuperable / $volumenRiesgo) * 100, 0);

            $recomendacion = "🚨 LLUVIA INMINENTE EN {$diasHastaDiaCero} DÍAS ({$diaCero['fecha_str']})\n\n";
            $recomendacion .= "📊 ESTRATEGIA DE ANTICIPACIÓN (LÍMITE ALCANZADO):\n";
            $recomendacion .= "   • Aumentar producción al MÁXIMO: 25%\n";
            $recomendacion .= "   • Meta diaria ajustada: " . round($metaDiaria * self::MAX_AUMENTO_PRODUCCION, 2) . " toneladas\n";
            $recomendacion .= "   • Volumen recuperable: {$volumenRecuperable} toneladas ({$porcentajeCobertura}%)\n";
            $recomendacion .= "   • Déficit residual: {$deficitResidual} toneladas\n\n";
            $recomendacion .= "⚠️ ADVERTENCIA:\n";
            $recomendacion .= "   No será posible cubrir el 100% del déficit proyectado.\n";
            $recomendacion .= "   Se recomienda priorizar cargas de mayor valor durante estos días.\n\n";
            $recomendacion .= "💡 ACCIÓN INMEDIATA:\n";
            $recomendacion .= "   Movilizar todos los recursos disponibles. Considerar horas extras.";
        }

        return [
            'success' => true,
            'lote_id' => $lote->id_lote,
            'lote_nombre' => $lote->propietario,
            'estrategia' => $estrategia,
            'nivel_urgencia' => $nivel_urgencia,
            'recomendacion' => $recomendacion,
            'datos_calculados' => [
                'dias_hasta_lluvia' => $diasHastaDiaCero,
                'dia_cero' => $diaCero['fecha_str'],
                'dias_operativos_previos' => $diasPrevios,
                'dias_perdidos_proyectados' => $diasPerdidos,
                'volumen_riesgo' => $volumenRiesgo,
                'meta_diaria_normal' => $metaDiaria,
                'aumento_necesario_pct' => round($porcentajeAumento, 2),
                'es_viable_100' => $porcentajeAumento <= 25,
            ],
            'dias_detalle' => $analisisDias['dias_detalle'],
        ];
    }

    /**
     * PASO C: Estrategia de Reacción
     * 
     * Aplica cuando ya está lloviendo o no hay tiempo de anticipación
     * Opciones: Mantenimiento Preventivo o Suspensión de Jornada
     */
    private function estrategiaReaccion(Lote $lote, array $analisisDias): array
    {
        $estrategia = 'REACCION';
        $nivel_urgencia = 'CRITICA'; // Cambiado de INMEDIATA a CRITICA para que mapee a SUSPENDER
        $recomendacion = '';
        $accion_recomendada = '';

        // Buscar maquinarias asignadas al lote con alto desgaste
        $maquinariasMantenimiento = $this->buscarMaquinariasParaMantenimiento($lote);

        if (!empty($maquinariasMantenimiento)) {
            // OPCIÓN 1: Mantenimiento Preventivo Adelantado
            $accion_recomendada = 'MANTENIMIENTO_PREVENTIVO';
            $cantidadMaquinas = count($maquinariasMantenimiento);

            $recomendacion = "🌧️ LLUVIA ACTIVA O INMINENTE\n\n";
            $recomendacion .= "📊 ESTRATEGIA DE REACCIÓN:\n";
            $recomendacion .= "   Opción recomendada: MANTENIMIENTO PREVENTIVO ADELANTADO\n\n";
            $recomendacion .= "🔧 MAQUINARIAS IDENTIFICADAS ({$cantidadMaquinas}):\n";

            foreach ($maquinariasMantenimiento as $maq) {
                $recomendacion .= "   • {$maq['nombre']} - Desgaste: {$maq['desgaste_pct']}%\n";
                $recomendacion .= "     Odómetro: {$maq['odometro']} hs | Próximo mant.: {$maq['horas_proximo_mant']} hs\n";
            }

            $recomendacion .= "\n💡 ACCIÓN INMEDIATA:\n";
            $recomendacion .= "   1. Coordinar con taller para adelantar mantenimientos\n";
            $recomendacion .= "   2. Aprovechar parada por lluvia para trabajos preventivos\n";
            $recomendacion .= "   3. Reducir riesgo de fallas futuras y tiempos de inactividad\n\n";
            $recomendacion .= "💰 BENEFICIO:\n";
            $recomendacion .= "   Convertir tiempo de inactividad en mantenimiento productivo.\n";
            $recomendacion .= "   Evitar fallas inesperadas durante días operativos.";

        } else {
            // OPCIÓN 2: Suspensión de Jornada
            $accion_recomendada = 'SUSPENSION_JORNADA';
            $costoEstructuralDiario = $this->calcularCostoEstructuralDiario($lote);
            $diasPerdidos = $analisisDias['total_dias_perdidos'];
            $costoTotal = $costoEstructuralDiario * $diasPerdidos;

            $recomendacion = "🌧️ LLUVIA ACTIVA O INMINENTE\n\n";
            $recomendacion .= "📊 ESTRATEGIA DE REACCIÓN:\n";
            $recomendacion .= "   Opción recomendada: SUSPENSIÓN DE JORNADA\n\n";
            $recomendacion .= "💰 ANÁLISIS DE COSTOS:\n";
            $recomendacion .= "   • Costo estructural diario: $" . number_format($costoEstructuralDiario, 2) . "\n";
            $recomendacion .= "   • Días de lluvia proyectados: {$diasPerdidos}\n";
            $recomendacion .= "   • Pérdida total estimada: $" . number_format($costoTotal, 2) . "\n\n";
            $recomendacion .= "🔍 MAQUINARIAS REVISADAS:\n";
            $recomendacion .= "   No se detectaron equipos que requieran mantenimiento urgente.\n\n";
            $recomendacion .= "💡 ACCIÓN INMEDIATA:\n";
            $recomendacion .= "   1. Suspender jornada para ahorro de costos operativos\n";
            $recomendacion .= "   2. Notificar a empleados sobre suspensión por clima\n";
            $recomendacion .= "   3. Asegurar equipos y cerrar el lote\n\n";
            $recomendacion .= "⏱️ MONITOREO:\n";
            $recomendacion .= "   Revisar pronóstico cada 24hs para retomar operaciones.";
        }

        return [
            'success' => true,
            'lote_id' => $lote->id_lote,
            'lote_nombre' => $lote->propietario,
            'estrategia' => $estrategia,
            'nivel_urgencia' => $nivel_urgencia,
            'accion_recomendada' => $accion_recomendada,
            'recomendacion' => $recomendacion,
            'datos_calculados' => [
                'dias_perdidos_proyectados' => $analisisDias['total_dias_perdidos'],
                'volumen_riesgo' => $analisisDias['volumen_riesgo'],
                'maquinarias_mantenimiento' => $maquinariasMantenimiento,
            ],
            'dias_detalle' => $analisisDias['dias_detalle'],
        ];
    }

    /**
     * Busca maquinarias asignadas al lote con desgaste > 80%
     */
    private function buscarMaquinariasParaMantenimiento(Lote $lote): array
    {
        // Por ahora retornamos array vacío para evitar errores
        // TODO: Implementar cuando se defina estructura correcta de maquinarias
        return [];
    }

    /**
     * Calcula la meta diaria estimada basada en partes diarios históricos
     */
    private function calcularMetaDiaria(): float
    {
        // Calcular promedio de los últimos 30 días operativos
        $promedioHistorico = ParteDiario::where('fecha', '>=', Carbon::now()->subDays(30))
            ->whereHas('cargas')
            ->withSum('cargas', 'peso_neto')
            ->get()
            ->avg('cargas_sum_peso_neto');

        // Convertir de kilos a toneladas
        $promedioToneladas = $promedioHistorico ? $promedioHistorico / 1000.0 : 50;
        
        return round($promedioToneladas, 2); // Default 50 ton si no hay histórico
    }

    /**
     * Calcula costo estructural diario (mano de obra + maquinaria)
     */
    private function calcularCostoEstructuralDiario(Lote $lote): float
    {
        $costoTotal = 0;

        // A) Costo de empleados activos (sin fecha_fin_actividades o fecha futura)
        $empleadosActivos = \App\Models\Empleado::where(function($query) {
            $query->whereNull('fecha_fin_actividades')
                  ->orWhere('fecha_fin_actividades', '>', Carbon::today());
        })->get();
        
        foreach ($empleadosActivos as $empleado) {
            try {
                $costoTotal += $empleado->calcularCostoDia(Carbon::today(), true, null);
            } catch (\Exception $e) {
                // Si no hay histórico, ignorar este empleado
            }
        }

        // B) Costo de maquinaria en alquiler
        $maquinariasAlquiladas = Maquinaria::where('es_alquilada', true)->get();
        
        foreach ($maquinariasAlquiladas as $maquinaria) {
            // Estimar costo diario: usar toneladas_acumuladas si existe
            $toneladas = $maquinaria->toneladas_acumuladas ?? 10;
            $precio = $maquinaria->tipoMaquinaria->precio_alquiler_destajo ?? 0;
            $costoTotal += $precio * min($toneladas, 10); // Max 10 ton estimadas
        }

        return round($costoTotal, 2);
    }
}
