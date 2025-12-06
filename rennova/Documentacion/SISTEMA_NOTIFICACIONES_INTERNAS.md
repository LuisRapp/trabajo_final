# Sistema de Notificaciones Internas - Mantenimiento Preventivo

## Descripción General

El sistema de notificaciones internas permite que los usuarios reciban alertas dentro de la aplicación cuando una maquinaria alcanza su umbral de mantenimiento. Los usuarios tienen **7 días** desde la creación de la notificación para programar la fecha del mantenimiento.

## Componentes del Sistema

### 1. Base de Datos

**Tabla: `notificaciones_sistema`**
- `id`: ID único de la notificación
- `user_id`: Usuario que recibe la notificación
- `mantenimiento_id`: Orden de mantenimiento relacionada
- `tipo`: Tipo de notificación (umbral_alcanzado, stock_insuficiente, recordatorio_programado, mantenimiento_vencido)
- `titulo`: Título de la notificación
- `mensaje`: Descripción detallada
- `fecha_limite`: Fecha límite para tomar acción (7 días desde creación)
- `leida`: Si el usuario ya vio la notificación
- `accionada`: Si el usuario ya programó el mantenimiento
- `leida_at`: Timestamp de lectura
- `accionada_at`: Timestamp de acción
- `created_at`: Fecha de creación

### 2. Modelo Eloquent

**`App\Models\NotificacionSistema`**

**Relaciones:**
- `user()`: Usuario que recibe la notificación
- `mantenimiento()`: Orden de mantenimiento relacionada

**Scopes:**
- `noLeidas()`: Notificaciones no leídas
- `pendientes()`: Notificaciones no accionadas
- `vigentes()`: Notificaciones que no han superado su fecha límite
- `vencidas()`: Notificaciones que superaron la fecha límite sin ser accionadas

**Métodos helper:**
- `marcarComoLeida()`: Marca la notificación como leída
- `marcarComoAccionada()`: Marca la notificación como accionada (y leída automáticamente)
- `estaVencida()`: Verifica si la notificación está vencida
- `diasRestantes()`: Calcula cuántos días quedan para la fecha límite

### 3. Comando Automático

**`App\Console\Commands\CheckMantenimientoUmbrales`**

Este comando verifica periódicamente si alguna maquinaria ha alcanzado su umbral de toneladas. Cuando esto ocurre:

1. Crea una orden de mantenimiento preventivo
2. Envía email a usuarios configurados
3. **Crea notificación interna** con fecha límite = hoy + 7 días
4. Verifica si hay stock suficiente de insumos

**Ejecución:** Se ejecuta según la expresión cron configurada en `/configuracion-mantenimiento`

### 4. Componentes Livewire

#### `NotificacionesCampana` (Campana en Navbar)

**Ubicación:** Navbar superior derecho

**Funcionalidades:**
- Muestra contador de notificaciones no leídas (badge rojo)
- Dropdown con últimas 5 notificaciones
- Click en notificación → marca como leída y redirige a mantenimientos
- Botón "Marcar todas como leídas"
- Actualización automática con `wire:poll` o `$listeners`

#### `NotificacionesSistema` (Página completa)

**Ruta:** `/notificaciones`

**Funcionalidades:**
- Listado completo de todas las notificaciones del usuario
- Filtros por tipo y estado
- Estadísticas (total, no leídas, pendientes, vencidas)
- Paginación
- Acciones: Ver mantenimiento, marcar como leída, marcar como accionada
- Badges visuales indicando días restantes o vencida

### 5. Flujo de Trabajo

```
1. Maquinaria alcanza umbral de toneladas
   ↓
2. CheckMantenimientoUmbrales crea orden de mantenimiento
   ↓
3. Se crea notificación interna con fecha_limite = hoy + 7 días
   ↓
4. Usuario ve notificación en campana (badge con contador)
   ↓
5. Usuario hace click → marca como leída → redirige a mantenimientos
   ↓
6. Usuario programa fecha_programada para el mantenimiento
   ↓
7. Al confirmar o guardar, se marca notificación como accionada
   ↓
8. Si pasan 7 días sin accionar, la notificación se marca como vencida
```

