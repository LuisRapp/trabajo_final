<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Obtener el usuario con ID 2
$usuario = App\Models\User::find(2);

if (!$usuario) {
    echo "No se encontró el usuario con ID 2.\n";
    exit(1);
}

echo "Creando notificación para: {$usuario->name} (ID: {$usuario->id})\n\n";

// Primero buscar mantenimientos existentes programados sin fecha
$mantenimiento = App\Models\Mantenimiento::where('estado', 'programado')
    ->whereNull('fecha_programada')
    ->with(['maquinaria', 'tipoMantenimiento'])
    ->first();

if ($mantenimiento) {
    echo "✓ Usando mantenimiento existente ID: {$mantenimiento->id_mantenimiento}\n";
} else {
    echo "No hay mantenimientos programados sin fecha. Verificando si hay datos para crear uno...\n\n";
    
    // Verificar maquinarias
    $maquinas = App\Models\Maquinaria::count();
    echo "Maquinarias disponibles: {$maquinas}\n";
    
    // Verificar tipos de mantenimiento
    $tipos = App\Models\TipoMantenimiento::count();
    echo "Tipos de mantenimiento disponibles: {$tipos}\n\n";
    
    if ($maquinas == 0 || $tipos == 0) {
        echo "❌ ERROR: No hay datos suficientes para crear un mantenimiento.\n";
        echo "Por favor:\n";
        echo "  1. Crea al menos una maquinaria desde el sistema\n";
        echo "  2. Crea al menos un tipo de mantenimiento\n";
        echo "  3. Vuelve a ejecutar este script\n";
        exit(1);
    }
    
    $maquinaria = App\Models\Maquinaria::first();
    $tipoMantenimiento = App\Models\TipoMantenimiento::first();
    
    echo "Creando mantenimiento de prueba...\n";
    $mantenimiento = App\Models\Mantenimiento::create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipoMantenimiento->id_tipo_mantenimiento,
        'fecha_inicio' => now()->addDays(7), // Fecha temporal, se actualizará al programar
        'estado' => 'programado',
        'toneladas_snapshot' => 0,
    ]);
    echo "✓ Mantenimiento creado: ID {$mantenimiento->id_mantenimiento}\n";
}

$tipoDesc = $mantenimiento->tipoMantenimiento ? $mantenimiento->tipoMantenimiento->nombre : 'Mantenimiento';
echo "Maquinaria: {$mantenimiento->maquinaria->nombre}\n";
echo "Tipo: {$tipoDesc}\n\n";

// Crear notificación asociada al mantenimiento
$notificacion = App\Models\NotificacionSistema::create([
    'user_id' => $usuario->id,
    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
    'tipo' => 'umbral_alcanzado',
    'titulo' => '⚠️ Mantenimiento Requerido - ' . $mantenimiento->maquinaria->nombre,
    'mensaje' => 'El mantenimiento requiere ser programado. Por favor, programa una fecha dentro de los próximos 7 días.',
    'fecha_limite' => now()->addDays(7),
    'leida' => false,
]);

echo "✓ Notificación creada exitosamente (ID: {$notificacion->id})\n";
echo "Título: {$notificacion->titulo}\n";
echo "Mantenimiento asociado: ID {$mantenimiento->id_mantenimiento}\n";
echo "Fecha límite: {$notificacion->fecha_limite->format('d/m/Y')}\n";
echo "Días restantes: {$notificacion->diasRestantes()}\n";

// Contar notificaciones no leídas
$noLeidas = App\Models\NotificacionSistema::where('user_id', $usuario->id)
    ->where('leida', false)
    ->count();

echo "\nTotal de notificaciones no leídas: {$noLeidas}\n";
echo "\n¡Recarga la página y haz clic en la notificación para abrir el modal de programación!\n";
