<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AllocationProposal;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

try {
    $proposalId = 25; // La propuesta que acabamos de crear
    
    echo "Simulando confirmación de propuesta #$proposalId...\n\n";
    
    DB::transaction(function () use ($proposalId) {
        $proposal = AllocationProposal::query()
            ->with(['lote', 'proposedEmployees', 'proposedMaquinarias'])
            ->lockForUpdate()
            ->findOrFail((int) $proposalId);
        
        echo "Propuesta cargada:\n";
        echo "  - ID: {$proposal->id_allocation_proposal}\n";
        echo "  - Lote: {$proposal->lote?->id_lote}\n";
        echo "  - Empleados seleccionados: " . $proposal->proposedEmployees->where('selected', true)->count() . "\n";
        echo "  - Maquinarias seleccionadas: " . $proposal->proposedMaquinarias->where('selected', true)->count() . "\n";
        
        $empleadosIds = $proposal->proposedEmployees
            ->where('selected', true)
            ->pluck('id_empleado')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->toArray();
        
        $maquinariasIds = $proposal->proposedMaquinarias
            ->where('selected', true)
            ->pluck('id_maquinaria')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->toArray();
        
        echo "\nEmpleados a sincronizar: " . json_encode($empleadosIds) . "\n";
        echo "Maquinarias a sincronizar: " . json_encode($maquinariasIds) . "\n";
        
        $lote = $proposal->lote;
        
        // Sync employees
        echo "\nSincronizando empleados...\n";
        $lote->empleados()->sync($empleadosIds);
        echo "✓ Empleados sincronizados\n";
        
        // Sync maquinarias
        echo "Sincronizando maquinarias...\n";
        $lote->maquinarias()->sync($maquinariasIds);
        echo "✓ Maquinarias sincronizadas\n";
        
        // Update proposal
        echo "Actualizando propuesta...\n";
        $proposal->status = 'applied';
        if (!$proposal->confirmed_at) {
            $proposal->confirmed_at = now();
        }
        $proposal->applied_at = now();
        $proposal->save();
        echo "✓ Propuesta actualizada\n";
        
        // Update lote
        echo "Actualizando lote...\n";
        if ($lote->estado !== 'en_proceso') {
            $lote->update(['estado' => 'en_proceso']);
        }
        echo "✓ Lote actualizado\n";
    });
    
    echo "\n✓ Confirmación completada exitosamente\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error durante la confirmación:\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace: " . $e->getTraceAsString() . "\n";
}