## Configuración

### 1. Usuarios que reciben notificaciones

**Ruta:** `/configuracion-notificaciones-mantenimiento`
**Permiso:** `configurar-notificaciones-mantenimiento`

Permite asignar usuarios a tres tipos de notificaciones:
- **Umbral Alcanzado**: Cuando maquinaria alcanza toneladas de mantenimiento
- **Recordatorio Programado**: Recordatorio del día del mantenimiento programado
- **Stock Insuficiente**: Cuando no hay insumos suficientes

### 2. Horario de verificación

**Ruta:** `/configuracion-mantenimiento`
**Permiso:** `configurar-mantenimiento`

Permite configurar:
- Hora de recordatorios diarios
- Frecuencia de verificación de umbrales (expresión cron)
- Ejecutar manualmente los comandos

## Uso del Sistema

### Para Usuarios

1. **Ver notificaciones nuevas:**
   - El ícono de campana en la navbar mostrará un badge rojo con el número de notificaciones no leídas
   - Click en la campana para ver las últimas 5 notificaciones

2. **Programar mantenimiento:**
   - Click en una notificación de "Umbral Alcanzado"
   - Serás redirigido a la página de mantenimientos
   - Busca la orden correspondiente (estado "programado")
   - Asigna `fecha_programada` dentro de los próximos 7 días
   - Guarda → la notificación se marca automáticamente como "accionada"

3. **Confirmar mantenimiento:**
   - Cuando la fecha llegue, click en botón "Confirmar"
   - El estado cambia a "en curso"
   - La notificación se marca como accionada

4. **Ver historial completo:**
   - Accede a `/notificaciones` para ver todas tus notificaciones
   - Filtra por tipo o estado
   - Ve estadísticas de notificaciones pendientes/vencidas

### Para Administradores

1. **Configurar destinatarios:**
   - Ir a `/configuracion-notificaciones-mantenimiento`
   - Seleccionar usuarios que deben recibir cada tipo de notificación
   - Guardar configuración

2. **Configurar horarios:**
   - Ir a `/configuracion-mantenimiento`
   - Establecer hora de recordatorios (formato HH:MM)
   - Configurar frecuencia de verificación de umbrales (expresión cron)
   - Opcionalmente ejecutar verificaciones manuales

3. **Monitoring:**
   - Revisar logs en `storage/logs/laravel.log`
   - Ver estadísticas de notificaciones vencidas
   - Verificar que los comandos automáticos se ejecutan correctamente

## Validaciones y Reglas de Negocio

1. **Plazo de 7 días para programar:**
   - Cuando se crea la notificación, se establece `fecha_limite = fecha_notificacion + 7 días`
   - El usuario puede ver la notificación y debe responder dentro de esos 7 días
   - Si el usuario no programa el mantenimiento en 7 días, la notificación aparece como "vencida"

2. **Validación de fecha_programada:**
   - La fecha que el usuario selecciona para realizar el mantenimiento **debe estar dentro de los 7 días desde la fecha de la notificación**
   - Ejemplo: Si la notificación se creó el 20/11, el usuario puede programar el mantenimiento entre 20/11 y 27/11
   - Si el usuario intenta seleccionar 28/11, el sistema mostrará error: "La fecha programada debe estar entre 20/11 y 27/11"
   - Fórmula de validación:
     ```
     fecha_programada >= fecha_notificacion AND fecha_programada <= (fecha_notificacion + 7 días)
     ```

3. **Marcado automático:**
   - Al programar `fecha_programada` válida → notificación marcada como accionada
   - Al confirmar mantenimiento → notificación marcada como accionada
   - Al marcar como accionada → también se marca como leída automáticamente

4. **Notificaciones duplicadas:**
   - Si ya existe una orden de mantenimiento en estado "programado" o "en curso" para esa maquinaria, no se crea nueva notificación

