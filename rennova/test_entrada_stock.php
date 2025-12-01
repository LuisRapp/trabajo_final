<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MovimientoStock;
use App\Models\LoteInventario;
use Illuminate\Support\Facades\DB;

echo "=== Probando registro de entrada de stock ===\n\n";

// Limpiar lotes anteriores del filtro aceite para prueba limpia
DB::table('movimiento_stocks')->where('id_insumo', 1)->delete();
DB::table('lotes_inventario')->where('id_insumo', 1)->delete();

echo "Registrando entrada de 10 unidades a \$15.00...\n";

try {
    $resultado = MovimientoStock::registrarEntrada(
        1, // id_insumo (Filtro Aceite)
        10, // cantidad
        15.00, // precio_unitario
        [
            'id_proveedor' => 1,
            'numero_factura' => 'TEST-001',
            'tipo_movimiento' => 'compra',
            'observaciones' => 'Prueba de entrada'
        ]
    );
    
    echo "\n✅ Entrada registrada exitosamente\n\n";
    
    $lote = $resultado['lote'];
    echo "Datos del lote:\n";
    echo "  ID: {$lote->id_lote_inventario}\n";
    echo "  Cantidad inicial: {$lote->cantidad_inicial}\n";
    echo "  Cantidad disponible: {$lote->cantidad_disponible}\n";
    echo "  Precio unitario: \${$lote->precio_unitario}\n";
    echo "  Costo total: \${$lote->costo_total}\n";
    echo "  Agotado: " . ($lote->agotado ? 'SÍ' : 'NO') . "\n";
    
    echo "\n\n=== Verificando en BD ===\n";
    $loteBD = LoteInventario::find($lote->id_lote_inventario);
    echo "Lote desde BD:\n";
    echo "  Cantidad inicial: {$loteBD->cantidad_inicial}\n";
    echo "  Cantidad disponible: {$loteBD->cantidad_disponible}\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
