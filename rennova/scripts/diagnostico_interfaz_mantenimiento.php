<?php

/**
 * Script de Diagnóstico - Interfaz de Mantenimiento Automático
 * 
 * Este script verifica:
 * 1. Que los datos existan en la base de datos
 * 2. Que los componentes Livewire estén registrados
 * 3. Que las rutas estén configuradas
 * 4. Que el usuario tenga notificaciones
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NotificacionSistema;
use App\Models\Mantenimiento;
use App\Models\User;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO: Interfaz de Mantenimiento Automático            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Verificar Base de Datos
echo "📊 1. VERIFICANDO BASE DE DATOS...\n";
echo str_repeat("─", 66) . "\n";

$totalNotificaciones = NotificacionSistema::count();
$notificacionesNoLeidas = NotificacionSistema::where('leida', false)->count();
$mantenimientosProgramados = Mantenimiento::where('estado', 'programado')->count();

echo "   Total notificaciones:        {$totalNotificaciones}\n";
echo "   Notificaciones no leídas:    {$notificacionesNoLeidas}\n";
echo "   Mantenimientos programados:  {$mantenimientosProgramados}\n";

if ($totalNotificaciones > 0) {
    echo "   ✓ Hay notificaciones en la BD\n";
} else {
    echo "   ✗ NO hay notificaciones en la BD\n";
    echo "     Ejecuta: php artisan mantenimiento:check-umbrales --simular --maquinaria=1\n";
}

// 2. Verificar usuarios con notificaciones
echo "\n👤 2. VERIFICANDO USUARIOS CON NOTIFICACIONES...\n";
echo str_repeat("─", 66) . "\n";

$usuariosConNotif = NotificacionSistema::select('user_id')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN leida = false THEN 1 ELSE 0 END) as no_leidas')
    ->groupBy('user_id')
    ->get();

if ($usuariosConNotif->count() > 0) {
    echo "   Usuarios con notificaciones:\n";
    foreach ($usuariosConNotif as $item) {
        $user = User::find($item->user_id);
        $email = $user ? $user->email : "Usuario #{$item->user_id}";
        echo "   • {$email}: {$item->total} total, {$item->no_leidas} no leídas\n";
    }
} else {
    echo "   ✗ Ningún usuario tiene notificaciones\n";
}

// 3. Verificar últimas notificaciones
echo "\n🔔 3. ÚLTIMAS 5 NOTIFICACIONES CREADAS...\n";
echo str_repeat("─", 66) . "\n";

$ultimasNotif = NotificacionSistema::with('user:id,email')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($ultimasNotif->count() > 0) {
    foreach ($ultimasNotif as $notif) {
        $email = $notif->user ? $notif->user->email : "Sin usuario";
        $leida = $notif->leida ? "✓ Leída" : "○ No leída";
        $fecha = $notif->created_at->format('d/m/Y H:i');
        echo "   [{$fecha}] {$notif->tipo} - {$email} - {$leida}\n";
        echo "     Título: {$notif->titulo}\n";
    }
} else {
    echo "   ✗ No hay notificaciones\n";
}

// 4. Verificar componentes Livewire
echo "\n⚡ 4. VERIFICANDO COMPONENTES LIVEWIRE...\n";
echo str_repeat("─", 66) . "\n";

$componentesRequeridos = [
    'NotificacionesCampana',
    'NotificacionesSistema',
    'GestionMantenimientos',
];

foreach ($componentesRequeridos as $componente) {
    $classPath = "App\\Http\\Livewire\\{$componente}";
    if (class_exists($classPath)) {
        echo "   ✓ {$componente} existe\n";
    } else {
        echo "   ✗ {$componente} NO existe\n";
    }
}

// 5. Verificar archivos de vista
echo "\n👁️  5. VERIFICANDO VISTAS...\n";
echo str_repeat("─", 66) . "\n";

$vistas = [
    'resources/views/livewire/notificaciones-campana.blade.php',
    'resources/views/livewire/notificaciones-sistema.blade.php',
    'resources/views/notificaciones/index.blade.php',
    'resources/views/mantenimientos/index.blade.php',
    'resources/views/partials/header.blade.php',
];

foreach ($vistas as $vista) {
    if (file_exists(__DIR__ . '/' . $vista)) {
        echo "   ✓ {$vista}\n";
    } else {
        echo "   ✗ {$vista} NO existe\n";
    }
}

// 6. Verificar instalación en header
echo "\n🎯 6. VERIFICANDO INTEGRACIÓN EN HEADER...\n";
echo str_repeat("─", 66) . "\n";

$headerPath = __DIR__ . '/resources/views/partials/header.blade.php';
if (file_exists($headerPath)) {
    $headerContent = file_get_contents($headerPath);
    if (strpos($headerContent, '@livewire(\'notificaciones-campana\')') !== false) {
        echo "   ✓ @livewire('notificaciones-campana') está en header.blade.php\n";
    } else {
        echo "   ✗ @livewire('notificaciones-campana') NO está en header.blade.php\n";
        echo "     Debe agregarse en: resources/views/partials/header.blade.php\n";
    }
} else {
    echo "   ✗ header.blade.php no existe\n";
}

// 7. Verificar rutas
echo "\n🛣️  7. VERIFICANDO RUTAS...\n";
echo str_repeat("─", 66) . "\n";

try {
    $routes = Illuminate\Support\Facades\Route::getRoutes();
    $rutasRequeridas = [
        'notificaciones.index',
        'mantenimientos.index',
        'programar-mantenimiento',
    ];
    
    foreach ($rutasRequeridas as $nombreRuta) {
        if ($routes->hasNamedRoute($nombreRuta)) {
            $route = $routes->getByName($nombreRuta);
            echo "   ✓ {$nombreRuta} → {$route->uri()}\n";
        } else {
            echo "   ✗ {$nombreRuta} NO existe\n";
        }
    }
} catch (Exception $e) {
    echo "   ⚠ Error verificando rutas: {$e->getMessage()}\n";
}

// 8. Resumen y recomendaciones
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN Y RECOMENDACIONES                                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($totalNotificaciones > 0 && $mantenimientosProgramados > 0) {
    echo "✓ BACKEND: Funcionando correctamente\n";
    echo "  • {$totalNotificaciones} notificaciones en base de datos\n";
    echo "  • {$mantenimientosProgramados} mantenimientos programados\n";
    echo "\n";
} else {
    echo "⚠ BACKEND: Necesita generar datos\n";
    echo "  Ejecuta: php artisan mantenimiento:check-umbrales --simular --maquinaria=1\n";
    echo "\n";
}

echo "🚀 PASOS PARA VERIFICAR EN EL NAVEGADOR:\n";
echo "\n";
echo "1. Iniciar servidor:\n";
echo "   php artisan serve\n";
echo "\n";
echo "2. Acceder a: http://localhost:8000\n";
echo "\n";
echo "3. Login con un usuario que tenga notificaciones:\n";
if ($usuariosConNotif->count() > 0) {
    $primerUser = User::find($usuariosConNotif->first()->user_id);
    if ($primerUser) {
        echo "   • Email: {$primerUser->email}\n";
        echo "   • Notificaciones no leídas: {$usuariosConNotif->first()->no_leidas}\n";
    }
}
echo "\n";
echo "4. Buscar en el navbar (arriba derecha) el ícono 🔔\n";
echo "   • Debería mostrar badge rojo con número\n";
echo "   • Click para ver dropdown con notificaciones\n";
echo "\n";
echo "5. Ir a: http://localhost:8000/notificaciones\n";
echo "   • Ver listado completo de notificaciones\n";
echo "\n";
echo "6. Ir a: http://localhost:8000/mantenimientos\n";
echo "   • Ver órdenes de mantenimiento programadas\n";
echo "\n";

echo "📚 DOCUMENTACIÓN:\n";
echo "   • Proceso automático: Documentacion/PROCESO_AUTOMATICO_MANTENIMIENTO.md\n";
echo "   • Guía de verificación: VERIFICAR_INTERFAZ_MANTENIMIENTO.md\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  Diagnóstico completado                                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
