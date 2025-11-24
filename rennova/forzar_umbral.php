<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Forzando umbral de mantenimiento ===\n\n";

// Verificar si hay lotes disponibles
$lote = \App\Models\Lote::first();

if (!$lote) {
    echo "⚠️ No hay lotes en el sistema. Creando uno...\n";
    
    // Necesitamos un cliente
    $cliente = \App\Models\Cliente::first();
    if (!$cliente) {
        echo "❌ No hay clientes en el sistema. Por favor crea un cliente primero.\n";
        exit(1);
    }
    
    $lote = \App\Models\Lote::create([
        'id_cliente' => $cliente->id_cliente,
        'numero_lote' => 'TEST-' . now()->format('YmdHis'),
        'fecha_inicio' => now(),
        'toneladas_objetivo' => 100,
        'estado' => 'activo'
    ]);
    
    echo "✅ Lote creado: {$lote->numero_lote}\n\n";
}

// Seleccionar la máquina ID 2
$maquinaria = \App\Models\Maquinaria::find(2);

if (!$maquinaria) {
    echo "❌ No se encontró la máquina\n";
    exit(1);
}

echo "Máquina: {$maquinaria->modelo}\n";
echo "Toneladas actuales: {$maquinaria->toneladas_acumuladas}\n";
echo "Umbral: {$maquinaria->umbral_toneladas}\n\n";

// Calcular cuánto falta para el umbral
$toneladas_necesarias = $maquinaria->umbral_toneladas - $maquinaria->toneladas_acumuladas + 10;

if ($toneladas_necesarias <= 0) {
    echo "⚠️ La máquina ya superó el umbral\n";
    $toneladas_necesarias = 10; // Agregar 10 más para asegurar
}

echo "Registrando carga de {$toneladas_necesarias} toneladas...\n";

// Crear una carga que supere el umbral
$carga = \App\Models\Carga::create([
    'id_lote' => $lote->id_lote,
    'id_maquinaria' => $maquinaria->id_maquinaria,
    'id_categoria_madera' => 1,
    'toneladas' => $toneladas_necesarias,
    'fecha_carga' => now(),
    'numero_remito' => 'TEST-UMBRAL-' . now()->format('YmdHis'),
    'observaciones' => 'Carga de prueba para disparar umbral de mantenimiento',
    'estado' => 'pendiente'
]);

echo "✅ Carga creada con ID: {$carga->id_carga}\n";
echo "   Toneladas: {$carga->toneladas}\n";
echo "🔔 El evento CargaRegistrada debería dispararse ahora\n";
echo "📧 Verifica tu email (Mailtrap) y las notificaciones en el sistema\n\n";

// Esperar y verificar
sleep(2);

// Recargar la máquina para ver las toneladas actualizadas
$maquinaria->refresh();
echo "📊 Toneladas acumuladas actualizadas: {$maquinaria->toneladas_acumuladas}\n";
echo "   Umbral: {$maquinaria->umbral_toneladas}\n";

if ($maquinaria->toneladas_acumuladas >= $maquinaria->umbral_toneladas) {
    echo "   ✅ ¡Umbral alcanzado!\n\n";
} else {
    echo "   ⚠️ Todavía no se alcanzó el umbral\n\n";
}

// Verificar si se creó el mantenimiento y la notificación
$mantenimiento = \App\Models\Mantenimiento::where('id_maquinaria', $maquinaria->id_maquinaria)
    ->latest()
    ->first();

if ($mantenimiento) {
    echo "✅ Mantenimiento creado:\n";
    echo "   ID: {$mantenimiento->id_mantenimiento}\n";
    echo "   Estado: {$mantenimiento->estado}\n";
    echo "   Fecha programada: {$mantenimiento->fecha_programada}\n\n";
    
    // Buscar notificación asociada
    $notificacion = \App\Models\NotificacionSistema::where('mantenimiento_id', $mantenimiento->id_mantenimiento)
        ->first();
    
    if ($notificacion) {
        echo "✅ Notificación creada:\n";
        echo "   Título: {$notificacion->titulo}\n";
        echo "   Mensaje: {$notificacion->mensaje}\n";
        echo "   Usuario: {$notificacion->id_usuario}\n";
        echo "   Fecha límite: {$notificacion->fecha_limite}\n";
        echo "   Leída: " . ($notificacion->leida ? 'Sí' : 'No') . "\n\n";
    } else {
        echo "⚠️ No se encontró notificación asociada\n\n";
    }
} else {
    echo "⚠️ No se creó mantenimiento automáticamente\n";
    echo "   Verifica que el listener esté funcionando correctamente\n\n";
}

echo "✅ Proceso completado!\n";
echo "\n🔍 Próximos pasos:\n";
echo "   1. Inicia sesión con el usuario ID correspondiente\n";
echo "   2. Verifica la campanita de notificaciones\n";
echo "   3. Haz clic en la notificación para programar el mantenimiento\n";
echo "   4. Revisa tu Mailtrap para el email\n";
