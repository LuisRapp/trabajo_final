<?php
/**
 * Script de Simulación: Crear Orden de Mantenimiento
 * 
 * Este script:
 * 1. Crea una maquinaria (si no existe)
 * 2. Asigna un kit de mantenimiento preventivo
 * 3. Genera una orden de mantenimiento
 * 4. Envía notificación por email
 * 5. Crea notificación interna del sistema
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\KitMantenimientoPreventivo;
use App\Models\KitMantenimientoInsumo;
use App\Models\Mantenimiento;
use App\Models\TipoMantenimiento;
use App\Models\Insumo;
use App\Models\NotificacionSistema;
use App\Models\User;
use App\Notifications\MantenimientoCreado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║          SIMULACIÓN: ORDEN DE MANTENIMIENTO                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

try {
    DB::beginTransaction();

    // PASO 1: Obtener o crear tipo de máquinaria
    echo "📋 PASO 1: Configurando tipo de máquinaria...\n";
    $tipoMaquinaria = TipoMaquinaria::first();
    if (!$tipoMaquinaria) {
        $tipoMaquinaria = TipoMaquinaria::create([
            'nombre' => 'Excavadora',
            'costo_mantenimiento_estimado' => 5000,
        ]);
        echo "   ✓ Tipo creado: {$tipoMaquinaria->nombre}\n";
    } else {
        echo "   ✓ Usando tipo existente: {$tipoMaquinaria->nombre}\n";
    }

    // PASO 2: Crear una máquinaria de prueba
    echo "\n🏗️  PASO 2: Creando máquinaria...\n";
    $maquinaria = Maquinaria::create([
        'id_tipo_maquinaria' => $tipoMaquinaria->id_tipo_maquinaria,
        'modelo' => 'CAT 320 - Simulación ' . now()->format('Y-m-d H:i:s'),
        'estado' => 'operativa',
        'es_alquilada' => false,
        'fecha_inicio_actividades' => now()->subMonths(6),
        'toneladas_acumuladas' => 500, // Simulación de uso
        'umbral_toneladas' => 400, // Supera el umbral
    ]);
    echo "   ✓ Máquinaria creada: ID {$maquinaria->id_maquinaria} - {$maquinaria->modelo}\n";
    echo "   • Toneladas acumuladas: {$maquinaria->toneladas_acumuladas}\n";
    echo "   • Umbral: {$maquinaria->umbral_toneladas}\n";

    // PASO 3: Crear o usar kit de mantenimiento
    echo "\n🔧 PASO 3: Configurando kit de mantenimiento...\n";
    
    // Verificar si existe kit para este tipo
    $kit = KitMantenimientoPreventivo::where('id_tipo_maquinaria', $tipoMaquinaria->id_tipo_maquinaria)->first();
    
    if (!$kit) {
        // Obtener o crear insumos de prueba
        $insumo1 = Insumo::firstOrCreate(
            ['nombre' => 'Aceite Hidráulico'],
            ['stock_cantidad' => 100, 'unidad' => 'litros', 'costo_unitario' => 50]
        );
        
        $insumo2 = Insumo::firstOrCreate(
            ['nombre' => 'Filtro Hidráulico'],
            ['stock_cantidad' => 50, 'unidad' => 'unidad', 'costo_unitario' => 150]
        );
        
        // Crear kit
        $kit = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $tipoMaquinaria->id_tipo_maquinaria,
            'nombre' => 'Kit Preventivo Standard ' . $tipoMaquinaria->nombre,
            'descripcion' => 'Kit de mantenimiento preventivo para ' . $tipoMaquinaria->nombre,
            'costo_estimado' => 800,
        ]);
        
        // Asociar insumos
        KitMantenimientoInsumo::create([
            'id_kit' => $kit->id_kit,
            'id_insumo' => $insumo1->id_insumo,
            'cantidad_requerida' => 20,
        ]);
        
        KitMantenimientoInsumo::create([
            'id_kit' => $kit->id_kit,
            'id_insumo' => $insumo2->id_insumo,
            'cantidad_requerida' => 5,
        ]);
        
        echo "   ✓ Kit creado: {$kit->nombre}\n";
        echo "   • Insumos incluidos: 2\n";
        echo "     - Aceite Hidráulico (20L)\n";
        echo "     - Filtro Hidráulico (5 unidades)\n";
    } else {
        echo "   ✓ Usando kit existente: {$kit->nombre}\n";
    }

    // PASO 4: Obtener tipo de mantenimiento
    echo "\n📝 PASO 4: Seleccionando tipo de mantenimiento...\n";
    $tipoMantenimiento = TipoMantenimiento::where('nombre', 'Preventivo')->first();
    if (!$tipoMantenimiento) {
        $tipoMantenimiento = TipoMantenimiento::create(['nombre' => 'Preventivo']);
    }
    echo "   ✓ Tipo: {$tipoMantenimiento->nombre}\n";

    // PASO 5: Crear orden de mantenimiento
    echo "\n🔨 PASO 5: Creando orden de mantenimiento...\n";
    $mantenimiento = Mantenimiento::create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipoMantenimiento->id_tipo_mantenimiento,
        'fecha_inicio' => now(),
        'fecha_programada' => now()->addDays(3),
        'estado' => 'programado',
        'toneladas_snapshot' => $maquinaria->toneladas_acumuladas,
        'costo_total' => 0,
    ]);
    echo "   ✓ Orden creada: ID {$mantenimiento->id_mantenimiento}\n";
    echo "   • Estado: {$mantenimiento->estado}\n";
    echo "   • Fecha programada: {$mantenimiento->fecha_programada->format('d/m/Y H:i')}\n";

    // PASO 6: Enviar notificación por email
    echo "\n📧 PASO 6: Enviando notificación por email...\n";
    try {
        $admin = User::where('email', 'admin@rennova.local')->first();
        if (!$admin) {
            $admin = User::first();
        }
        
        if ($admin) {
            Notification::send($admin, new MantenimientoCreado($mantenimiento));
            echo "   ✓ Email enviado a: {$admin->email}\n";
        } else {
            echo "   ⚠ No hay usuarios para enviar notificación\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠ Error al enviar email: {$e->getMessage()}\n";
    }

    // PASO 7: Crear notificación interna del sistema
    echo "\n🔔 PASO 7: Creando notificación interna...\n";
    
    $usuarios = User::where('email', '!=', null)->limit(3)->get();
    foreach ($usuarios as $usuario) {
        NotificacionSistema::create([
            'id_usuario' => $usuario->id,
            'tipo' => 'mantenimiento',
            'titulo' => 'Nueva Orden de Mantenimiento',
            'mensaje' => "Se ha generado una nueva orden de mantenimiento para la máquina {$maquinaria->modelo}. Maquinaria ha acumulado {$maquinaria->toneladas_acumuladas} toneladas.",
            'relacionado_id' => $mantenimiento->id_mantenimiento,
            'relacionado_tipo' => 'mantenimiento',
            'leido' => false,
        ]);
        echo "   ✓ Notificación enviada a: {$usuario->email}\n";
    }

    DB::commit();

    echo "\n" . str_repeat("═", 66) . "\n";
    echo "✅ SIMULACIÓN COMPLETADA EXITOSAMENTE\n";
    echo str_repeat("═", 66) . "\n";
    echo "\nRESUMEN:\n";
    echo "  • Máquinaria: {$maquinaria->modelo}\n";
    echo "  • Orden ID: {$mantenimiento->id_mantenimiento}\n";
    echo "  • Kit asignado: {$kit->nombre}\n";
    echo "  • Emails enviados: 1\n";
    echo "  • Notificaciones internas: " . count($usuarios) . "\n";
    echo "\nPuedes revisar:\n";
    echo "  1. El email enviado en MailHog (http://localhost:8025)\n";
    echo "  2. Las notificaciones en el dashboard del sistema\n";
    echo "  3. El registro de auditoría\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR DURANTE LA SIMULACIÓN:\n";
    echo "   {$e->getMessage()}\n";
    echo "   En: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}

exit(0);
