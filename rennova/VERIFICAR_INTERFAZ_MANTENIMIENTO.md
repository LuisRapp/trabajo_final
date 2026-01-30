# 🔍 Verificación de Interfaz - Proceso Automático de Mantenimiento

## Estado Actual del Sistema

### ✅ Backend Funcionando

El comando se ejecutó correctamente y generó:
- **4 mantenimientos programados** en estado `programado`
- **32 notificaciones internas** no leídas
- Las órdenes están en la base de datos correctamente

### 🎯 Componentes de Interfaz Implementados

1. **Campanita de Notificaciones** (Navbar)
   - Componente: `NotificacionesCampana.php`
   - Vista: `notificaciones-campana.blade.php`
   - Ubicación: Header principal (arriba derecha)
   - ✅ Instalado en: `resources/views/partials/header.blade.php` línea 9

2. **Panel de Notificaciones** (Página completa)
   - Componente: `NotificacionesSistema.php`
   - Ruta: `/notificaciones`
   - Vista: `resources/views/notificaciones/index.blade.php`

3. **Gestión de Mantenimientos** (Listado)
   - Componente: `GestionMantenimientos.php`
   - Ruta: `/mantenimientos`
   - Vista: `resources/views/mantenimientos/index.blade.php`

---

## 📋 Pasos para Verificar la Interfaz

### **Paso 1: Iniciar el Servidor**

```powershell
cd d:\trabajo_final\rennova
php artisan serve
```

Acceder a: **http://localhost:8000**

---

### **Paso 2: Verificar la Campanita de Notificaciones**

1. **Login en el sistema** con un usuario autorizado
2. **Buscar en el navbar** (arriba derecha) el ícono de campanita 🔔
3. **Debería mostrar**:
   - Badge rojo con número de notificaciones no leídas
   - Al hacer clic, dropdown con las últimas 5 notificaciones

**¿Qué esperar?**
```
🔔 [Badge: 32]  ← Cantidad de notificaciones no leídas
```

**Si NO aparece el badge o las notificaciones:**
- Verificar que el usuario logueado tenga notificaciones asociadas
- Ver sección "Troubleshooting" abajo

---

### **Paso 3: Verificar Listado de Notificaciones**

1. Ir a: **http://localhost:8000/notificaciones**
2. Debería mostrar:
   - Todas las notificaciones del usuario
   - Filtros por tipo y estado
   - Estadísticas (total, no leídas, pendientes, vencidas)

**Tipos de notificación esperados:**
- `umbral_alcanzado`: Cuando una maquinaria superó el umbral
- `stock_insuficiente`: Cuando faltan insumos

---

### **Paso 4: Verificar Órdenes de Mantenimiento**

1. Ir a: **http://localhost:8000/mantenimientos**
2. Debería mostrar:
   - Tab "Órdenes Activas" con las órdenes en estado `programado`
   - Las 4 órdenes creadas recientemente
   - Botones de acción (Aprobar, Completar, Detalle)

**Estados esperados:**
- `programado`: Creadas automáticamente por el sistema
- `en curso`: En ejecución
- `completado`: Finalizadas

---

### **Paso 5: Probar Flujo Completo desde Notificación**

1. **Click en la campanita** 🔔
2. **Click en una notificación** de tipo "Mantenimiento Preventivo Requerido"
3. **Debería redirigir** a:
   - Modal de programación de mantenimiento, O
   - Directamente a `/mantenimientos` con la orden resaltada

---

## 🔧 Troubleshooting - Problemas Comunes

### **Problema 1: No aparece la campanita de notificaciones**

**Verificar:**
```powershell
# Verificar que el componente está registrado
cd d:\trabajo_final\rennova
php artisan livewire:list | findstr NotificacionesCampana
```

**Debería mostrar:**
```
App\Http\Livewire\NotificacionesCampana -> notificaciones-campana
```

**Solución si no aparece:**
```powershell
php artisan livewire:discover
php artisan view:clear
php artisan cache:clear
```

---

### **Problema 2: La campanita aparece pero sin badge (sin número)**

**Causa:** El usuario logueado no tiene notificaciones asignadas.

**Verificar:**
```powershell
# Ver notificaciones del usuario ID 1 (ajustar según tu usuario)
php artisan tinker
```

Dentro de tinker:
```php
use App\Models\NotificacionSistema;
$userId = 1; // Cambiar por tu ID de usuario
NotificacionSistema::where('user_id', $userId)->count();
NotificacionSistema::where('user_id', $userId)->where('leida', false)->count();
exit
```

**Solución:**
- Si devuelve `0`, el problema es que las notificaciones se crearon para otros usuarios
- Ver "Paso Extra" abajo para crear notificaciones para tu usuario

---

### **Problema 3: Error "Class NotificacionesCampana not found"**

**Solución:**
```powershell
composer dump-autoload
php artisan config:clear
php artisan view:clear
```

