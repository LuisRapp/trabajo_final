<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== LIMPIEZA Y PREPARACIÓN PRUEBA UMBRAL ===\n\n";

// 1. Eliminar mantenimientos de prueba y sus notificaciones
echo "1. Eliminando mantenimientos de prueba...\n";
$mantsPrueba = \App\Models\Mantenimiento::whereIn('id_mantenimiento', [16, 17, 18, 19])->get();
foreach ($mantsPrueba as $m) {
    echo "   - Eliminando mantenimiento {$m->id_mantenimiento}\n";
    // Eliminar notificaciones asociadas
    \App\Models\NotificacionSistema::where('mantenimiento_id', $m->id_mantenimiento)->delete();
    // Eliminar insumos de mantenimiento
    \Illuminate\Support\Facades\DB::table('mantenimiento_insumos')->where('id_mantenimiento', $m->id_mantenimiento)->delete();
    $m->delete();
}

// 2. Resetear máquina 2
echo "\n2. Reseteando máquina 2...\n";
$maq = \App\Models\Maquinaria::find(2);
if ($maq) {
    $maq->update([
        'toneladas_acumuladas' => 0,
        'umbral_toneladas' => 10
    ]);
    echo "   ✓ Toneladas: 0, Umbral: 10\n";
}

// 3. Verificar configuración de usuarios
echo "\n3. Verificando configuración notificaciones...\n";
foreach ([1, 2] as $userId) {
    $exists = \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')
        ->where('user_id', $userId)
        ->where('tipo_notificacion', 'umbral')
        ->exists();
    
    if (!$exists) {
        \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')->insert([
            'user_id' => $userId,
            'tipo_notificacion' => 'umbral',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "   ✓ Configuración creada para usuario {$userId}\n";
    } else {
        echo "   ✓ Usuario {$userId} ya tiene configuración\n";
    }
}

// 4. Forzar toneladas acumuladas
echo "\n4. Forzando toneladas acumuladas a 12...\n";
$maq->update(['toneladas_acumuladas' => 12]);
echo "   ✓ Toneladas actualizadas: {$maq->fresh()->toneladas_acumuladas}\n";

echo "\n=== PREPARACIÓN COMPLETA ===\n";
echo "\nAhora ejecuta: php artisan mantenimiento:check-umbrales\n";
echo "Se esperará 60 segundos entre el envío de emails.\n";
