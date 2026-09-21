# ⏰ TAREAS PROGRAMADAS (SCHEDULER)

Ultima actualizacion: 20 de septiembre de 2026.

**Fecha:** 5 de Diciembre de 2025  
Estado: configurado y listo.

---

##  RESUMEN

El sistema Rennova tiene **3 procesos automatizados** que se ejecutan en el servidor sin intervención manual:

1.  **Mantenimiento preventivo por umbral** (generación automática de órdenes cuando la maquinaria supera umbrales, más recordatorios de órdenes programadas)
2.  **Análisis climático operativo** (sincronización de pronóstico, clima real histórico y recomendaciones)
3.  **Propuestas de asignación automática** (jobs en cola disparados por cambios de estado del lote y por la planificación de tareas)

Los procesos 1 y 2 se disparan por tiempo (scheduler). El proceso 3 se dispara por eventos/estado y **requiere que el worker de cola esté corriendo** (ver Requisitos).

El monitoreo de los tres procesos está disponible en la pantalla **Estado de procesos** (Administración → Estado de procesos): muestra la sincronización climática por lote, los trabajos pendientes y fallidos de la cola.

---

##  TAREAS CONFIGURADAS

### 1️⃣ Verificación de Umbrales de Mantenimiento
```
Comando: mantenimiento:check-umbrales
Frecuencia: Diariamente a las 06:30 (después de clima:analizar de las 06:00)
Descripción: Verifica si la maquinaria supera sus umbrales de toneladas acumuladas y genera automáticamente órdenes de mantenimiento preventivo
```

**¿Qué hace?**
- Revisa todas las maquinarias operativas
- Compara toneladas desde el último mantenimiento vs `umbral_toneladas`
- Si se supera → Crea orden de mantenimiento automáticamente (en transacción)
- Asigna personal disponible automáticamente (por rol: mantenimiento → administrativo → sin filtro)
- Detecta faltantes de stock del kit preventivo y genera propuesta de compra
- Resuelve la fecha programada evitando la ventana de lluvia (72h) usando los datos del proceso climático
- Notifica por email (con reintentos) y en el sistema (notificación interna `umbral_alcanzado`, que es el canal de registro: se crea antes del intento de email)

**Archivos:**
- Comando: `app/Console/Commands/CheckMantenimientoUmbrales.php`
- Configuración: `routes/console.php`

---

### 2️⃣ Análisis de Decisiones Climáticas
```
Comando: clima:decisiones
Frecuencia: Cada 6 horas
Descripción: Analiza el clima actual y genera recomendaciones operativas inteligentes (Anticipación/Reacción)
```

**¿Qué hace?**
- Revisa lotes activos con coordenadas GPS
- Consulta datos climáticos en tiempo real
- Genera recomendaciones basadas en ClimaDecisionService
- Categoriza como "ANTICIPACION" o "REACCION"
- Registra el estado por lote/día en `clima_dias_lote` (fuente: api/archive/forecast; los errores se persisten como fallback)

**Archivos:**
- Comando: `app/Console/Commands/AnalizarDecisionesClimaticas.php`
- Servicio: `app/Services/ClimaDecisionService.php`
- Configuración: `routes/console.php`

---

### 3️⃣ Análisis de Riesgo Climático (7 días)
```
Comando: clima:analizar --dias=7
Frecuencia: Diariamente a las 6:00 AM
Descripción: Analiza pronóstico climático de 7 días usando Open-Meteo API
```

**¿Qué hace?**
- Consulta Open-Meteo API para pronóstico a 7 días
- Calcula costo de oportunidad por días de lluvia
- Analiza impacto en producción forestal
- Genera alertas de riesgo climático
- Registra análisis en base de datos

**Archivos:**
- Comando: `app/Console/Commands/AnalizarRiesgoClimatico.php`
- Configuración: `routes/console.php`

---

### 4️⃣ Verificación de Mantenimientos Programados
```
Comando: mantenimiento:check-programados
Frecuencia: Cada 4 horas
Descripción: Verifica mantenimientos programados para hoy, envía recordatorios y marca como vencidos los no confirmados
```

