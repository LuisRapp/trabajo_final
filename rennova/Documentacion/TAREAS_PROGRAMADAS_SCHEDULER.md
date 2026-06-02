# ⏰ TAREAS PROGRAMADAS (SCHEDULER)

Ultima actualizacion: 8 de febrero de 2026.

**Fecha:** 5 de Diciembre de 2025  
Estado: configurado y listo.

---

##  RESUMEN

El sistema Rennova tiene **2 procesos críticos automatizados** que se ejecutan en el servidor sin intervención manual:

1.  **Generación automática de órdenes de mantenimiento** (cuando la maquinaria supera umbrales)
2.  **Actualización de pronóstico climático** (análisis de riesgo y recomendaciones)

---

##  TAREAS CONFIGURADAS

### 1️⃣ Verificación de Umbrales de Mantenimiento
```
Comando: mantenimiento:check-umbrales
Frecuencia: Diariamente a las 2:00 AM
Descripción: Verifica si la maquinaria supera sus umbrales de toneladas acumuladas y genera automáticamente órdenes de mantenimiento preventivo
```

**¿Qué hace?**
- Revisa todas las maquinarias operativas
- Compara `toneladas_acumuladas` vs `umbral_toneladas`
- Si se supera → Crea orden de mantenimiento automáticamente
- Notifica por email y en el sistema

**Archivos:**
- Comando: `app/Console/Commands/CheckMantenimientoUmbrales.php` (305 líneas)
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
- Registra decisiones en el sistema

**Archivos:**
- Comando: `app/Console/Commands/AnalizarDecisionesClimaticas.php` (170 líneas)
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
- Analiza impacto en producción forestales
- Genera alertas de riesgo climático
- Registra análisis en base de datos

**Archivos:**
- Comando: `app/Console/Commands/AnalizarRiesgoClimatico.php` (250 líneas)
- Configuración: `routes/console.php`

---

### 4️⃣ Verificación de Mantenimientos Programados
```
Comando: mantenimiento:check-programados
Frecuencia: Cada 4 horas
Descripción: Verifica estado de mantenimientos programados
```

**Archivos:**
- Comando: `app/Console/Commands/CheckMantenimientosProgramados.php`
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

// Tarea 1: Mantenimiento - Diariamente 2:00 AM
Schedule::command('mantenimiento:check-umbrales')
    ->dailyAt('02:00')
    ->withoutOverlapping(10)
    ->onFailure(fn() => Log::error('...'))
    ->onSuccess(fn() => Log::info('...'));

// Tarea 2: Clima Decisiones - Cada 6 horas
Schedule::command('clima:decisiones')
    ->everySixHours()
    ->withoutOverlapping(5);

// Tarea 3: Análisis Riesgo - Diariamente 6:00 AM
Schedule::command('clima:analizar --dias=7')
    ->dailyAt('06:00')
    ->withoutOverlapping(10);

// Tarea 4: Chequeo Programados - Cada 4 horas
Schedule::command('mantenimiento:check-programados')
    ->everyFourHours()
    ->withoutOverlapping(5);
```

---

##  TESTING - EJECUTAR MANUAL

Para probar sin esperar a la hora programada:

```bash
# Ejecutar mantenimiento ahora
php artisan mantenimiento:check-umbrales

# Ejecutar con simulación
php artisan mantenimiento:check-umbrales --maquinaria=1 --simular

# Ejecutar análisis de clima para lote específico
php artisan clima:decisiones --lote=1

# Ejecutar análisis de riesgo (7 días)
php artisan clima:analizar --dias=7

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
[2025-12-05 02:00:15] local.INFO: Tarea de mantenimiento completada: mantenimiento:check-umbrales
[2025-12-05 02:00:15] local.INFO:  Orden creada - Maquinaria ID: 5 - Toneladas: 1200/1000
[2025-12-05 06:00:22] local.INFO: Tarea de clima completada: clima:analizar
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

### 3. **Queue driver configurado**
   ```
   # En .env
   QUEUE_CONNECTION=database
   ```

### 4. **API de clima (Open-Meteo)**
   - No requiere autenticación
   - Debe tener acceso a internet
   - Límite: 10,000 requests/día (suficiente)

---

##  ORDEN DE EJECUCIÓN

```timeline
02:00 AM  → mantenimiento:check-umbrales        (Diario)
04:00 AM  → mantenimiento:check-programados     (Cada 4h)
06:00 AM  → clima:analizar                      (Diario)
08:00 AM  → mantenimiento:check-programados     (Cada 4h)
12:00 PM  → clima:decisiones                    (Cada 6h)
12:00 PM  → mantenimiento:check-programados     (Cada 4h)
04:00 PM  → mantenimiento:check-programados     (Cada 4h)
06:00 PM  → clima:decisiones                    (Cada 6h)
08:00 PM  → mantenimiento:check-programados     (Cada 4h)
12:00 AM  → clima:decisiones                    (Cada 6h)
```

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
