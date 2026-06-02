<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AllocationProposal;
use App\Models\Lote;
use App\Services\AutomaticAllocationService;
use Illuminate\Support\Facades\DB;

try {
    echo "=== REGENERANDO PROPUESTA Y CONFIRMANDO ===\n\n";
    
    // Delete old proposals for lote 13
    AllocationProposal::where('id_lote', 13)->delete();
    echo "✓ Propuestas anteriores eliminadas\n\n";
    
    // Generate new proposal
    $service = app(AutomaticAllocationService::class);
    $lote = Lote::find(13);
    
    echo "Generando nueva propuesta para lote 13...\n";
    $proposal = $service->proposeForLotAndTask($lote, \App\Enums\TaskType::TALA_RASA);
    
    echo "✓ Propuesta creada: #{$proposal->id_allocation_proposal}\n";
    echo "  - Empleados: " . $proposal->proposedEmployees->count() . "\n";
    echo "  - Maquinarias: " . $proposal->proposedMaquinarias->count() . "\n\n";
    
    // Confirm recommendation
    echo "Confirmando propuesta...\n";
    
    DB::transaction(function () use ($proposal) {
        $proposal->refresh();
        
        if ($proposal->status === 'applied') {
            echo "⚠️  Ya estaba aplicada\n";
            return;
        }
        
        $empleadosIds = $proposal->proposedEmployees
            ->where('selected', true)
            ->pluck('id_empleado')
            ->toArray();
        
        $maquinariasIds = $proposal->proposedMaquinarias
            ->where('selected', true)
            ->pluck('id_maquinaria')
            ->toArray();
        
        $proposal->lote->empleados()->sync($empleadosIds);
        $proposal->lote->maquinarias()->sync($maquinariasIds);
        
        $proposal->status = 'applied';
        if (!$proposal->confirmed_at) {
            $proposal->confirmed_at = now();
        }
        $proposal->applied_at = now();
        $proposal->save();
        
        if ($proposal->lote->estado !== 'en_proceso') {
            $proposal->lote->update(['estado' => 'en_proceso']);
        }
    });
    
    echo "✓ Propuesta confirmada y lote actualizado\n\n";
    
    // Send purchase order
    echo "Enviando orden de compra...\n";
    
    // Reload proposal
    $proposal->refresh();
    $proposal->load([
        'lote',
        'loteTarea',
        'proposedEmployees.empleado.rolLaboral',
        'proposedMaquinarias.maquinaria.tipoMaquinaria',
        'proposedInsumos.insumo.unidadMedida',
    ]);
    
    $meta = $proposal->meta ?? [];
    if (!empty($meta['purchase_order']['sent_at'] ?? null)) {
        echo "⚠️  Orden de compra ya fue enviada anteriormente\n";
    } else {
        $service->ensureWeek1SupplyEstimates($proposal);
        $proposal->refresh();
        $proposal->load([
            'proposedEmployees.empleado.rolLaboral',
            'proposedMaquinarias.maquinaria.tipoMaquinaria',
            'proposedInsumos.insumo.unidadMedida',
        ]);
        
        $emails = [];
        
        // Add configured emails
        foreach ((array) config('mail.purchase_order_emails', []) as $e) {
            $e = trim((string) $e);
            if ($e !== '') {
                $emails[] = $e;
            }
        }
        
        // Add employee emails
        foreach ($proposal->proposedEmployees->where('selected', true) as $row) {
            $email = trim((string) ($row->empleado->email ?? ''));
            if ($email !== '') {
                $emails[] = $email;
            }
        }
        
        $emails = array_unique($emails);
        
        if (!empty($emails)) {
            foreach ($emails as $email) {
                \Illuminate\Support\Facades\Notification::route('mail', $email)
                    ->notify(new \App\Notifications\OrdenCompraPropuestaNotification($proposal));
            }
            
            $meta['purchase_order'] = [
                'sent_at' => now()->toISOString(),
                'recipients' => $emails,
            ];
            $proposal->meta = $meta;
            $proposal->save();
            
            echo "✓ Orden de compra enviada a: " . json_encode($emails) . "\n";
        } else {
            echo "❌ No hay emails para enviar la orden de compra\n";
        }
    }
    
    echo "\n=== PROPUESTA COMPLETADA EXITOSAMENTE ===\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