---

### **Problema 4: Las notificaciones no se actualizan en tiempo real**

**Causa:** Livewire no está refrescando el componente.

**Solución temporal:**
- Recargar la página (F5)
- El componente debería actualizarse

**Solución permanente (opcional):**
- Implementar polling en el componente (ver abajo)

---

### **Problema 5: Error 404 en ruta /notificaciones**

**Verificar rutas:**
```powershell
php artisan route:list | findstr notificaciones
```

**Debería mostrar:**
```
GET|HEAD  notificaciones .................. notificaciones.index
```

**Si no aparece, verificar en:**
- `routes/web.php` línea ~105-110

---

## 🎨 Mejoras Opcionales para la UI

### **Agregar Polling para Actualización Automática**

Si quieres que las notificaciones se actualicen automáticamente cada X segundos:

**Opción 1: Polling en NotificacionesCampana**

Editar `app/Http/Livewire/NotificacionesCampana.php`:

```php
// Agregar al inicio de la clase
protected $pollInterval = 30000; // 30 segundos en milisegundos

// En el método render, agregar:
public function render()
{
    $this->cargarNotificaciones();
    return view('livewire.notificaciones-campana');
}
```

Y en la vista `resources/views/livewire/notificaciones-campana.blade.php`, agregar al div principal:

```blade
<li class="nav-item dropdown" wire:poll.30s="cargarNotificaciones">
```

---

### **Agregar Sonido de Notificación**

En `resources/views/livewire/notificaciones-campana.blade.php`, agregar al final:

```blade
@push('scripts')
<script>
    let ultimaCantidad = {{ $cantidadNoLeidas }};
    
    Livewire.hook('message.processed', (message, component) => {
        if (component.fingerprint.name === 'notificaciones-campana') {
            const nuevaCantidad = component.get('cantidadNoLeidas');
            if (nuevaCantidad > ultimaCantidad) {
                // Reproducir sonido o mostrar alerta
                // new Audio('/sounds/notification.mp3').play();
            }
            ultimaCantidad = nuevaCantidad;
        }
    });
</script>
@endpush
```

---

## 📊 Paso Extra: Crear Notificación de Prueba para Tu Usuario

Si necesitas crear una notificación manualmente para tu usuario actual:

```powershell
php artisan tinker
```

```php
use App\Models\NotificacionSistema;
use App\Models\Mantenimiento;
use App\Models\User;

// Obtener el primer usuario (ajustar según necesites)
$user = User::first();

// Obtener un mantenimiento programado
$mantenimiento = Mantenimiento::where('estado', 'programado')->first();

// Crear notificación
NotificacionSistema::create([
    'user_id' => $user->id,
    'mantenimiento_id' => $mantenimiento?->id_mantenimiento,
    'tipo' => 'umbral_alcanzado',
    'titulo' => 'Mantenimiento Preventivo Requerido - Test',
    'mensaje' => 'Esta es una notificación de prueba para verificar la interfaz.',
    'fecha_limite' => now()->addDays(7),
    'leida' => false,
]);

echo "✓ Notificación creada para usuario: {$user->email}\n";
exit
```

Ahora recarga la página y deberías ver la campanita con badge.

---

## ✅ Checklist de Verificación

- [ ] Servidor Laravel ejecutándose (`php artisan serve`)
- [ ] Usuario logueado en el sistema
- [ ] Campanita visible en el navbar (arriba derecha)
- [ ] Badge con número de notificaciones no leídas
- [ ] Dropdown funciona al hacer click
- [ ] Ruta `/notificaciones` muestra listado completo
- [ ] Ruta `/mantenimientos` muestra órdenes programadas
- [ ] Click en notificación redirige correctamente
- [ ] Marcar como leída funciona
- [ ] Botones de acción en mantenimientos funcionan

---

## 🐛 Debug Avanzado

### Ver logs de Livewire en consola del navegador

1. Abrir **DevTools** (F12)
2. En **Console**, escribir:
```javascript
Livewire.hook('message.sent', (message, component) => {
    console.log('Livewire Message:', message, component);
});
```

### Ver queries de base de datos

En `.env` cambiar:
```env
APP_DEBUG=true
DB_LOG_QUERIES=true
```

Luego instalar debugbar:
```powershell
composer require barryvdh/laravel-debugbar --dev
```

---

## 📞 Soporte

Si después de seguir todos estos pasos aún tienes problemas:

1. **Revisar logs:**
   ```powershell
   Get-Content .\storage\logs\laravel.log -Tail 50
   ```

2. **Verificar consola del navegador (F12):**
   - Errores JavaScript
   - Errores de red (Network tab)
   - Errores de Livewire

3. **Limpiar cachés completo:**
   ```powershell
   php artisan optimize:clear
   php artisan view:clear
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

---

**Última actualización:** 29 de enero de 2026
