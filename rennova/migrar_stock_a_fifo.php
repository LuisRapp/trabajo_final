<?php

/**
 * Script de migración de datos para el sistema FIFO
 * 
 * Convierte el stock actual de cada insumo en lotes de inventario
 * con precio promedio calculado desde el costo_unitario actual.
 * 
 * IMPORTANTE: Ejecutar DESPUÉS de aplicar las migraciones:
 * - 2025_11_24_000001_create_lotes_inventario_table.php
 * - 2025_11_24_000002_refactor_movimiento_stocks_table.php
 * - 2025_11_24_000003_create_calcular_costo_fifo_function.php
 * 
 * USO:
 * php rennova/migrar_stock_a_fifo.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;

// Inicializar aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "  MIGRACIÓN DE STOCK A SISTEMA FIFO\n";
echo "========================================\n\n";

try {
    DB::beginTransaction();
    
    // 1. Validar que las migraciones se ejecutaron
    echo "1. Validando estructura de base de datos...\n";
    
    if (!Schema::hasTable('lotes_inventario')) {
        throw new Exception("Tabla 'lotes_inventario' no existe. Ejecutar migración primero.");
    }
    
    if (!Schema::hasColumn('movimiento_stocks', 'precio_unitario')) {
        throw new Exception("Columna 'precio_unitario' no existe en movimiento_stocks. Ejecutar migración primero.");
    }
    
    echo "   ✓ Estructura validada\n\n";
    
    // 2. Obtener todos los insumos con stock
    echo "2. Obteniendo insumos con stock...\n";
    $insumos = Insumo::whereNotNull('stock')
        ->where('stock', '>', 0)
        ->get();
    
    echo "   Encontrados: {$insumos->count()} insumos con stock > 0\n\n";
    
    // 3. Crear lotes de inventario inicial por cada insumo
    echo "3. Creando lotes de inventario inicial...\n";
    $lotesCreados = 0;
    $totalValor = 0;
    
    foreach ($insumos as $insumo) {
        // Obtener fecha del movimiento más antiguo (o hoy si no hay movimientos)
        $movimientoAntiguo = MovimientoStock::where('id_insumo', $insumo->id_insumo)
            ->orderBy('fecha', 'asc')
            ->first();
        
        $fechaCompra = $movimientoAntiguo ? $movimientoAntiguo->fecha : now()->format('Y-m-d');
        
        // Usar costo_unitario actual como precio de referencia
        $precioUnitario = $insumo->costo_unitario ?? 0;
        
        if ($precioUnitario <= 0) {
            echo "   ⚠ Insumo '{$insumo->nombre}' (ID: {$insumo->id_insumo}) sin costo_unitario. Usando $1 por defecto.\n";
            $precioUnitario = 1;
        }
        
        // Crear lote de inventario inicial
        $lote = LoteInventario::create([
            'id_insumo' => $insumo->id_insumo,
            'id_proveedor' => $insumo->id_proveedor, // Proveedor asociado al insumo
            'cantidad_inicial' => $insumo->stock,
            'cantidad_disponible' => $insumo->stock,
            'precio_unitario' => $precioUnitario,
            'costo_total' => $insumo->stock * $precioUnitario,
            'fecha_compra' => $fechaCompra,
            'numero_factura' => null,
            'tipo_movimiento' => 'ajuste_entrada',
            'observaciones' => 'Migración inicial a sistema FIFO - Stock convertido desde campo legacy',
            'agotado' => false
        ]);
        
        $lotesCreados++;
        $totalValor += $lote->costo_total;
        
        echo "   ✓ Lote #{$lote->id_lote_inventario}: {$insumo->nombre} - {$insumo->stock} unidades @ \${$precioUnitario}\n";
    }
    
    echo "\n   Total lotes creados: {$lotesCreados}\n";
    echo "   Valor total de inventario: \$" . number_format($totalValor, 2) . "\n\n";
    
    // 4. Backfill de precio_unitario en movimientos históricos
    echo "4. Actualizando movimientos históricos con precio_unitario...\n";
    
    $movimientosSinPrecio = MovimientoStock::whereNull('precio_unitario')->get();
    echo "   Encontrados: {$movimientosSinPrecio->count()} movimientos sin precio\n";
    
    $movimientosActualizados = 0;
    foreach ($movimientosSinPrecio as $movimiento) {
        $insumo = Insumo::find($movimiento->id_insumo);
        if ($insumo) {
            $precioUnitario = $insumo->costo_unitario ?? 1;
            
            $movimiento->update([
                'precio_unitario' => $precioUnitario,
                'costo_total_movimiento' => $movimiento->cantidad * $precioUnitario,
                // NO asignar id_lote_inventario porque son movimientos históricos previos a FIFO
            ]);
            
            $movimientosActualizados++;
        }
    }
    
    echo "   ✓ Movimientos actualizados: {$movimientosActualizados}\n\n";
    
    // 5. Verificación final
    echo "5. Verificando migración...\n";
    
    $stockDB = LoteInventario::disponibles()->sum('cantidad_disponible');
    $stockOriginal = Insumo::whereNotNull('stock')->sum('stock');
    
    echo "   Stock original (suma insumos.stock): " . number_format($stockOriginal, 2) . "\n";
    echo "   Stock FIFO (suma lotes.disponible): " . number_format($stockDB, 2) . "\n";
    
    $diferencia = abs($stockDB - $stockOriginal);
    if ($diferencia < 0.01) {
        echo "   ✓ Verificación exitosa - stocks coinciden\n\n";
    } else {
        echo "   ⚠ Diferencia detectada: " . number_format($diferencia, 2) . "\n\n";
    }
    
    DB::commit();
    
    echo "========================================\n";
    echo "  MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "========================================\n\n";
    
    echo "SIGUIENTES PASOS:\n";
    echo "1. Verificar lotes en GestionStock\n";
    echo "2. Registrar nuevas compras con precios reales\n";
    echo "3. Las salidas futuras usarán FIFO automáticamente\n";
    echo "4. Opcional: Eliminar columna 'stock' de tabla 'insumos' después de validar\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR EN MIGRACIÓN: " . $e->getMessage() . "\n";
    echo "STACK TRACE:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
