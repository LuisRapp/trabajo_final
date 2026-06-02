#  Proceso Automatico de Mantenimiento de Maquinaria

Ultima actualizacion: 8 de febrero de 2026.

##  Descripción General

El sistema Rennova cuenta con un **proceso completamente automatizado** para gestionar el mantenimiento preventivo de la maquinaria, basado en un "odómetro" de toneladas procesadas. Este proceso elimina la necesidad de seguimiento manual y garantiza que las máquinas reciban mantenimiento cuando realmente lo necesitan.

---

##  Flujo Completo del Proceso

### **Fase 1: Actualización en Tiempo Real**

#### Cuando se registra producción (Parte Diario)

```
Usuario registra carga en Parte Diario
          ↓
Se guarda en la base de datos
          ↓
Event: CargaRegistrada se dispara
          ↓
Listener: ActualizarOdometroMaquina ejecuta
          ↓
Campo 'toneladas_acumuladas' se incrementa automáticamente
```

**Archivos involucrados:**
- Evento: [`app/Events/CargaRegistrada.php`](../app/Events/CargaRegistrada.php)
- Listener: [`app/Listeners/ActualizarOdometroMaquina.php`](../app/Listeners/ActualizarOdometroMaquina.php)

**Código del Evento:**
```php
// Se dispara cuando se registra una carga
event(new CargaRegistrada($carga, $maquinariaId, $toneladas));
```

**Código del Listener:**
```php
public function handle(CargaRegistrada $event): void
{
    $maquinaria = Maquinaria::findOrFail($event->maquinariaId);
    
    // Incrementar el odómetro
    $maquinaria->increment('toneladas_acumuladas', $event->toneladas);
    
    Log::info("Odómetro actualizado", [
        'maquinaria_id' => $event->maquinariaId,
        'toneladas_agregadas' => $event->toneladas,
        'toneladas_totales' => $maquinaria->fresh()->toneladas_acumuladas
    ]);
}
```

---

### **Fase 2: Verificación Automática Nocturna**

#### Todos los días a las 2:00 AM

El comando `CheckMantenimientoUmbrales` se ejecuta automáticamente:

```
Comando ejecuta a las 2:00 AM
          ↓
Recorre todas las maquinarias operativas
          ↓
Para cada maquinaria:
  1. Calcula: toneladas_acumuladas - último_snapshot
  2. ¿Supera el umbral configurado?
          ↓ SÍ
  3. Crea orden de mantenimiento (estado: "programado")
  4. Verifica stock del kit preventivo (por maquinaria, fallback por tipo)
  5. Envia notificaciones:
     - Email a usuarios configurados
     - Notificación interna (con fecha límite de 7 días)
     - Si falta stock: Email adicional de advertencia
```

**Archivo del comando:**
- [`app/Console/Commands/CheckMantenimientoUmbrales.php`](../app/Console/Commands/CheckMantenimientoUmbrales.php)

**Lógica principal:**
```php
// Obtener snapshot del último mantenimiento
$ultimoMantenimiento = Mantenimiento::where('id_maquinaria', $maquinaria->id_maquinaria)
    ->whereNotNull('toneladas_snapshot')
    ->orderBy('fecha_fin', 'desc')
    ->first();

// Calcular toneladas desde el último mantenimiento
$toneladasDesdeUltimo = $ultimoMantenimiento 
    ? ($maquinaria->toneladas_acumuladas - $ultimoMantenimiento->toneladas_snapshot)
    : $maquinaria->toneladas_acumuladas;

// Verificar si supera el umbral
if ($toneladasDesdeUltimo >= $maquinaria->umbral_toneladas) {
    // Crear orden de mantenimiento automáticamente
    $mantenimiento = Mantenimiento::create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipoPreventivo->id_tipo_mantenimiento,
        'fecha_inicio' => now()->toDateString(),
        'estado' => 'programado'
    ]);
    
    // Enviar notificaciones...
}
```

---

## ️ Configuración del Sistema

### **1. Configuración de la Programación**

El comando está programado en [`routes/console.php`](../routes/console.php):

```php
Schedule::command('mantenimiento:check-umbrales')
    ->dailyAt('02:00')
    ->withoutOverlapping(10)
    ->onFailure(function () {
        \Log::error('Tarea de mantenimiento fallida: mantenimiento:check-umbrales');
    })
    ->onSuccess(function () {
        \Log::info('Tarea de mantenimiento completada: mantenimiento:check-umbrales');
    });
```

