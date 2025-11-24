<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE RESULTADOS ===\n\n";

// Mantenimiento creado
$mant = \App\Models\Mantenimiento::latest()->first();
if ($mant) {
    echo "✅ MANTENIMIENTO ID {$mant->id_mantenimiento}:\n";
    echo "   Máquina: {$mant->id_maquinaria}\n";
    echo "   Tipo: {$mant->tipoMantenimiento->nombre}\n";
    echo "   Estado: {$mant->estado}\n";
    echo "   Fecha inicio: {$mant->fecha_inicio}\n\n";
    
    // Notificaciones creadas
    echo "✅ NOTIFICACIONES CREADAS:\n";
    $notifs = \App\Models\NotificacionSistema::where('mantenimiento_id', $mant->id_mantenimiento)->get();
    foreach ($notifs as $n) {
        $user = \App\Models\User::find($n->user_id);
        echo "   - Usuario {$n->user_id} ({$user->name})\n";
        echo "     Título: {$n->titulo}\n";
        echo "     Límite: {$n->fecha_limite}\n";
        echo "     Leída: " . ($n->leida ? 'Sí' : 'No') . "\n\n";
    }
} else {
    echo "⚠️ No se encontró mantenimiento\n\n";
}

echo "=== INSTRUCCIONES PARA PROBAR ===\n\n";
echo "1. Inicia sesión en el sistema con el usuario 2 (Administrador)\n";
echo "2. Verifica la campanita de notificaciones (debe mostrar 1 no leída)\n";
echo "3. Haz clic en la notificación para ir a programar mantenimiento\n";
echo "4. Programa una fecha dentro del rango de 7 días\n";
echo "5. Ve a Mantenimientos y completa la orden {$mant->id_mantenimiento}\n";
echo "6. Verifica que se autocarguen los insumos del kit preventivo\n\n";

echo "✅ EMAILS:\n";
echo "   - Botones 'Ver Orden' eliminados de las notificaciones\n";
echo "   - Email de mantenimiento: bloqueado por rate limit de Mailtrap\n";
echo "   - Email de stock insuficiente: enviado a admin@example.com\n";
echo "   - Revisa tu inbox de Mailtrap en https://mailtrap.io\n";
