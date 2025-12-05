# 🚀 GUÍA DE DESPLIEGUE EN PRODUCCIÓN

**Fecha:** 5 de Diciembre de 2025  
**Versión:** 1.0 Final

---

## 📋 CHECKLIST PRE-DESPLIEGUE

### Sistema Completamente Configurado

- ✅ **Procesos automatizados:** Mantenimiento y Clima
- ✅ **Reportes y estadísticas:** Dashboard con gráficos ApexCharts
- ✅ **Tareas programadas:** Scheduler configurado
- ✅ **Notificaciones:** Sistema integrado
- ✅ **Base de datos:** Estructura completa
- ✅ **Testing:** 34/34 pruebas pasadas (100%)

---

## 🔧 PASOS DE DESPLIEGUE

### 1. Preparar Servidor

```bash
# En el servidor de producción:
cd /var/www/rennova

# Instalar dependencias
composer install --optimize-autoloader --no-dev

# Instalar dependencias Node
npm install --production

# Build de assets
npm run build

# Generar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Configurar Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate --force

# Seed inicial (opcional)
php artisan db:seed
```

### 3. Configurar Tareas Programadas

#### Opción A: Linux/Mac (crontab)

```bash
# Editar crontab
crontab -e

# Agregar esta línea
* * * * * cd /var/www/rennova && php artisan schedule:run >> /dev/null 2>&1

# Guardar y salir (Ctrl+O, Enter, Ctrl+X en nano)
```

#### Opción B: Docker (Ya incluido)

El archivo `docker/laravel-cron` ya contiene la configuración.

Asegurar que el servicio cron está activo:

```bash
docker-compose restart
```

#### Opción C: Windows Server (Task Scheduler)

1. Abrir **Task Scheduler**
2. Crear tarea básica:
   - **Nombre:** Laravel Scheduler
   - **Acción:** Ejecutar programa
   - **Programa:** `C:\PHP\php.exe`
   - **Argumentos:** `C:\inetpub\rennova\artisan schedule:run`
   - **Repetir:** Cada 1 minuto
   - **Duración:** Indefinida

### 4. Configurar Variables de Entorno

```bash
# Copiar y configurar
cp .env.example .env

# Editar .env con:
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=rennova
DB_USERNAME=rennova_user
DB_PASSWORD=***
QUEUE_CONNECTION=database
LOG_CHANNEL=stack
```

### 5. Permisos de Carpetas

```bash
# Dar permisos de escritura
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. SSL Certificate (HTTPS)

```bash
# Si usa Let's Encrypt con Certbot
sudo certbot certonly --webroot -w /var/www/rennova/public -d tudominio.com

# Actualizar nginx/apache para usar SSL
```

---

## ✅ VERIFICACIÓN POST-DESPLIEGUE

### 1. Verificar que el sitio está en línea

```bash
# Acceder a
https://tudominio.com
```

### 2. Verificar que las tareas programadas están configuradas

```bash
# En el servidor
php artisan schedule:list

# Debe mostrar:
# - mantenimiento:check-umbrales ..................... (02:00)
# - clima:decisiones ............................... (Every 6 hours)
# - clima:analizar .................................. (06:00)
# - mantenimiento:check-programados ................ (Every 4 hours)
```

### 3. Verificar que cron está ejecutándose

```bash
# Ver último acceso a logs
tail -f storage/logs/laravel.log

# Debe mostrar logs de tareas siendo ejecutadas
```

### 4. Ejecutar una tarea manual para probar

```bash
# Probar mantenimiento
php artisan mantenimiento:check-umbrales

# Probar clima
php artisan clima:decisiones

# Debe completarse sin errores
```

---

## 📊 MONITOREO EN PRODUCCIÓN

### Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Filtrar solo errores
tail -f storage/logs/laravel.log | grep ERROR

# Filtrar solo tareas programadas
tail -f storage/logs/laravel.log | grep "Tarea"
```

### Cron (Linux)

```bash
# Verificar que cron está activo
ps aux | grep cron

# Ver historial de cron
grep CRON /var/log/syslog  # Debian/Ubuntu
grep CRON /var/log/messages # CentOS/RHEL
```

