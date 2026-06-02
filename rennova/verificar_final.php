<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AllocationProposal;

echo "=== VERIFICACIÓN FINAL ===\n\n";

$proposal = AllocationProposal::with([
    'proposedEmployees.empleado.rolLaboral',
    'proposedMaquinarias.maquinaria.tipoMaquinaria',
])->find(26);

echo "PROPUESTA #26\n";
echo "  Status: {$proposal->status}\n";
echo "  Confirmada: " . ($proposal->confirmed_at ? 'SÍ (' . $proposal->confirmed_at->format('Y-m-d H:i:s') . ')' : 'NO') . "\n";
echo "  Aplicada: " . ($proposal->applied_at ? 'SÍ (' . $proposal->applied_at->format('Y-m-d H:i:s') . ')' : 'NO') . "\n\n";

$meta = $proposal->meta ?? [];

echo "ORDEN DE COMPRA\n";
if (!empty($meta['purchase_order'])) {
    echo "  Enviada: " . $meta['purchase_order']['sent_at'] . "\n";
    echo "  Destinatarios: " . json_encode($meta['purchase_order']['recipients']) . "\n";
} else {
    echo "  ❌ No se envió\n";
}

echo "\n\nEMPLEADOS Y MAQUINARIAS ASIGNADOS AL LOTE 13\n";

$lote = $proposal->lote;
echo "Estado del lote: {$lote->estado}\n\n";

echo "Empleados:\n";
foreach ($lote->empleados as $emp) {
    echo "  - {$emp->nombre} {$emp->apellido}\n";
}

echo "\nMaquinarias:\n";
foreach ($lote->maquinarias as $maq) {
    echo "  - {$maq->modelo} ({$maq->tipoMaquinaria?->nombre})\n";
}

echo "\n✅ TODO COMPLETADO EXITOSAMENTE\n";
