# 📧 Sistema de Notificaciones por Email - Mantenimiento Preventivo

## 🎯 Funcionalidad Implementada

El sistema automáticamente:

1. **Verifica umbrales de toneladas** de cada maquinaria diariamente a las 2:00 AM
2. **Genera órdenes de mantenimiento preventivo** cuando una maquinaria alcanza su umbral
3. **Envía notificaciones por email** al personal administrativo cuando:
   - Se genera una nueva orden de mantenimiento automáticamente
   - Falta stock de insumos para el kit de mantenimiento

## 📋 Archivos Principales

- **Command**: `app/Console/Commands/CheckMantenimientoUmbrales.php`
- **Notificaciones**:
  - `app/Notifications/MantenimientoCreado.php`
  - `app/Notifications/StockInsuficiente.php`
- **Scheduler**: `routes/console.php` (configurado para ejecutar diariamente)

## ⚙️ Configuración

### 1. Configurar Email en `.env`

#### Opción A: Mailtrap (Desarrollo/Testing)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mantenimiento@rennova.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Opción B: Gmail (Producción)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME="Sistema Rennova"
```

#### Opción C: Log (Solo para desarrollo, no envía emails reales)
```env
MAIL_MAILER=log
```
Los emails se guardarán en `storage/logs/laravel.log`

### 2. Configurar Email del Administrador

En el archivo `config/mail.php` o directamente en `.env`:

```env
MAIL_ADMIN_EMAIL=admin@rennova.com
```

O en `config/mail.php`:
```php
'admin_email' => env('MAIL_ADMIN_EMAIL', 'admin@example.com'),
```

## 🧪 Pruebas

### Probar Manualmente el Comando

```bash
# Ejecutar verificación de umbrales manualmente
php artisan mantenimiento:check-umbrales
```

### Simular Escenario de Prueba

1. **Crear una maquinaria con umbral bajo**:
   - Ir a `/maquinarias`
   - Crear/editar una maquinaria
   - Establecer `Umbral Mantenimiento Preventivo`: 10.00 ton

2. **Registrar toneladas suficientes**:
   - Asegurarse de que la maquinaria tenga `toneladas_acumuladas >= 10.00`
   - Esto se actualiza automáticamente al registrar cargas

3. **Ejecutar el comando**:
   ```bash
   php artisan mantenimiento:check-umbrales
   ```

4. **Verificar resultados**:
   - Se debe crear una orden de mantenimiento en `/mantenimientos`
   - Se debe enviar un email (o registrar en logs si `MAIL_MAILER=log`)
   - Revisar `storage/logs/laravel.log` para ver el email simulado

### Verificar Emails en Logs

```bash
# Ver últimos emails enviados (si MAIL_MAILER=log)
tail -f storage/logs/laravel.log | grep -A 50 "MantenimientoCreado"
```

## 📅 Programación Automática (Scheduler)

El comando se ejecuta automáticamente **todos los días a las 2:00 AM**.

Para activar el scheduler en producción:

### Windows (Task Scheduler)
Crear tarea programada que ejecute cada minuto:
```cmd
cd D:\trabajo_final\rennova && php artisan schedule:run
```

### Linux (Crontab)
```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Prueba Manual del Scheduler
```bash
# Ejecutar tareas programadas manualmente
php artisan schedule:run

# Ver lista de tareas programadas
php artisan schedule:list
```

## 📧 Contenido de las Notificaciones

### 1. Notificación: Mantenimiento Creado
**Asunto**: Nueva Orden de Mantenimiento Generada

**Contenido**:
- ID de la orden
- Maquinaria afectada
- Tipo de mantenimiento (Preventivo)
- Estado (Programado)
- Enlace directo a la orden

### 2. Notificación: Stock Insuficiente
**Asunto**: ⚠️ Advertencia: Stock Insuficiente para Mantenimientos

**Contenido**:
- Lista de órdenes generadas
- Insumos faltantes por orden
- Cantidad faltante vs disponible
- Enlace a la gestión de órdenes

## 🔍 Lógica del Sistema

### Flujo de Verificación:

```
1. ¿Maquinaria tiene umbral configurado?
   └─ No → Omitir
   └─ Sí → Continuar

2. Calcular toneladas desde último mantenimiento preventivo
   └─ Si nunca tuvo: usar toneladas_acumuladas totales
   └─ Si tuvo: restar snapshot del último mantenimiento

3. ¿Toneladas >= Umbral?
   └─ No → Omitir
   └─ Sí → Continuar

4. ¿Ya tiene orden pendiente o en curso?
   └─ Sí → Omitir (evitar duplicados)
   └─ No → Crear orden

5. Crear orden de mantenimiento preventivo

6. Verificar stock del kit de la maquinaria
   └─ Stock OK → Solo enviar notificación de orden creada
   └─ Stock insuficiente → Enviar ambas notificaciones

7. Enviar email(s) al administrador
```

## 🛠️ Troubleshooting

### Problema: No se envían emails

**Solución**:
1. Verificar que `MAIL_MAILER` no esté en `log`
2. Verificar credenciales SMTP
3. Revisar logs: `storage/logs/laravel.log`
4. Probar configuración:
   ```bash
   php artisan tinker
   >>> Mail::raw('Test email', function($m) { $m->to('test@example.com')->subject('Test'); });
   ```

### Problema: No se generan órdenes automáticas

**Solución**:
1. Verificar que la maquinaria tenga `umbral_toneladas` configurado
2. Verificar que `toneladas_acumuladas >= umbral_toneladas`
3. Ejecutar manualmente: `php artisan mantenimiento:check-umbrales`
4. Revisar logs de errores

### Problema: El scheduler no se ejecuta

**Solución**:
1. Verificar que el cron/task esté configurado
2. Ejecutar manualmente: `php artisan schedule:run`
3. Verificar que la hora del servidor sea correcta
4. Revisar logs: `storage/logs/laravel.log`

## 📊 Monitoreo

### Ver logs en tiempo real:
```bash
# Ver todos los logs
tail -f storage/logs/laravel.log

# Solo notificaciones
tail -f storage/logs/laravel.log | grep -i "notification"

# Solo mantenimiento
tail -f storage/logs/laravel.log | grep -i "mantenimiento"
```

### Consultas útiles en la BD:
```sql
-- Ver órdenes generadas automáticamente hoy
SELECT * FROM mantenimientos 
WHERE estado = 'programado' 
AND DATE(created_at) = CURRENT_DATE;

-- Maquinarias cerca del umbral
SELECT 
    m.id_maquinaria,
    m.modelo,
    m.toneladas_acumuladas,
    m.umbral_toneladas,
    (m.toneladas_acumuladas / m.umbral_toneladas * 100) as porcentaje
FROM maquinarias m
WHERE m.umbral_toneladas IS NOT NULL
AND m.estado = 'operativa'
ORDER BY porcentaje DESC;
```

## 🎯 Próximos Pasos Opcionales

1. **Múltiples destinatarios**: Modificar para enviar a varios administradores
2. **Notificaciones en la app**: Agregar notificaciones in-app además de email
3. **Configuración por usuario**: Permitir a usuarios suscribirse/desuscribirse
4. **Plantillas personalizadas**: Crear templates HTML más atractivos
5. **Dashboard de alertas**: Visualizar próximas máquinas que alcanzarán umbral

## 📞 Contacto

Para dudas sobre el sistema de notificaciones, consultar:
- Documentación de Laravel Notifications: https://laravel.com/docs/notifications
- Documentación de Laravel Mail: https://laravel.com/docs/mail
