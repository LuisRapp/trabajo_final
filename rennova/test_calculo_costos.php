<?php

/**
 * Script de prueba para validar el cálculo de costos de un Parte Diario
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ParteDiario;
use App\Models\Empleado;
use App\Models\Carga;
use Carbon\Carbon;

echo "\n=== TEST DE CÁLCULO DE COSTOS - PARTE DIARIO ===\n\n";

// Buscar un parte diario existente con cargas
$parte = ParteDiario::with(['cargas.empleados', 'cargas.maquinarias', 'empleados'])
    ->whereHas('cargas')
    ->first();

if (!$parte) {
    echo "❌ No se encontró ningún parte diario con cargas para probar.\n";
    echo "Tip: Crea un parte diario con cargas desde la UI primero.\n";
    exit(1);
}

echo "✅ Parte Diario encontrado:\n";
echo "   ID: {$parte->id_parte_diario}\n";
echo "   Fecha: {$parte->fecha}\n";
echo "   Es día caído: " . ($parte->es_dia_caido ? 'SÍ' : 'NO') . "\n";
echo "   Cargas asociadas: " . $parte->cargas->count() . "\n";
echo "   Empleados participantes: " . $parte->empleados->count() . "\n\n";

// Mostrar estado ANTES del cálculo
echo "--- ESTADO ANTES DEL CÁLCULO ---\n";
echo "   Costo Mano de Obra: " . ($parte->costo_mano_obra ?? 'NULL') . "\n";
echo "   Costo Insumos: " . ($parte->costo_insumos ?? 'NULL') . "\n";
echo "   Costo Maquinaria: " . ($parte->costo_maquinaria ?? 'NULL') . "\n";
echo "   Costo Total Día: " . ($parte->costo_total_dia ?? 'NULL') . "\n";
echo "   Costo Unitario: " . ($parte->costo_unitario_calculado ?? 'NULL') . "\n\n";

// Ejecutar cálculo
echo "🔄 Ejecutando calcularYGuardarCostos()...\n\n";
try {
    $start = microtime(true);
    $parte->calcularYGuardarCostos();
    $tiempo = (microtime(true) - $start) * 1000;
    
    // Recargar desde BD
    $parte->refresh();
    
    echo "✅ Cálculo completado en " . number_format($tiempo, 2) . " ms\n\n";
    
    echo "--- RESULTADO DEL CÁLCULO ---\n";
    echo "   Costo Mano de Obra: $" . number_format($parte->costo_mano_obra ?? 0, 2) . "\n";
    echo "   Costo Insumos: $" . number_format($parte->costo_insumos ?? 0, 2) . "\n";
    echo "   Costo Maquinaria: $" . number_format($parte->costo_maquinaria ?? 0, 2) . "\n";
    echo "   --------------------------------\n";
    echo "   COSTO TOTAL DÍA: $" . number_format($parte->costo_total_dia ?? 0, 2) . "\n";
    
    if ($parte->costo_unitario_calculado) {
        echo "   Costo Unitario ($/ton): $" . number_format($parte->costo_unitario_calculado, 2) . "\n";
    } else {
        echo "   Costo Unitario: N/A (día caído o sin toneladas)\n";
    }
    
    echo "\n";
    
    // Validaciones
    $total_esperado = ($parte->costo_mano_obra ?? 0) + ($parte->costo_insumos ?? 0) + ($parte->costo_maquinaria ?? 0);
    $total_calculado = $parte->costo_total_dia ?? 0;
    
    if (abs($total_esperado - $total_calculado) < 0.01) {
        echo "✅ VALIDACIÓN: La suma de componentes coincide con el total\n";
    } else {
        echo "❌ VALIDACIÓN FALLIDA: Diferencia de $" . number_format(abs($total_esperado - $total_calculado), 2) . "\n";
    }
    
    // Desglose por empleado (BONUS: mostrar trait en acción)
    if ($parte->empleados->count() > 0) {
        echo "\n--- DESGLOSE POR EMPLEADO ---\n";
        foreach ($parte->empleados as $emp) {
            $cargasDelEmpleado = Carga::whereDate('fecha_carga', $parte->fecha)
                ->whereHas('empleados', function($q) use ($emp) {
                    $q->where('empleados.id_empleado', $emp->id_empleado);
                })
                ->with('empleados')
                ->get();
            
            $costoEmpleado = $emp->calcularCostoDia(
                $parte->fecha,
                $parte->es_dia_caido,
                $cargasDelEmpleado
            );
            
            echo "   • {$emp->apellido}, {$emp->nombre}: $" . number_format($costoEmpleado, 2) . "\n";
        }
    }
    
    echo "\n✅ Test completado exitosamente\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
