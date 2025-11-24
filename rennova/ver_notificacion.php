<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Información de la última notificación ===\n\n";

$notif = \App\Models\NotificacionSistema::latest()->first();

if ($notif) {
    echo "Notificación ID: {$notif->id_notificacion}\n";
    echo "Usuario ID: {$notif->id_usuario}\n";
    
    $user = \App\Models\User::find($notif->id_usuario);
    if ($user) {
        echo "Nombre: {$user->name}\n";
        echo "Email: {$user->email}\n";
    }
    
    echo "\nTítulo: {$notif->titulo}\n";
    echo "Mensaje: {$notif->mensaje}\n";
    echo "Fecha límite: {$notif->fecha_limite}\n";
    echo "Leída: " . ($notif->leida ? 'Sí' : 'No') . "\n";
    echo "Accionada: " . ($notif->accionada ? 'Sí' : 'No') . "\n";
    
    if ($notif->mantenimiento_id) {
        echo "\nMantenimiento ID: {$notif->mantenimiento_id}\n";
    }
}

echo "\n=== Resetear máquina para nueva prueba ===\n";
echo "¿Deseas resetear las toneladas de la máquina a 0? (s/n): ";

// Para script automático, solo mostramos el comando
echo "\nPara resetear, ejecuta:\n";
echo "php artisan tinker --execute=\"\\\$m = \\App\\Models\\Maquinaria::find(2); \\\$m->toneladas_acumuladas = 0; \\\$m->save();\"\n";
