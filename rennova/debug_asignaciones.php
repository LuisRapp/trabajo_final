<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lote;
use App\Models\AllocationProposal;
use Illuminate\Support\Facades\DB;

try {
    echo "=== VERIFICACIÓN DE ASIGNACIONES APLICADAS ===\n\n";
    
    $lote = Lote::find(13);
    $proposal = AllocationProposal::find(25);
    
    echo "LOTE #13\n";
    echo "  - Estado: {$lote->estado}\n";
    echo "  - Empleados asignados: " . $lote->empleados()->count() . "\n";
    echo "  - Maquinarias asignadas: " . $lote->maquinarias()->count() . "\n";
    
    if ($lote->empleados()->count() > 0) {
        echo "\n  Empleados:\n";
        foreach ($lote->empleados as $emp) {
            echo "    - {$emp->nombre} {$emp->apellido} (ID: {$emp->id_empleado})\n";
        }
    }
    
    if ($lote->maquinarias()->count() > 0) {
        echo "\n  Maquinarias:\n";
        foreach ($lote->maquinarias as $maq) {
            echo "    - {$maq->modelo} - {$maq->tipoMaquinaria?->nombre} (ID: {$maq->id_maquinaria})\n";
        }
    }
    
    echo "\n\nPROPUESTA #25\n";
    echo "  - Status: {$proposal->status}\n";
    echo "  - Confirmada: " . ($proposal->confirmed_at ? $proposal->confirmed_at : 'No') . "\n";
    echo "  - Aplicada: " . ($proposal->applied_at ? $proposal->applied_at : 'No') . "\n";
    
    // Check if order email was sent
    echo "\n\n=== VERIFICACIÓN DE CORREOS ===\n\n";
    
    // Check mailables in logs or database
    $emailLogs = DB::table('email_logs')
        ->where('lote_id', 13)
        ->orWhere('proposal_id', 25)
        ->latest()
        ->get();
    
    if ($emailLogs->count() > 0) {
        echo "Correos enviados:\n";
        foreach ($emailLogs as $log) {
            echo "  - {$log->subject} (enviado: {$log->sent_at})\n";
        }
    } else {
        echo "Sin tabla email_logs. Buscando en jobs...\n";
        
        // Check if order job was dispatched
        $jobs = DB::table('jobs')
            ->where('payload', 'like', '%ProcessAllocationProposal%25%')
            ->get();
        
        if ($jobs->count() > 0) {
            echo "Jobs pendientes encontrados\n";
        } else {
            echo "No hay jobs registrados para la propuesta\n";
        }
    }
    
    // Check failed jobs
    $failedJobs = DB::table('failed_jobs')
        ->where('payload', 'like', '%25%')
        ->latest()
        ->get();
    
    if ($failedJobs->count() > 0) {
        echo "\n⚠️  Jobs fallidos encontrados:\n";
        foreach ($failedJobs as $job) {
            echo "  - Exception: " . json_decode($job->exception, true)['message'] . "\n";
        }
    }
    
    echo "\n=== VERIFICACIÓN FINALIZADA ===\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
