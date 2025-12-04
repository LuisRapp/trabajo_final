# Sistema de Análisis Climático y Predicción de Días Caídos

## 📋 Descripción General

Sistema integrado con **Open-Meteo API** para predecir días de lluvia en lotes forestales y calcular el costo de oportunidad perdido. Permite tomar decisiones proactivas para aumentar la producción y compensar pérdidas anticipadas.

---

## 🎯 Objetivos

1. **Predicción climática**: Consultar pronóstico de lluvia para lotes con coordenadas GPS
2. **Análisis de riesgo**: Identificar días con lluvia superior al umbral (10mm)
3. **Cálculo de costos**: Determinar pérdida económica por día caído climático
4. **Alertas proactivas**: Notificar para aumentar producción antes del evento

---

## 🏗️ Arquitectura del Sistema

### Componentes Implementados

#### 1. **Base de Datos**
- **Migración**: `2025_12_03_143747_add_coordinates_to_lotes_table.php`
- **Campos agregados a `lotes`**:
  - `latitud` DECIMAL(10,8) nullable
  - `longitud` DECIMAL(11,8) nullable

#### 2. **Modelo**
- **Archivo**: `app/Models/Lote.php`
- **Cambios**: 
  - Agregado `'latitud', 'longitud'` a `$fillable`
  - Validación de rangos: lat (-90,90), lng (-180,180)

#### 3. **Interfaz de Usuario**
- **Componente Livewire**: `app/Http/Livewire/Lotes.php`
  - Propiedades: `$latitud`, `$longitud`
  - Validación: `'latitud' => 'nullable|numeric|between:-90,90'`
  
- **Vista Blade**: `resources/views/livewire/lotes.blade.php`
  - **Formulario**: Inputs con `step="0.00000001"`, min/max, placeholder
  - **Tabla**: Columna "Coordenadas GPS" con enlace a Google Maps
  - **Alert informativo**: Instrucciones y link para buscar coordenadas

#### 4. **Comando Artisan**
- **Archivo**: `app/Console/Commands/AnalizarRiesgoClimatico.php`
- **Signature**: `clima:analizar {--dias=7}`
- **Descripción**: Analiza pronóstico y genera alertas

---

## 🔧 Instalación y Configuración

### Paso 1: Ejecutar Migración

```bash
php artisan migrate
```

**Output esperado**:
```
2025_12_03_143747_add_coordinates_to_lotes_table ........ 105.09ms DONE
```

### Paso 2: Agregar Coordenadas a Lotes