### **2. Activación en Producción**

Para que el scheduler funcione, debes configurar un **cron job**:

#### **Linux/Mac:**
```bash
# Editar crontab
crontab -e

# Agregar esta línea
* * * * * cd /ruta/a/rennova && php artisan schedule:run >> /dev/null 2>&1
```

#### **Windows (Task Scheduler):**
1. Abrir **Programador de tareas**
2. Crear tarea básica
3. Acción: `php.exe`
4. Argumentos: `C:\ruta\rennova\artisan schedule:run`
5. Frecuencia: **Cada minuto**

#### **Docker:**
Ya está configurado en [`docker/laravel-cron`](../docker/laravel-cron)

---

##  Datos y Configuración

### **Campos de Base de Datos**

| Tabla | Campo | Descripción |
|-------|-------|-------------|
| `maquinarias` | `toneladas_acumuladas` | Odómetro que nunca se resetea. Se incrementa con cada carga |
| `maquinarias` | `umbral_toneladas` | Umbral por maquinaria para generar mantenimiento preventivo automatico |
| `mantenimientos` | `toneladas_snapshot` | Snapshot del odómetro cuando se completa el mantenimiento |

### **Kit Preventivo**

Tabla `kit_mantenimiento_preventivo`:
- Define insumos por maquinaria (si no existe, se usa el kit por tipo)
- Incluye cantidad requerida y si es obligatorio
- El comando verifica stock disponible al crear la orden

### **Tipos de Notificaciones**

El sistema genera **dos tipos de notificaciones**:

1. **Notificación por Email** (vía Mailtrap)
   - `MantenimientoCreado`: Cuando se genera una orden automáticamente
   - `StockInsuficiente`: Si falta stock para el kit preventivo

2. **Notificación Interna** (en el sistema)
   - Tipo: `umbral_alcanzado`
   - Incluye fecha límite (7 días desde la creación)
   - Los usuarios pueden ver y gestionar desde el panel

---

##  Comandos de Prueba

### **Ejecución Manual del Comando**

```bash
# Ejecutar verificación inmediatamente (sin esperar a las 2:00 AM)
php artisan mantenimiento:check-umbrales

# Ver resumen de ejecución
php artisan mantenimiento:check-umbrales -v
```

### **Simulación para Pruebas/Demos**

```bash
# Simular que una maquinaria superó el umbral y crear orden inmediatamente
php artisan mantenimiento:check-umbrales --maquinaria=1 --simular

# Esto fuerza:
# 1. Aumenta las toneladas_acumuladas sobre el umbral
# 2. Crea la orden de mantenimiento
# 3. Envía todas las notificaciones
```

### **Verificar Tareas Programadas**

```bash
# Ver lista de tareas programadas y próximas ejecuciones
php artisan schedule:list

# Ejecutar el scheduler manualmente (ejecuta todas las tareas que correspondan)
php artisan schedule:run

# Modo desarrollo: ejecuta el scheduler continuamente
php artisan schedule:work
```

### **Monitoreo de Logs**

```bash
# Ver logs en tiempo real (Windows PowerShell)
Get-Content .\storage\logs\laravel.log -Tail 50 -Wait

# Ver logs en tiempo real (Linux/Mac)
tail -f storage/logs/laravel.log

# Buscar logs específicos de mantenimiento
grep "check-umbrales" storage/logs/laravel.log
```

---

##  Sistema de Notificaciones

### **Configuración de Destinatarios**

Los usuarios que reciben notificaciones se configuran en la tabla:
```
configuracion_notificaciones_mantenimiento
```

Campos:
- `user_id`: Usuario que recibirá las notificaciones
- `tipo_notificacion`: `'umbral'` o `'stock'`

### **Notificación de Umbral Alcanzado**

Se crea en la tabla `notificaciones_sistema` con:
- **Tipo**: `umbral_alcanzado`
- **Título**: "Mantenimiento Preventivo Requerido - {maquinaria}"
- **Mensaje**: Detalla toneladas acumuladas y número de orden
- **Fecha límite**: 7 días desde la creación
- **Enlace**: Vinculado al mantenimiento para acceso directo

### **Notificación de Stock Insuficiente**

