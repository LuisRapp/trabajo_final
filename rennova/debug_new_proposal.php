<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lote;
use App\Services\AutomaticAllocationService;

try {
    $service = app(AutomaticAllocationService::class);
    $lote = Lote::find(13);
    
    if (!$lote) {
        echo "Lote no encontrado\n";
        exit;
    }
    
    echo "Lote 13 info:\n";
    echo "  - Tipo: " . ($lote->tipo_trabajo ?? 'N/A') . "\n";
    echo "  - Especie: " . ($lote->especie ?? 'N/A') . "\n\n";
    
    echo "Enumeraciones disponibles en TaskType:\n";
    foreach (\App\Enums\TaskType::cases() as $case) {
        echo "  - " . $case->name . " = " . $case->value . "\n";
    }
    echo "\n";
    
    echo "Generando recomendación para lote 13...\n\n";
    
    // Use TALA_RASA as default
    $proposal = $service->proposeForLotAndTask($lote, \App\Enums\TaskType::TALA_RASA);
    
    // Reload with eager loading
    $proposal = $proposal->load('proposedEmployees.empleado', 'proposedMaquinarias.maquinaria.tipoMaquinaria');
    
    if ($proposal) {
        echo "✓ Propuesta creada exitosamente\n";
        echo "  ID: " . $proposal->id_allocation_proposal . "\n";
        echo "  Estado: " . ($proposal->status ?? 'N/A') . "\n";
        echo "  Empleados: " . $proposal->proposedEmployees->count() . "\n";
        echo "  Maquinarias: " . $proposal->proposedMaquinarias->count() . "\n";
        echo "  Tamaño equipo sugerido: " . $proposal->suggested_team_size . "\n";
        echo "  Maquinarias sugeridas: " . $proposal->suggested_machinery_count . "\n";
        
        echo "\nEmpleados propuestos:\n";
        foreach ($proposal->proposedEmployees as $prop) {
            $emp = $prop->empleado;
            echo "  - ID {$prop->id_empleado}: " . ($emp ? $emp->nombre_empleado : 'NO ENCONTRADO') . " (Score: {$prop->score}, Selected: " . ($prop->selected ? 'SI' : 'NO') . ")\n";
        }
        
        echo "\nMaquinarias propuestas:\n";
        foreach ($proposal->proposedMaquinarias as $prop) {
            $maq = $prop->maquinaria;
            if ($maq) {
                echo "  - ID {$prop->id_maquinaria}: {$maq->modelo} ({$maq->tipoMaquinaria?->nombre}) (Score: {$prop->score}, Selected: " . ($prop->selected ? 'SI' : 'NO') . ")\n";
            } else {
                echo "  - ID {$prop->id_maquinaria}: NO ENCONTRADA (Score: {$prop->score}, Selected: " . ($prop->selected ? 'SI' : 'NO') . ")\n";
            }
        }
    } else {
        echo "✗ No se pudo crear la propuesta\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