1. Ir al menú **Lotes** en la aplicación
2. Crear/editar un lote
3. Buscar coordenadas en [Google Maps](https://www.google.com/maps)
4. Copiar latitud y longitud en los campos correspondientes
5. Guardar

**Ejemplo de coordenadas**:
- **Formosa, Argentina**: `-26.185040, -58.175400`
- **Posadas, Misiones**: `-27.367794, -55.896108`
- **Puerto Iguazú**: `-25.695139, -54.436389`

---

## 🚀 Uso del Sistema

### Ejecución Manual

```bash
# Analizar próximos 7 días (default)
php artisan clima:analizar

# Analizar próximos 3 días
php artisan clima:analizar --dias=3

# Analizar próximos 14 días
php artisan clima:analizar --dias=14
```

### Output del Comando

#### Sin alertas:
```
🌦️  Iniciando análisis climático para los próximos 7 días...

📍 Analizando 1 lote(s) con coordenadas GPS...

🌲 Lote: Rapp - Las tunas
   ✅ Sin riesgo de lluvia significativa (< 10mm)

═══════════════════════════════════════════════════
📊 RESUMEN DEL ANÁLISIS CLIMÁTICO
═══════════════════════════════════════════════════
   Lotes analizados: 1
   Alertas generadas: 0
   Costo evitable estimado: $0.00
```

#### Con alertas:
```
🌲 Lote: Rapp - Las tunas
   ⚠️  ALERTA CLIMÁTICA - Lote Rapp (Las tunas)
       📅 Fecha: 05/12/2025
       🌧️  Lluvia pronosticada: 15.3 mm
       💰 Riesgo de pérdida: $12,450.00
       💡 Sugerencia: Aumentar producción hoy para compensar.

═══════════════════════════════════════════════════
📊 RESUMEN DEL ANÁLISIS CLIMÁTICO
═══════════════════════════════════════════════════
   Lotes analizados: 1
   Alertas generadas: 1
   Costo evitable estimado: $12,450.00

💡 RECOMENDACIÓN: Considere aumentar la producción hoy para compensar las pérdidas estimadas.
```

---

## 📊 Cálculo de Costos

### Lógica Implementada

El comando calcula el **costo estructural diario** sumando:

#### A) Mano de Obra
- **Fuente**: Empleados activos
- **Método**: Reutiliza `CalculaCostosLaborales` trait
- **Cálculo**: `$empleado->calcularCostoDia(Carbon::today(), true, null)`
  - `true` = día caído (usa jornal diario, no destajo)
  - Consulta `HistoricoRolLaboral` para tarifa vigente

#### B) Maquinaria
- **Fuente**: Maquinarias en alquiler (`tipo_maquinaria = 'alquiler'`)
- **Cálculo**: `precio_alquiler_destajo × 10 toneladas estimadas/día`
- **Justificación**: Costo fijo diario de equipos alquilados

### Fórmula Total

```
Costo Día Caído = Σ(Jornal Empleados) + Σ(Alquiler Maquinaria × 10 ton)
```

---

## 🌐 Integración con Open-Meteo API

### Endpoint Utilizado

```
https://api.open-meteo.com/v1/forecast
```

### Parámetros

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| `latitude` | `-26.185040` | Latitud del lote |
| `longitude` | `-58.175400` | Longitud del lote |
| `daily` | `precipitation_sum` | Precipitación diaria total |
| `timezone` | `America/Argentina/Buenos_Aires` | Zona horaria |
| `forecast_days` | `7` | Días a pronosticar (default) |

### Respuesta JSON

```json
{
  "daily": {
    "time": ["2025-12-03", "2025-12-04", "2025-12-05"],
    "precipitation_sum": [0.0, 5.2, 15.3]
  }
}
```

### Lógica de Umbral

```php
const UMBRAL_LLUVIA = 10; // mm

if ($mm >= self::UMBRAL_LLUVIA) {
    // Generar alerta
}
```

**Criterio**: 10mm es considerado lluvia moderada que impide operaciones forestales.

---

## 📝 Logging y Alertas

### Registro en Logs

Todas las alertas se escriben en `storage/logs/laravel.log`:

```php
Log::warning('Alerta Climática', [
    'lote_id' => 1,
    'lote_nombre' => 'Rapp',
    'ubicacion' => 'Las tunas',
    'fecha_lluvia' => '2025-12-05',
    'precipitacion_mm' => 15.3,
    'costo_estimado' => 12450.00,
]);
```

### Formato de Log

```
[2025-12-03 14:30:45] local.WARNING: Alerta Climática
{
  "lote_id": 1,
  "lote_nombre": "Rapp",
  "ubicacion": "Las tunas",
  "fecha_lluvia": "2025-12-05",
  "precipitacion_mm": 15.3,
  "costo_estimado": 12450
}
```

---

## 🔄 Automatización (Opcional)

### Configuración en Cron

Para ejecutar análisis automático diario:

#### Linux/macOS

Editar crontab:
```bash
crontab -e
```

Agregar línea:
```
0 6 * * * cd /path/to/rennova && php artisan clima:analizar --dias=7
```

**Explicación**: Ejecuta todos los días a las 6:00 AM, analiza próximos 7 días.

#### Windows Task Scheduler

1. Abrir "Programador de tareas"
2. Crear tarea básica:
   - **Nombre**: Análisis Climático Rennova
   - **Desencadenador**: Diariamente a las 6:00 AM
   - **Acción**: Iniciar programa
     - **Programa**: `php.exe`
     - **Argumentos**: `artisan clima:analizar --dias=7`
     - **Directorio**: `D:\trabajo_final\rennova`

### Integración con Laravel Scheduler

Editar `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('clima:analizar --dias=7')
             ->dailyAt('06:00')
             ->timezone('America/Argentina/Buenos_Aires');
}
```

Configurar cron para ejecutar scheduler:
```
* * * * * cd /path/to/rennova && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Scripts de Prueba

### 1. Prueba Básica

**Archivo**: `test_analisis_clima.php`

```bash
php test_analisis_clima.php
```

**Función**:
- Asigna coordenadas a un lote
- Ejecuta `clima:analizar --dias=3`
- Valida persistencia en BD
- Muestra resumen

### 2. Prueba con Múltiples Zonas

**Archivo**: `test_alerta_clima_forzada.php`

```bash
php test_alerta_clima_forzada.php
```

**Función**:
- Prueba 3 coordenadas diferentes:
  - Buenos Aires
  - Posadas
  - Puerto Iguazú
- Ejecuta análisis para cada zona
- Identifica cuál genera alertas

---

## 🔍 Troubleshooting

### Error: "No hay lotes activos con coordenadas GPS"

**Causa**: Ningún lote tiene `latitud` y `longitud` configuradas.

**Solución**:
1. Ir al menú Lotes
2. Editar lote existente
3. Agregar coordenadas
4. Guardar

### Error: "API respondió con status 500/400"

**Causa**: Open-Meteo API no responde o coordenadas inválidas.

**Solución**:
1. Verificar coordenadas están en rango válido (-90,90 lat / -180,180 lng)
2. Revisar conectividad a internet
3. Consultar status de API: https://open-meteo.com/

### Error: "Sin histórico de tarifa para empleado"

**Causa**: Empleado activo sin registro en `historico_rol_laboral`.

**Solución**:
- El sistema estima costo base
- Para precisión, asegurar que todos los empleados tengan histórico de tarifas vigente

### No se generan alertas

**Causa**: Pronóstico de lluvia < 10mm en todos los días.

**Verificación**:
1. Revisar manualmente pronóstico en: https://open-meteo.com/
2. Ajustar `UMBRAL_LLUVIA` en `AnalizarRiesgoClimatico.php` si es necesario
3. Aumentar `--dias` para analizar período más largo

---

## 📈 Mejoras Futuras

### 1. Notificaciones por Email

```php
// En generarAlerta()
Notification::send($usuarios, new AlertaClimatica($lote, $diaLluvia, $costoEstructural));
```

**Requiere**:
- Crear `app/Notifications/AlertaClimatica.php`
- Configurar SMTP en `.env`

### 2. Dashboard de Alertas

- Crear modelo `AlertaClimatica` con migración
- Vista Livewire para mostrar historial
- Gráficos de pronóstico con Chart.js

### 3. Ajuste Dinámico de Umbral

```php
// Configuración por lote
$lote->umbral_lluvia ?? self::UMBRAL_LLUVIA
```

### 4. Integración con Calendario

- Sincronizar alertas con Google Calendar
- Crear eventos automáticos para días de riesgo

### 5. Machine Learning

- Analizar histórico de alertas vs partes diarios reales
- Ajustar predicción de costos con datos históricos

---

## 📚 Referencias

- **Open-Meteo API Docs**: https://open-meteo.com/en/docs
- **Laravel HTTP Client**: https://laravel.com/docs/12.x/http-client
- **Laravel Console**: https://laravel.com/docs/12.x/artisan
- **Google Maps Coordinates**: https://www.google.com/maps

---

## ✅ Checklist de Implementación

- [x] Migración de coordenadas a lotes
- [x] Actualización de modelo Lote
- [x] UI para editar coordenadas (Livewire + Blade)
- [x] Comando `clima:analizar`
- [x] Integración con Open-Meteo API
- [x] Cálculo de costo estructural diario
- [x] Sistema de alertas en consola y logs
- [x] Scripts de prueba
- [x] Documentación completa
- [ ] Notificaciones email (opcional)
- [ ] Automatización con cron (configuración manual)
- [ ] Dashboard de alertas (futuro)

---

## 🤝 Soporte

Para consultas o issues, revisar:
1. Logs en `storage/logs/laravel.log`
2. Ejecutar scripts de prueba
3. Verificar configuración de coordenadas en BD

**Comando de diagnóstico**:
```bash
php artisan tinker
>>> Lote::whereNotNull('latitud')->whereNotNull('longitud')->count()
```

Debe retornar > 0 para que el sistema funcione.