Si al verificar el kit preventivo falta stock:
- Se envía email a usuarios configurados para notificaciones de `'stock'`
- Incluye detalle de insumos faltantes
- Permite tomar acción antes de ejecutar el mantenimiento

---

##  Ejemplo de Flujo Completo

### **Escenario Real:**

1. **Día 1 - Producción Normal:**
   - Se registran cargas: Excavadora procesa 15 toneladas
   - `toneladas_acumuladas`: 0 → 15
   - Umbral configurado: 100 toneladas

2. **Día 15 - Continúa operando:**
   - Más cargas registradas
   - `toneladas_acumuladas`: 15 → 105 toneladas
   - Umbral: 100 toneladas

3. **Día 16 - 2:00 AM (Verificación Automática):**
   ```
    Comando ejecuta
    Detecta: 105 >= 100
    Crea orden #1234 (estado: programado)
    Verifica kit preventivo
    Envía email a supervisor@empresa.com
    Crea notificación interna (límite: 23/01/2026)
   ```

4. **Día 16 - 8:00 AM (Usuario revisa):**
   - Ve notificación en el sistema
   - Accede directamente a la orden #1234
   - Programa fecha de ejecución
   - Verifica disponibilidad de insumos

5. **Día 18 - Ejecuta Mantenimiento:**
   - Completa la orden
   - Sistema guarda `toneladas_snapshot`: 105
   - Próximo mantenimiento: cuando llegue a 205 toneladas

---

##  Ventajas del Sistema Automático

 **Sin intervención manual**: El sistema detecta automáticamente cuándo se necesita mantenimiento

 **Basado en uso real**: No depende de calendario, sino de toneladas procesadas

 **Anticipación de problemas**: Verifica stock antes de ejecutar

 **Trazabilidad completa**: Auditoría de todas las acciones

 **Notificaciones multinivel**: Email + notificación interna

 **Configurable por maquinaria (o por tipo como fallback)**: Cada maquinaria puede tener su propio umbral

---

##  Troubleshooting

### **Las órdenes no se crean automáticamente**

**Verificar:**
1. ¿Está configurado el cron job?
   ```bash
   crontab -l  # Linux/Mac
   # Debe aparecer: * * * * * cd /ruta && php artisan schedule:run
   ```

2. ¿Hay maquinarias que superan el umbral?
   ```sql
   SELECT id_maquinaria, toneladas_acumuladas, umbral_toneladas 
   FROM maquinarias 
   WHERE toneladas_acumuladas >= umbral_toneladas
   AND estado = 'operativa';
   ```

3. ¿El scheduler está ejecutándose?
   ```bash
   # Ver logs
   grep "schedule:run" storage/logs/laravel.log
   ```

### **No se envían notificaciones por email**

**Verificar configuración en `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS=noreply@rennova.com
```

**Probar envío:**
```bash
php artisan tinker
> Mail::raw('Test', function($msg) { $msg->to('test@test.com')->subject('Test'); });
```

### **El odómetro no se actualiza**

**Verificar que el evento se dispare en el controlador:**
```php
// En ParteDiarioController o donde se registren cargas
use App\Events\CargaRegistrada;

event(new CargaRegistrada($carga, $maquinariaId, $toneladas));
```

**Verificar logs:**
```bash
grep "Odómetro actualizado" storage/logs/laravel.log
```

---

##  Documentación Relacionada

- [Manual de Mantenimientos](MANUAL_MANTENIMIENTOS.md)
- [Guía de Uso: Mantenimientos](GUIA_MANTENIMIENTOS_USO.md)
- [Tareas Programadas (Scheduler)](TAREAS_PROGRAMADAS_SCHEDULER.md)
- [Sistema de Notificaciones Internas](SISTEMA_NOTIFICACIONES_INTERNAS.md)
- [Instrucciones de Notificaciones Email](INSTRUCCIONES_NOTIFICACIONES_EMAIL.md)

---

##  Contacto y Soporte

Para más información sobre el sistema de mantenimiento automático, consultar:
- Documentación técnica: `Documentacion/SISTEMA_MANTENIMIENTO_DOCS.md`
- Código fuente del comando: `app/Console/Commands/CheckMantenimientoUmbrales.php`
- Configuración del scheduler: `routes/console.php`

---

**Última actualización:** 29 de enero de 2026