**¿Qué hace?**
- Lista las órdenes programadas para hoy y las pendientes de programar con fecha límite cercana
- Envía recordatorio por email a los usuarios configurados (con reintentos; respaldo interno en notificaciones)
- Marca como `vencido` el mantenimiento no confirmado cuya fecha programada pasó
- Al marcar vencido crea notificación interna `mantenimiento_vencido` para los usuarios configurados (canal de registro que no depende de SMTP)

**Archivos:**
- Comando: `app/Console/Commands/CheckMantenimientosProgramados.php`
- Configuración: `routes/console.php`

---

### 5️⃣ Sincronización de Clima Real (histórico)
```
Comando: clima:real
Frecuencia: Diariamente a las 00:30
Descripción: Sincroniza el clima real del día anterior por lote (API de archivo histórico, con fallback a pronóstico pasado)
```

**¿Qué hace?**
- Consulta el histórico real del día anterior por lote activo con coordenadas
- Persiste el estado real del día en `clima_dias_lote` (fuente: archive/forecast/fallback)
- Alimenta las métricas de días no operativos por lluvia de los reportes

**Archivos:**
- Comando: `app/Console/Commands/SincronizarClimaReal.php`
- Servicio: `app/Services/ClimaDecisionService.php` (`sincronizarReal`)
- Configuración: `routes/console.php`

---

##  CÓMO ACTIVAR EN SERVIDOR

### Opción 1: Linux/Mac (Recomendado)

Agregar al **crontab**:

```bash
crontab -e
```

Agregar esta línea:

```cron
* * * * * cd /ruta/a/rennova && php artisan schedule:run >> /dev/null 2>&1
```

**Explicación:**
- `* * * * *` = Cada minuto
- El scheduler de Laravel verifica qué tareas están programadas y las ejecuta si toca la hora
- Los logs se guardan en `storage/logs/laravel.log`

### Opción 2: Windows (Task Scheduler)

1. Abrir **Task Scheduler**
2. Crear tarea básica
3. Acción: `php.exe`
4. Argumentos: `C:\ruta\rennova\artisan schedule:run`
5. Frecuencia: **Cada minuto**

### Opción 3: Docker (Ya incluido)

El contenedor Docker tiene configurado el cron automáticamente:

```dockerfile
# En docker/laravel-cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Se ejecuta automáticamente cuando el contenedor está activo.

---

##  CONFIGURACIÓN ACTUAL

```php
// routes/console.php

// Tarea 1: Mantenimiento - Diariamente 06:30 (después de clima:analizar)
Schedule::command('mantenimiento:check-umbrales')
    ->dailyAt('06:30')
    ->withoutOverlapping(10)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));

// Tarea 2: Clima Decisiones - Cada 6 horas
Schedule::command('clima:decisiones')
    ->everySixHours()
    ->withoutOverlapping(5)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));

// Tarea 3: Análisis Riesgo - Diariamente 6:00 AM
Schedule::command('clima:analizar --dias=7')
    ->dailyAt('06:00')
    ->withoutOverlapping(10)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));

// Tarea 4: Chequeo Programados - Cada 4 horas
Schedule::command('mantenimiento:check-programados')
    ->everyFourHours()
    ->withoutOverlapping(5)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));

// Tarea 5: Clima Real - Diariamente 00:30
Schedule::command('clima:real')
    ->dailyAt('00:30')
    ->withoutOverlapping(10)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));
```

---

##  TESTING - EJECUTAR MANUAL

Para probar sin esperar a la hora programada:

```bash
# Ejecutar mantenimiento ahora
php artisan mantenimiento:check-umbrales

# Ejecutar solo para una maquinaria
php artisan mantenimiento:check-umbrales --maquinaria=1

# Ejecutar análisis de clima para lote específico
php artisan clima:decisiones --lote=1

# Ejecutar análisis de riesgo (7 días)
php artisan clima:analizar --dias=7

# Sincronizar clima real del día anterior
php artisan clima:real

