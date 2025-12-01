<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Insumo;
use App\Models\LoteInventario;

echo "=== Verificando Filtro Aceite ===\n\n";

$insumo = Insumo::where('nombre', 'LIKE', '%Filtro%')->first();

if (!$insumo) {
    echo "❌ Insumo no encontrado\n";
    exit;
}

echo "Insumo: {$insumo->nombre} (ID: {$insumo->id_insumo})\n\n";

$lotes = LoteInventario::where('id_insumo', $insumo->id_insumo)
    ->orderBy('fecha_compra', 'asc')
    ->get();

echo "Total de lotes: {$lotes->count()}\n\n";

foreach ($lotes as $lote) {
    echo "Lote #{$lote->id_lote_inventario}:\n";
    echo "  - Fecha: {$lote->fecha_compra}\n";
    echo "  - Cantidad inicial: {$lote->cantidad_inicial}\n";
    echo "  - Cantidad disponible: {$lote->cantidad_disponible}\n";
    echo "  - Precio unitario: \${$lote->precio_unitario}\n";
    echo "  - Agotado: " . ($lote->agotado ? 'SÍ' : 'NO') . "\n\n";
}

echo "Stock total disponible: " . LoteInventario::where('id_insumo', $insumo->id_insumo)
    ->where('agotado', false)
    ->sum('cantidad_disponible') . "\n";