5. **Estados:**
   - **No leída + Pendiente:** Usuario no ha visto ni actuado (badge rojo, fondo gris claro)
   - **Leída + Pendiente:** Usuario vio pero no actuó (sin badge, fondo blanco)
   - **Accionada:** Usuario programó el mantenimiento con fecha válida (badge verde "Accionada")
   - **Vencida:** Pasaron 7 días sin accionar (badge rojo "Vencida")

## Ejemplos de Código

### Crear notificación manualmente

```php
use App\Models\NotificacionSistema;

NotificacionSistema::create([
    'user_id' => 1,
    'mantenimiento_id' => 123,
    'tipo' => 'umbral_alcanzado',
    'titulo' => 'Mantenimiento Preventivo Requerido',
    'mensaje' => 'La maquinaria X ha alcanzado 500 toneladas...',
    'fecha_limite' => now()->addDays(7),
]);
```

### Consultar notificaciones de usuario

```php
use App\Models\NotificacionSistema;

// Notificaciones no leídas
$noLeidas = NotificacionSistema::where('user_id', auth()->id())
    ->noLeidas()
    ->vigentes()
    ->get();

// Notificaciones vencidas
$vencidas = NotificacionSistema::where('user_id', auth()->id())
    ->vencidas()
    ->get();

// Notificaciones pendientes (no accionadas)
$pendientes = NotificacionSistema::where('user_id', auth()->id())
    ->pendientes()
    ->get();
```

### Marcar notificación como accionada

```php
$notificacion = NotificacionSistema::find($id);
$notificacion->marcarComoAccionada();
// Automáticamente establece: accionada=true, accionada_at=now(), leida=true, leida_at=now()
```

## Rutas del Sistema

- `/notificaciones` - Página principal de notificaciones (todos los usuarios autenticados)
- `/configuracion-notificaciones-mantenimiento` - Configurar destinatarios (permiso requerido)
- `/configuracion-mantenimiento` - Configurar horarios (permiso requerido)

## Permisos Necesarios

- `configurar-notificaciones-mantenimiento` - Para configurar destinatarios
- `configurar-mantenimiento` - Para configurar horarios y ejecutar comandos manualmente
- `confirmar-mantenimiento` - Para confirmar órdenes programadas
- `reprogramar-mantenimiento` - Para reprogramar órdenes vencidas

## Logs y Debugging

El sistema registra eventos importantes en `storage/logs/laravel.log`:

```
[timestamp] Orden creada para Maquinaria XXXX (ID orden: YYYY)
[timestamp] Notificación interna creada para N usuario(s) (límite: YYYY-MM-DD)
[timestamp] Notificación #ZZZ marcada como accionada para mantenimiento #YYYY
```

## Mantenimiento y Troubleshooting

### Problema: No se crean notificaciones

**Verificar:**
1. Que `CheckMantenimientoUmbrales` se está ejecutando (revisar logs)
2. Que hay usuarios configurados en `/configuracion-notificaciones-mantenimiento`
3. Que la maquinaria tiene `umbral_toneladas` configurado
4. Que no existe ya una orden en estado "programado" o "en curso"

### Problema: Notificaciones no aparecen en campana

**Verificar:**
1. Que el usuario actual tiene notificaciones asignadas
2. Que las notificaciones están en estado `vigente` (no vencidas)
3. Refrescar la página o verificar Alpine.js x-data

### Problema: No se marca como accionada al programar

**Verificar:**
1. Que el método `marcarNotificacionComoAccionada()` se está llamando en `Mantenimientos.php`
2. Que existe una notificación con `mantenimiento_id` correspondiente
3. Revisar logs para errores silenciosos

## Futuras Mejoras

- [ ] Notificaciones push en navegador (Service Workers)
- [ ] Notificaciones por email cuando quedan 2 días
- [ ] Dashboard de métricas de notificaciones
- [ ] Notificaciones para otros módulos (stock bajo, ventas pendientes, etc.)
- [ ] WebSockets para actualizaciones en tiempo real sin polling