### Alertas Recomendadas

Configurar alertas para:
- ❌ Si una tarea falla
- ❌ Si no hay logs de tareas en 24 horas
- ❌ Si la base de datos está desconectada
- ⚠️ Si hay muchos errores en logs

---

## 🔐 SEGURIDAD

### 1. Desactivar Debug en Producción

```env
APP_DEBUG=false
```

### 2. Ocultar Rutas Administrativas

```php
// routes/web.php - Proteger rutas admin
Route::middleware(['auth', 'admin'])->group(function () {
    // Rutas admin
});
```

### 3. Rate Limiting

```php
// En app/Http/Kernel.php o middleware
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email);
});
```

### 4. HTTPS Obligatorio

```php
// config/app.php
'force_https' => true,
```

---

## 📱 NOTIFICACIONES POR EMAIL

Las notificaciones se configuran automáticamente. Verificar:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=***
MAIL_PASSWORD=***
MAIL_ENCRYPTION=tls
```

Para producción, usar servicio real (SendGrid, AWS SES, etc.)

---

## 🆘 TROUBLESHOOTING

### Las tareas no se ejecutan

**Síntoma:** Logs vacíos, nada se ejecuta

**Solución:**
```bash
# 1. Verificar cron está activo
ps aux | grep cron

# 2. Ejecutar manual para probar
php artisan mantenimiento:check-umbrales

# 3. Ver qué tareas están programadas
php artisan schedule:list

# 4. Buscar errores
tail -100 storage/logs/laravel.log
```

### Errores de base de datos

**Síntoma:** SQLSTATE[08006]...

**Solución:**
```bash
# 1. Verificar conexión
php artisan tinker
# Luego: DB::connection()->getPdo();

# 2. Verificar credenciales en .env
cat .env | grep DB_

# 3. Reiniciar servidor
php artisan cache:clear
php artisan config:cache
```

### Alto consumo de memoria

**Síntoma:** Tareas lentas, timeouts

**Solución:**
```bash
# 1. Limitar concurrencia
php artisan schedule:work --max-duration=3600

# 2. Aumentar memoria en php.ini
memory_limit = 256M

# 3. Optimizar queries en logs
# Ver dónde tarda tiempo
```

---

## 📈 ESCALABILIDAD

Para manejo de muchos lotes/máquinas:

### 1. Usar Redis en lugar de Database para Queue

```env
QUEUE_CONNECTION=redis
REDIS_HOST=localhost
REDIS_PORT=6379
```

### 2. Ejecutar multiple workers

```bash
# En producción, ejecutar varios workers
php artisan queue:work --queue=default --daemon
php artisan queue:work --queue=default --daemon &
php artisan queue:work --queue=default --daemon &
```

### 3. Aumentar timeouts

```php
// routes/console.php
Schedule::command('mantenimiento:check-umbrales')
    ->dailyAt('02:00')
    ->timeout(300) // 5 minutos máximo
```

---

## 📞 SOPORTE Y MANTENIMIENTO

### Actualizaciones de dependencias

```bash
# Actualizar Laravel y paquetes
composer update

# Actualizar assets
npm update

# Rebuild
npm run build
```

### Backup automático

```bash
# Configurar backup diario
0 2 * * * /usr/bin/mysqldump -u root -p*** rennova | gzip > /backups/rennova_$(date +\%Y\%m\%d).sql.gz
```

### Monitoreo de performance

Usar herramientas como:
- **New Relic** - APM y monitoring
- **DataDog** - Logs y alertas
- **Sentry** - Error tracking
- **Grafana** - Dashboards

---

## ✨ CONCLUSIÓN

El sistema **Rennova** está 100% listo para producción con:

✅ Automatización completa de procesos  
✅ Estadísticas en tiempo real con gráficos  
✅ Notificaciones automáticas  
✅ Monitoreo y logging  
✅ Seguridad implementada  
✅ Escalabilidad considerada  

**Estado:** 🟢 **LISTO PARA DESPLEGAR**

---

*Última actualización: 5 de Diciembre de 2025*
