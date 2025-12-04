# 🧪 Script de Prueba - Sistema de Notificaciones

Este script te permite probar el sistema completo de notificaciones por email.

## Ejecutar Prueba Completa

```bash
# 1. Ejecutar el script de prueba
php artisan tinker
```

Luego dentro de Tinker, ejecutar:

```php
// Configurar para usar log en lugar de email real (para pruebas)
config(['mail.default' => 'log']);

// Obtener o crear una maquinaria de prueba
$maquinaria = \App\Models\Maquinaria::first();

if (!$maquinaria) {
    // Crear maquinaria de prueba
    $tipo = \App\Models\TipoMaquinaria::first();
    $maquinaria = \App\Models\Maquinaria::create([
        'id_tipo_maquinaria' => $tipo->id_tipo_maquinaria,
        'modelo' => 'Test Maquina 001',
        'estado' => 'operativa',
        'es_alquilada' => false,
        'fecha_inicio_actividades' => now(),
        'toneladas_acumuladas' => 150.00,
        'umbral_toneladas' => 100.00  // Umbral más bajo que las acumuladas
    ]);
    echo "✓ Maquinaria de prueba creada\n";
}

// Establecer toneladas para que supere el umbral
$maquinaria->update([
    'toneladas_acumuladas' => 150.00,
    'umbral_toneladas' => 100.00
]);

echo "✓ Maquinaria configurada: {$maquinaria->modelo}\n";
echo "  - Toneladas acumuladas: {$maquinaria->toneladas_acumuladas}\n";
echo "  - Umbral: {$maquinaria->umbral_toneladas}\n";

// Verificar que existe tipo de mantenimiento preventivo
$tipoPreventivo = \App\Models\TipoMantenimiento::where('nombre', 'LIKE', '%preventivo%')->first();
if (!$tipoPreventivo) {
    $tipoPreventivo = \App\Models\TipoMantenimiento::create([
        'nombre' => 'Mantenimiento Preventivo',
        'descripcion' => 'Mantenimiento programado preventivo',
        'activo' => true
    ]);
    echo "✓ Tipo de mantenimiento preventivo creado\n";
}

// Ejecutar el comando
Artisan::call('mantenimiento:check-umbrales');
echo Artisan::output();

// Verificar si se creó la orden
$ultimaOrden = \App\Models\Mantenimiento::latest()->first();
if ($ultimaOrden && $ultimaOrden->id_maquinaria == $maquinaria->id_maquinaria) {
    echo "✓ Orden de mantenimiento creada: #{$ultimaOrden->id_mantenimiento}\n";
    echo "  - Estado: {$ultimaOrden->estado}\n";
    echo "  - Fecha: {$ultimaOrden->fecha_inicio}\n";
} else {
    echo "✗ No se creó orden (puede ser porque ya existe una pendiente)\n";
}

// Ver el email en el log
echo "\n📧 Revisar el email simulado en: storage/logs/laravel.log\n";
echo "💡 Buscar: 'Nueva Orden de Mantenimiento Generada'\n";
```

## Limpiar Prueba

Para limpiar los datos de prueba:

```php
// En Tinker:
\App\Models\Mantenimiento::where('estado', 'programado')->delete();
echo "✓ Órdenes de prueba eliminadas\n";
```

## Prueba con Email Real

Si ya configuraste SMTP (Gmail, Mailtrap, etc.):

```bash
# 1. Verificar .env
cat .env | grep MAIL_

# 2. Probar envío simple
php artisan tinker
```

```php
Mail::raw('Email de prueba del sistema', function($message) {
    $message->to('tu_email@gmail.com')
            ->subject('Test desde Rennova');
});
```

Si no hay errores, el email se envió correctamente.

## Verificar Logs

```bash
# Ver últimas líneas del log
tail -n 100 storage/logs/laravel.log

# Buscar notificaciones enviadas
grep -i "notification" storage/logs/laravel.log | tail -20

# Ver emails simulados (si MAIL_MAILER=log)
grep -A 30 "Nueva Orden de Mantenimiento" storage/logs/laravel.log
```
