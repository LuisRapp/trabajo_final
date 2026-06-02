<?php

/**
 * Script de prueba completo para ClimaDecisionService
 * 
 * Simula diferentes escenarios:
 * 1. Anticipación viable (aumento < 25%)
 * 2. Anticipación al límite (aumento > 25%)
 * 3. Reacción con mantenimiento
 * 4. Reacción con suspensión
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lote;
use App\Services\ClimaDecisionService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "    PRUEBA EXHAUSTIVA - CLIMA DECISION SERVICE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

try {
    $climaService = new ClimaDecisionService();

    // Buscar o crear lote de prueba
    $lote = Lote::first();

    if (!$lote) {
        echo "❌ ERROR: No hay lotes en el sistema.\n";
        echo "💡 Cree al menos un lote desde el menú de gestión.\n";
        exit(1);
    }

    echo "📍 LOTE DE PRUEBA:\n";
    echo "   ID: {$lote->id_lote}\n";
    echo "   Propietario: {$lote->propietario}\n";
    echo "   Ubicación: {$lote->ubicacion}\n";
    echo "\n";

    // ================================================================
    // ESCENARIO 1: Prueba con coordenadas de Buenos Aires
    // (Clima típicamente estable, buen caso para anticipación)
    // ================================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "    ESCENARIO 1: Buenos Aires (Anticipación potencial)\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

    $lote->update([
        'latitud' => -34.603722,
        'longitud' => -58.381592,
    ]);

    $inicio = microtime(true);
    $resultado1 = $climaService->analizarYRecomendar($lote);
    $tiempo1 = round((microtime(true) - $inicio) * 1000, 2);

    echo "⏱️  Tiempo de análisis: {$tiempo1}ms\n";
    echo "\n";

    if ($resultado1['success']) {
        echo "✅ ANÁLISIS EXITOSO\n";
        echo "   Estrategia: {$resultado1['estrategia']}\n";
        echo "   Nivel de urgencia: {$resultado1['nivel_urgencia']}\n";
        echo "\n";
        echo "📋 RECOMENDACIÓN:\n";
        echo str_repeat("─", 63) . "\n";
        echo $resultado1['recomendacion'];
        echo "\n";
        echo str_repeat("─", 63) . "\n";
        echo "\n";

        // Mostrar días analizados
        if (isset($resultado1['dias_detalle'])) {
            echo "📅 PRONÓSTICO DETALLADO (7 DÍAS):\n";
            echo "\n";
            printf("%-12s %-10s %-8s %-10s %-8s %-6s %-10s %-18s\n", "Fecha", "Día", "Lluvia", "Lluvia 6-18", "Viento", "ET0", "Nubosidad", "Estado");
            echo str_repeat("─", 110) . "\n";
            
            foreach ($resultado1['dias_detalle'] as $dia) {
                $iconoEstado = match ($dia['estado']) {
                    'OPERATIVO' => '✅',
                    'OPERATIVO_CONDICIONAL' => '🟡',
                    default => '⚠️ ',
                };
                $lluviaDiurna = $dia['lluvia_diurna_mm'] ?? 0;
                $vientoMax = $dia['viento_max'] ?? 0;
                $et0 = $dia['et0'] ?? 0;
                printf(
                    "%-12s %-10s %6.1fmm %10.1fmm %6.1f %6.1f %8d%% %s %s\n",
                    $dia['fecha_str'],
                    substr($dia['dia_semana'], 0, 9),
                    $dia['precipitacion_mm'],
                    $lluviaDiurna,
                    $vientoMax,
                    $et0,
                    $dia['nubosidad'],
                    $iconoEstado,
                    $dia['estado']
                );
                if ($dia['razon']) {
                    echo "                                            └─ {$dia['razon']}\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "❌ ERROR: {$resultado1['error']}\n";
        if (isset($resultado1['detalle'])) {
            echo "   Detalle: {$resultado1['detalle']}\n";
        }
    }

    echo "\n";
    echo "\n";

    // ================================================================
    // ESCENARIO 2: Prueba con coordenadas de Puerto Iguazú
    // (Zona muy húmeda, alta probabilidad de lluvia)
    // ================================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "    ESCENARIO 2: Puerto Iguazú (Zona húmeda)\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

    $lote->update([
        'latitud' => -25.695139,
        'longitud' => -54.436389,
    ]);

    $inicio = microtime(true);
    $resultado2 = $climaService->analizarYRecomendar($lote);
    $tiempo2 = round((microtime(true) - $inicio) * 1000, 2);

    echo "⏱️  Tiempo de análisis: {$tiempo2}ms\n";
    echo "\n";

    if ($resultado2['success']) {
        echo "✅ ANÁLISIS EXITOSO\n";
        echo "   Estrategia: {$resultado2['estrategia']}\n";
        echo "   Nivel de urgencia: {$resultado2['nivel_urgencia']}\n";
        echo "\n";
        echo "📋 RECOMENDACIÓN:\n";
        echo str_repeat("─", 63) . "\n";
        echo $resultado2['recomendacion'];
        echo "\n";
        echo str_repeat("─", 63) . "\n";
        echo "\n";

        // Mostrar datos calculados
        if (isset($resultado2['datos_calculados'])) {
            echo "📊 DATOS CALCULADOS:\n";
            $datos = $resultado2['datos_calculados'];
            
            if (isset($datos['dias_hasta_lluvia'])) {
                echo "   • Días hasta lluvia: {$datos['dias_hasta_lluvia']}\n";
                echo "   • Día Cero: {$datos['dia_cero']}\n";
                echo "   • Días operativos previos: {$datos['dias_operativos_previos']}\n";
                echo "   • Volumen en riesgo: {$datos['volumen_riesgo']} ton\n";
                echo "   • Meta diaria normal: {$datos['meta_diaria_normal']} ton\n";
                echo "   • Aumento necesario: " . round($datos['aumento_necesario_pct'], 1) . "%\n";
                echo "   • Viable 100%: " . ($datos['es_viable_100'] ? '✅ SÍ' : '⚠️  NO') . "\n";
            } elseif (isset($datos['maquinarias_mantenimiento'])) {
                echo "   • Días perdidos proyectados: {$datos['dias_perdidos_proyectados']}\n";
                echo "   • Volumen en riesgo: {$datos['volumen_riesgo']} ton\n";
                echo "   • Maquinarias para mantenimiento: " . count($datos['maquinarias_mantenimiento']) . "\n";
            }
            
            echo "\n";
        }
    } else {
        echo "❌ ERROR: {$resultado2['error']}\n";
    }

    echo "\n";
    echo "\n";

    // ================================================================
    // ESCENARIO 3: Prueba con coordenadas de Posadas
    // (Punto intermedio)
    // ================================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "    ESCENARIO 3: Posadas, Misiones\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

    $lote->update([
        'latitud' => -27.367794,
        'longitud' => -55.896108,
    ]);

    $inicio = microtime(true);
    $resultado3 = $climaService->analizarYRecomendar($lote);
    $tiempo3 = round((microtime(true) - $inicio) * 1000, 2);

    echo "⏱️  Tiempo de análisis: {$tiempo3}ms\n";
    echo "\n";

    if ($resultado3['success']) {
        echo "✅ ANÁLISIS EXITOSO\n";
        echo "   Estrategia: {$resultado3['estrategia']}\n";
        
        if (isset($resultado3['accion_recomendada'])) {
            echo "   Acción: {$resultado3['accion_recomendada']}\n";
        }
        
        echo "\n";
        echo "📋 RECOMENDACIÓN:\n";
        echo str_repeat("─", 63) . "\n";
        echo $resultado3['recomendacion'];
        echo "\n";
        echo str_repeat("─", 63) . "\n";
    } else {
        echo "❌ ERROR: {$resultado3['error']}\n";
    }

    echo "\n";
    echo "\n";

    // ================================================================
    // RESUMEN FINAL DE PRUEBAS
    // ================================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "    RESUMEN DE PRUEBAS\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

    $exitososCount = 0;
    if ($resultado1['success']) $exitososCount++;
    if ($resultado2['success']) $exitososCount++;
    if ($resultado3['success']) $exitososCount++;

    echo "✅ Escenarios exitosos: {$exitososCount}/3\n";
    echo "⏱️  Tiempo promedio de análisis: " . round(($tiempo1 + $tiempo2 + $tiempo3) / 3, 2) . "ms\n";
    echo "\n";

    echo "📊 ESTRATEGIAS DETECTADAS:\n";
    if ($resultado1['success']) {
        echo "   • Buenos Aires: {$resultado1['estrategia']}\n";
    }
    if ($resultado2['success']) {
        echo "   • Puerto Iguazú: {$resultado2['estrategia']}\n";
    }
    if ($resultado3['success']) {
        echo "   • Posadas: {$resultado3['estrategia']}\n";
    }

    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "    CONCLUSIÓN\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

    if ($exitososCount === 3) {
        echo "✅ TODOS LOS ESCENARIOS FUNCIONARON CORRECTAMENTE\n";
        echo "\n";
        echo "💡 PRÓXIMOS PASOS:\n";
        echo "   1. Ejecutar: php artisan clima:decisiones\n";
        echo "   2. Revisar recomendaciones para cada lote\n";
        echo "   3. Implementar estrategias sugeridas\n";
        echo "   4. Automatizar ejecución diaria (opcional)\n";
    } else {
        echo "⚠️  ALGUNOS ESCENARIOS FALLARON\n";
        echo "   Revise los errores arriba y verifique:\n";
        echo "   - Conexión a internet\n";
        echo "   - Validez de coordenadas GPS\n";
        echo "   - Disponibilidad de Open-Meteo API\n";
    }

    echo "\n";

} catch (\Exception $e) {
    echo "\n";
    echo "❌ ERROR CRÍTICO:\n";
    echo "   {$e->getMessage()}\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
    echo "\n";
    exit(1);
}
