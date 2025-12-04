# Sistema de Análisis Climático - Guía Rápida

## 🚀 Inicio Rápido

### 1. Agregar coordenadas a un lote

```bash
# Abrir aplicación web → Menú Lotes → Editar lote
# Agregar latitud/longitud (ejemplo: -26.185040, -58.175400)
# Guardar
```

### 2. Ejecutar análisis

```bash
# Analizar próximos 7 días
php artisan clima:analizar

# Analizar próximos 3 días
php artisan clima:analizar --dias=3
```

### 3. Probar sistema

```bash
# Test básico (agrega coordenadas y ejecuta comando)
php test_analisis_clima.php

# Test con múltiples zonas
php test_alerta_clima_forzada.php
```

---

## 📋 Output Esperado

### Sin alertas:
```
🌦️  Iniciando análisis climático para los próximos 7 días...
📍 Analizando 1 lote(s) con coordenadas GPS...
🌲 Lote: Rapp - Las tunas
   ✅ Sin riesgo de lluvia significativa (< 10mm)
```

### Con alertas:
```
⚠️  ALERTA CLIMÁTICA - Lote Rapp (Las tunas)
    📅 Fecha: 05/12/2025
    🌧️  Lluvia pronosticada: 15.3 mm
    💰 Riesgo de pérdida: $12,450.00
    💡 Sugerencia: Aumentar producción hoy para compensar.
```

---

## 🔧 Configuración

| Componente | Ubicación |
|-----------|-----------|
| **Comando** | `app/Console/Commands/AnalizarRiesgoClimatico.php` |
| **Modelo** | `app/Models/Lote.php` (latitud, longitud) |
| **UI Livewire** | `app/Http/Livewire/Lotes.php` |
| **Vista** | `resources/views/livewire/lotes.blade.php` |
| **Migración** | `database/migrations/2025_12_03_143747_add_coordinates_to_lotes_table.php` |
| **Logs** | `storage/logs/laravel.log` |

---

## 📊 Lógica de Cálculo

```
Costo Día Caído = Σ(Jornal Empleados Activos) + Σ(Alquiler Maquinaria × 10 ton)
```

**Umbral de lluvia**: 10mm (configurable en `AnalizarRiesgoClimatico::UMBRAL_LLUVIA`)

---

## 🌐 API Utilizada

**Open-Meteo Forecast API**  
Endpoint: `https://api.open-meteo.com/v1/forecast`

Parámetros:
- `latitude`, `longitude`: Coordenadas del lote
- `daily=precipitation_sum`: Precipitación diaria
- `timezone=America/Argentina/Buenos_Aires`
- `forecast_days=7`: Días a pronosticar

Documentación: https://open-meteo.com/en/docs

---

## 🔄 Automatización (Opcional)

### Laravel Scheduler

Editar `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('clima:analizar --dias=7')
             ->dailyAt('06:00');
}
```

Configurar cron:
```bash
* * * * * cd /path/to/rennova && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| "No hay lotes con coordenadas" | Agregar latitud/longitud desde menú Lotes |
| "API respondió con status 500" | Verificar conectividad e integridad de coordenadas |
| No se generan alertas | Pronóstico < 10mm. Probar con otras coordenadas o ajustar umbral |

---

## 📚 Documentación Completa

Ver archivo: `SISTEMA_ANALISIS_CLIMATICO.md`

---

## ✅ Checklist

- [x] Migración ejecutada
- [x] Modelo actualizado
- [x] UI funcional
- [x] Comando implementado
- [x] API integrada
- [x] Tests creados
- [x] Documentación completa
- [ ] Automatización configurada (manual)
- [ ] Notificaciones email (opcional)

---

**Última actualización**: 3 de diciembre de 2025
