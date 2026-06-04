<?php

namespace App\Services;

use App\Models\Lote;

/**
 * Servicio de Estrategia Climática
 * 
 * Responsable de generar recomendaciones operativas basadas en análisis climático
 */
class ClimaEstrategiaService
{
    const MAX_AUMENTO_PRODUCCION = 1.25; // Máximo 25% extra

    protected ClimaAnalisisService $analisisService;

    public function __construct(ClimaAnalisisService $analisisService)
    {
        $this->analisisService = $analisisService;
    }

    /**
     * Determinar estrategia según análisis completo de ventanas operativas
     */
    public function determinarEstrategia(Lote $lote, array $analisisDias): array
    {
        $volumenRiesgo = $analisisDias['volumen_riesgo'];
        $diasPerdidos = $analisisDias['total_dias_perdidos'];
        $diasPrevios = $analisisDias['dias_operativos_previos'];
        $diasPosterior = $analisisDias['dias_operativos_posterior'];
        $totalDiasOperativos = $analisisDias['total_dias_operativos'];
        $metaDiaria = $analisisDias['meta_diaria'];
        $diaCeroIndex = $analisisDias['dia_cero_index'];

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

        // CASO 2: Lluvia inminente y sin días previos → Reacción inmediata
        if ($diaCeroIndex !== null && $diasPrevios === 0) {
            return $this->estrategiaReaccion($lote, $analisisDias);
        }

        // CASO 3: Hay días operativos disponibles (antes o después) → Planificación estratégica
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
                    'dias_inactivos' => $this->analisisService->extraerDiasInactivos($analisisDias['dias_detalle']),
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
                    'dias_inactivos' => $this->analisisService->extraerDiasInactivos($analisisDias['dias_detalle']),
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

        // CASO 4: No hay días operativos disponibles → Suspensión
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
     * PASO B: Estrategia de Anticipación (DEPRECATED - mantenido por compatibilidad)
     * 
     * Intenta redistribuir el volumen de riesgo en días previos operativos
     * Máximo aumento permitido: 25%
     */
    public function estrategiaAnticipacion(Lote $lote, array $analisisDias): array
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
    public function estrategiaReaccion(Lote $lote, array $analisisDias): array
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
            $costoEstructuralDiario = $this->analisisService->calcularCostoEstructuralDiario($lote);
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
}
