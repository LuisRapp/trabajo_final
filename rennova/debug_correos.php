<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AllocationProposal;

try {
    $proposal = AllocationProposal::with([
        'proposedEmployees.empleado.rolLaboral',
        'proposedMaquinarias.maquinaria.tipoMaquinaria',
        'proposedInsumos.insumo.unidadMedida',
    ])->find(25);
    
    echo "=== VERIFICACIÓN DE ENVÍO DE ORDEN DE COMPRA ===\n\n";
    
    echo "PROPUESTA #25 META:\n";
    echo json_encode($proposal->meta ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "CONFIGURACIÓN DE MAIL:\n";
    $purchaseEmails = config('mail.purchase_order_emails', []);
    echo "  Emails configurados (config): ";
    if (!empty($purchaseEmails)) {
        echo json_encode($purchaseEmails) . "\n";
    } else {
        echo "VACÍO o no configurado\n";
    }
    
    echo "\nEMPLEADOS SELECCIONADOS:\n";
    foreach ($proposal->proposedEmployees->where('selected', true) as $row) {
        $emp = $row->empleado;
        echo "  - {$emp->nombre} {$emp->apellido}\n";
        echo "    Email: " . ($emp->email ? $emp->email : '❌ SIN EMAIL') . "\n";
    }
    
    echo "\n\nCONFIGURACIÓN COMPLETA DE MAIL:\n";
    echo "  Driver: " . config('mail.driver') . "\n";
    echo "  From: " . config('mail.from.address') . "\n";
    echo "  Host: " . config('mail.host') . "\n";
    
    echo "\n=== ANÁLISIS ===\n";
    
    // Check if emails were resolved
    $emails = [];
    
    foreach ((array) config('mail.purchase_order_emails', []) as $e) {
        $e = trim((string) $e);
        if ($e !== '') {
            $emails[] = $e;
        }
    }
    
    foreach ($proposal->proposedEmployees->where('selected', true) as $row) {
        $email = trim((string) ($row->empleado->email ?? ''));
        if ($email === '') {
            echo "  ⚠️  El empleado {$row->empleado->nombre} NO tiene email\n";
        } else {
            $emails[] = $email;
        }
    }
    
    echo "\n✓ Emails finales para enviar: " . json_encode(array_unique($emails)) . "\n";
    
    if (empty($emails)) {
        echo "\n❌ NO HAY EMAILS PARA ENVIAR LA ORDEN DE COMPRA\n";
        echo "  Razón: No hay emails configurados en config/mail.php ni en los empleados\n";
    } else {
        if ($proposal->meta && isset($proposal->meta['purchase_order']['sent_at'])) {
            echo "\n✅ Orden de compra enviada: " . $proposal->meta['purchase_order']['sent_at'] . "\n";
            echo "   Destinatarios: " . json_encode($proposal->meta['purchase_order']['recipients']) . "\n";
        } else {
            echo "\n⚠️  Orden de compra NO fue enviada (no se registró en meta)\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