# Ver próximas tareas programadas
php artisan schedule:list
```

---

##  LOGS Y MONITOREO

Los logs de tareas programadas se guardan en:

```
storage/logs/laravel.log
```

Cada tarea registra:
-  Inicio de ejecución
-  Órdenes creadas
-  Errores ocurridos
-  Finalización exitosa

Ejemplo de log:

```
[2025-12-05 00:30:12] local.INFO: Tarea de clima real completada: clima:real
[2025-12-05 06:00:22] local.INFO: Tarea de clima completada: clima:analizar
[2025-12-05 06:30:15] local.INFO: Tarea de mantenimiento completada: mantenimiento:check-umbrales
[2025-12-05 06:30:16] local.INFO:  Orden creada - Maquinaria ID: 5 - Toneladas: 1200/1000
```

---

## ️ REQUISITOS IMPORTANTES

### 1. **Cron debe estar ejecutándose**
   - Sin cron, las tareas NO se ejecutan automáticamente
   - Linux/Mac: Verificar con `crontab -l`
   - Docker: Ya incluido en `docker/laravel-cron`

### 2. **Base de datos accesible**
   - Las tareas necesitan conectarse a PostgreSQL
   - Verificar `.env` está bien configurado

### 3. **Queue driver configurado Y worker corriendo (OBLIGATORIO)**
   ```
   # En .env
   QUEUE_CONNECTION=database
   ```
   Las propuestas de asignación automática se ejecutan como jobs en cola
   (`GenerateAllocationProposalsForLote`, `ProcessAllocationProposal`, `SendPurchaseOrderEmail`).
   Sin un worker activo, esos jobs quedan pendientes para siempre y **el proceso no se ejecuta**:

   ```bash
   # Verificar jobs pendientes / fallidos
   php artisan queue:failed

   # Worker en producción (systemd, supervisor o nohup)
   php artisan queue:work --queue=default --daemon
   ```

   El estado de la cola (pendientes y fallidos) es visible en la pantalla
   **Estado de procesos** (Administración → Estado de procesos).

### 4. **API de clima (Open-Meteo)**
   - No requiere autenticación
   - Debe tener acceso a internet
   - Límite: 10,000 requests/día (suficiente)

---

##  ORDEN DE EJECUCIÓN

```timeline
00:30 AM  → clima:real                          (Diario)
04:00 AM  → mantenimiento:check-programados     (Cada 4h)
06:00 AM  → clima:analizar                      (Diario)
06:00 AM  → clima:decisiones                    (Cada 6h)
06:30 AM  → mantenimiento:check-umbrales        (Diario, después del clima)
08:00 AM  → mantenimiento:check-programados     (Cada 4h)
12:00 PM  → clima:decisiones                    (Cada 6h)
12:00 PM  → mantenimiento:check-programados     (Cada 4h)
04:00 PM  → mantenimiento:check-programados     (Cada 4h)
06:00 PM  → clima:decisiones                    (Cada 6h)
08:00 PM  → mantenimiento:check-programados     (Cada 4h)
12:00 AM  → clima:decisiones                    (Cada 6h)
```

El job de propuestas de asignación corre **bajo demanda**: se dispara al cambiar el
estado de un lote (observer) o al guardar la planificación de tareas, y lo ejecuta el
worker de cola.

---

##  CHECKLIST DE IMPLEMENTACIÓN

- [x] Comandos creados y funcionales
- [x] Scheduler configurado en `routes/console.php`
- [x] Logging implementado en cada tarea
- [x] Manejo de errores con `onFailure()` / `onSuccess()`
- [x] Bloqueo de tareas concurrentes (`withoutOverlapping()`)
- [x] Docker con cron incluido
- [x] Notificaciones por email integradas
- [x] Base de datos para almacenar resultados

---

##  SOPORTE

**Problema:** Las tareas no se ejecutan
-  Verificar que cron está activo: `ps aux | grep cron`
-  Verificar logs: `tail -f storage/logs/laravel.log`
-  Ejecutar manual: `php artisan mantenimiento:check-umbrales`

**Problema:** Errores de conexión
-  Verificar `.env` con credenciales BD
-  Verificar permiso de escritura en `storage/logs/`

**Problema:** Tareas se ejecutan muy lentamente
-  Aumentar timeout en crontab
-  Revisar queries en logs
-  Verificar recursos del servidor

---

##  PRÓXIMOS PASOS

1. **Desplegar en servidor** con cron configurado
2. **Monitorear logs** primeros 3 días
3. **Ajustar horarios** según carga del servidor
4. **Agregar alertas** si tareas fallan

---

Estado: completamente configurado y probado.

El sistema está listo para funcionar automáticamente en producción. 
