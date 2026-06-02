# ️ Guia Completa: API del Clima y Toma de Decisiones

Ultima actualizacion: 8 de febrero de 2026.

## Índice
1. [Flujo General (3 Fases)](#flujo-general-3-fases)
2. [PASO A: Mapeo de Días Inactivos](#paso-a-mapeo-de-días-inactivos)
3. [PASO B: Estrategia de Anticipación](#paso-b-estrategia-de-anticipación)
4. [PASO C: Estrategia de Reacción](#paso-c-estrategia-de-reacción)
5. [Integración Open-Meteo API](#-integración-open-meteo-api)
6. [Cálculo de Costos](#-cálculo-de-costos)
7. [Constantes de Configuración](#-constantes-de-configuración)
8. [Uso del Sistema](#-uso-del-sistema)
9. [Archivos Clave](#-archivos-clave)

---

## Flujo General (3 Fases)

El sistema utiliza un flujo de decisión de 3 fases para analizar el clima y generar recomendaciones operativas:

```
┌─────────────────────────────────────────┐
│  1. Validar coordenadas GPS del lote   │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  2. Obtener pronóstico Open-Meteo API  │
│     (precipitation_sum, cloudcover)    │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  PASO A: Mapear Días Inactivos         │
│  - Lluvia > 10mm → INACTIVO             │
│  - Post-lluvia nublado → INACTIVO       │
└──────────────┬──────────────────────────┘
               ↓
        ¿Hay días previos operativos?
               │
       ┌───────┴───────┐
       │               │
      SÍ              NO
       │               │
       ↓               ↓
   ANTICIPACIÓN     REACCIÓN
```

### Descripción del Flujo

1. **Validación**: Verifica que el lote tenga coordenadas GPS válidas
2. **Obtención de datos**: Consulta el API de Open-Meteo para obtener pronóstico de 7 días
3. **Análisis**: Mapea qué días serán operativos y cuáles no
4. **Decisión**: Determina la estrategia según la disponibilidad de días operativos previos

---

## PASO A: Mapeo de Días Inactivos

### Objetivo
Identificar días que no serán operativos por lluvia o barro.

### Lógica de Análisis (actualizada)

El sistema analiza los próximos 7 días del pronóstico con **granularidad horaria** y **saturación de suelo** para reducir falsos positivos:

1. **Granularidad horaria (ventana operativa 06:00–18:00)**
   - Si `precipitación_diaria > 10mm`, se revisa el acumulado **horario**.
   - **INACTIVO** solo si la lluvia acumulada **entre 06:00 y 18:00** supera **5mm**.
   - Si llueve solo de noche, el día se marca como **OPERATIVO CONDICIONAL** (se trabaja hoy, pero afecta la saturación del día siguiente).

2. **Saturación de suelo (lluvia acumulada)**
   - Fórmula:
     - $\text{Saturacion\_Index} = \text{Lluvia\_Hoy} + (\text{Lluvia\_Ayer} \times 0.5)$
   - Si $\text{Saturacion\_Index} > 12\text{mm}$, el terreno está en **riesgo de barro**.

3. **Factor de secado (viento vs. nubes)**
   - El día puede ser **OPERATIVO** aunque esté nublado si:
     - `wind_speed_10m_max > 15 km/h` **o** `et0_fao_evapotranspiration > 4mm`.
   - Solo se marca **INACTIVO** si hay **saturación alta**, **nubosidad alta** y **poco viento**.

### Salidas del PASO A

```php
[
   'dias_detalle' => [
      ['fecha' => '04/12', 'estado' => 'OPERATIVO', 'precipitacion_mm' => 0.2, 'nubosidad' => 45, 'viento_max' => 12, 'et0' => 3.2],
      ['fecha' => '05/12', 'estado' => 'OPERATIVO_CONDICIONAL', 'razon' => 'Lluvia nocturna', 'lluvia_diurna_mm' => 1.2],
      ['fecha' => '06/12', 'estado' => 'INACTIVO', 'razon' => 'Saturación alta + nubosidad', 'saturacion_index' => 13.5],
      ['fecha' => '07/12', 'estado' => 'OPERATIVO', 'precipitacion_mm' => 0, 'nubosidad' => 30, 'viento_max' => 18, 'et0' => 4.5],
   ],
    'total_dias_perdidos' => 2,
    'volumen_riesgo' => 100.0,  // toneladas
    'dia_cero_index' => 1,      // Índice del primer día de lluvia
    'dias_operativos_previos' => 1  // Días secos antes del Día Cero
]
```

### Pseudocódigo (actualizado)

```php
$dia_cero_index = null;
$dias_operativos_previos = 0;

foreach (próximos 7 días as $i => $dia) {
   $lluvia_hoy = $dia['daily']['precipitation_sum'];
   $lluvia_ayer = $i > 0 ? $dias[$i-1]['daily']['precipitation_sum'] : 0;
   $nubosidad = $dia['daily']['cloudcover_mean'];
   $viento_max = $dia['daily']['wind_speed_10m_max'];
   $et0 = $dia['daily']['et0_fao_evapotranspiration'];

   // Ventana operativa 06:00–18:00
   $lluvia_diurna = sum(hourly.precipitation between 06:00 and 18:00);

   // Saturación de suelo
   $saturacion_index = $lluvia_hoy + ($lluvia_ayer * 0.5);
   $saturacion_alta = $saturacion_index > 12;

   $lluvia_diurna_intensa = ($lluvia_hoy > 10) && ($lluvia_diurna > 5);

   if ($lluvia_diurna_intensa) {
      estado = 'INACTIVO';
      razon = "Lluvia diurna > 5mm";
      if ($dia_cero_index === null) $dia_cero_index = $i;
   } else if ($lluvia_hoy > 10 && $lluvia_diurna <= 5) {
      estado = 'OPERATIVO_CONDICIONAL';
      razon = "Lluvia nocturna";
   } else {
      // Regla de barro con factor de secado
      $secado_activo = ($viento_max > 15) || ($et0 > 4);
      if ($saturacion_alta && $nubosidad > 60 && !$secado_activo) {
         estado = 'INACTIVO';
         razon = "Saturación alta + nubosidad + poco viento";
         if ($dia_cero_index === null) $dia_cero_index = $i;
      } else {
         estado = 'OPERATIVO';
      }
   }

   if ($dia_cero_index === null && estado === 'OPERATIVO') {
      $dias_operativos_previos++;
   }
}
```

---

## PASO B: Estrategia de Anticipación

### Objetivo
Aumentar producción **ANTES** de la lluvia para compensar las pérdidas estimadas.

### Condición de Activación
Se activa cuando hay **al menos 1 día operativo previo** antes del Día Cero.

### Fórmula de Cálculo

```
Meta Diaria = Promedio de últimos 30 días (ó 50 ton si no hay datos)
Volumen Riesgo = Meta Diaria × Días Perdidos
Aumento Necesario = Volumen Riesgo / Días Operativos Previos
% Aumento = (Aumento Necesario / Meta Diaria) × 100
```

### Decisiones Posibles

| Condición | Decisión | Acción |
|-----------|----------|--------|
| `% Aumento < 25%` |  **VIABLE** | Aumentar producción inmediatamente |
| `% Aumento ≥ 25%` | ️ **LÍMITE** | Aumento muy agresivo, requiere revisión manual |

### Ejemplo Práctico 1 (Viable)

```
Meta Diaria: 50 toneladas
Días Perdidos: 2
Días Operativos Previos: 4

Volumen Riesgo = 50 × 2 = 100 toneladas
Aumento Necesario = 100 / 4 = 25 toneladas/día
% Aumento = (25 / 50) × 100 = 50%

 NO VIABLE (50% > 25%)
```

### Ejemplo Práctico 2 (Límite)

```
Meta Diaria: 60 toneladas
Días Perdidos: 2
Días Operativos Previos: 6

Volumen Riesgo = 60 × 2 = 120 toneladas
Aumento Necesario = 120 / 6 = 20 toneladas/día
% Aumento = (20 / 60) × 100 = 33.3%

️ AL LÍMITE (33.3% > 25%)
```

### Recomendación Generada

```
 ESTRATEGIA: ANTICIPACIÓN [MEDIA]

️ ALERTA DE LLUVIA EN 6 DÍAS (09/12/2025)

 ANÁLISIS:
   • Pronóstico de lluvia: 15.3 mm
   • Días que se perderán: 2
   • Costo estimado: $12,450.00

 RECOMENDACIÓN:

   Aumentar la producción hoy en un 12% para compensar 
   las pérdidas esperadas. Distribuir el aumento en los 
   próximos 4 días operativos.
   
   Meta ajustada: 56 toneladas/día
   (Meta normal: 50 toneladas/día)
```

---

## PASO C: Estrategia de Reacción

### Objetivo
Decidir qué hacer **DURANTE** la lluvia cuando no hay días operativos previos.

### Condición de Activación
Se activa cuando hay **0 días operativos previos** (lluvia es inminente).

### Dos Opciones Disponibles

#### OPCIÓN 1: Mantenimiento Preventivo

```
 Mejor cuando:
   • Hay equipos con mantenimiento pendiente
   • Condiciones climáticas permitirán secar el área después
   • Personal técnico disponible

 Actividades:
   • Reparación de maquinaria
   • Revisión técnica de equipos
   • Cambio de aceites/filtros
   • Mantenimiento preventivo

 Costos:
   • Se pagan solo jornales (no destajo)
   • Evita daño a equipos por inactividad
```

#### OPCIÓN 2: Suspensión de Jornada

```
 Mejor cuando:
   • Emergencia climática (lluvia muy intensa)
   • No hay mantenimiento urgente
   • Necesidad de minimizar costos

 Actividades:
   • Cerrar operaciones completamente
   • Personal se queda en casa
   • Maquinaria sin movimiento

 Costos:
   • Pago mínimo (solo jornal legal)
   • Reducción máxima de gastos
```

### Ejemplo de Reacción

```
 Lote: Rapp - Las Tunas

    REACCIÓN [ALTA URGENCIA]

    LLUVIA INMEDIATA (Mañana a partir de las 14hs)
   
    DATOS:
      • Pronóstico: 28.5 mm de lluvia
      • Impacto: 3 días sin operar
      • Costo estimado: $18,750.00

    DECISIÓN RECOMENDADA: MANTENIMIENTO PREVENTIVO

      Ejecutar mantenimiento preventivo durante los 3 días 
      de lluvia. Se ha identificado que la cortadora necesita
      revisión técnica urgente.

      Esto maximiza el uso del tiempo inactivo y evita 
      problemas mecánicos más adelante.
```

---

##  Integración Open-Meteo API

### Descripción
Open-Meteo es un API meteorológico **gratuito** que proporciona pronósticos climáticos precisos sin requerer autenticación.

### Endpoint

```
https://api.open-meteo.com/v1/forecast
```

### Parámetros de Solicitud

```php
[
    'latitude' => $lote->latitud,          // Ej: -25.695139
    'longitude' => $lote->longitud,        // Ej: -54.436389
   'daily' => 'precipitation_sum,cloudcover_mean,wind_speed_10m_max,et0_fao_evapotranspiration',
   'hourly' => 'precipitation',
    'timezone' => 'America/Argentina/Buenos_Aires',
    'forecast_days' => 7,
]
```

### Ejemplo de Solicitud Completa

```bash
curl "https://api.open-meteo.com/v1/forecast?latitude=-25.695139&longitude=-54.436389&daily=precipitation_sum,cloudcover_mean,wind_speed_10m_max,et0_fao_evapotranspiration&hourly=precipitation&timezone=America/Argentina/Buenos_Aires&forecast_days=7"
```

### Respuesta JSON

```json
{
  "daily": {
    "time": ["2025-12-04", "2025-12-05", "2025-12-06", "2025-12-07"],
    "precipitation_sum": [0.2, 15.3, 2.1, 0.0],
      "cloudcover_mean": [45, 80, 75, 30],
      "wind_speed_10m_max": [12.4, 9.8, 16.2, 18.1],
      "et0_fao_evapotranspiration": [3.2, 2.1, 4.5, 5.0]
   },
   "hourly": {
      "time": ["2025-12-04T00:00", "2025-12-04T01:00", "..."],
      "precipitation": [0.0, 0.1, "..."]
  }
}
```

### Campos Utilizados

| Campo | Descripción | Rango |
|-------|-------------|-------|
| `precipitation_sum` | Precipitación total del día en mm | 0-100+ |
| `cloudcover_mean` | Cobertura promedio de nubes | 0-100% |
| `wind_speed_10m_max` | Viento máximo del día (km/h) | 0-150+ |
| `et0_fao_evapotranspiration` | Evapotranspiración diaria (mm) | 0-10+ |
| `hourly.precipitation` | Precipitación horaria (mm) | 0-10+ |

> Nota: Para la ventana operativa se suman las horas entre **06:00 y 18:00**.

### Cómo Obtener Coordenadas GPS

1. Ir a [Google Maps](https://www.google.com/maps)
2. Buscar la ubicación del lote
3. Click derecho en la ubicación
4. Las coordenadas aparecen en el formato: `latitud, longitud`

**Ejemplos por región**:
- Formosa, Argentina: `-26.185040, -58.175400`
- Posadas, Misiones: `-27.367794, -55.896108`
- Puerto Iguazú: `-25.695139, -54.436389`
- Buenos Aires: `-34.6037, -58.3816`

---

##  Cálculo de Costos

### Meta Diaria Estimada

```php
$promedio_historico = ParteDiario::where('fecha', '>=', Carbon::now()->subDays(30))
    ->whereHas('cargas')
    ->withSum('cargas', 'peso_neto')
    ->get()
    ->avg('cargas_sum_peso_neto');

$meta_diaria = $promedio_historico ?: 50; // Default 50 toneladas si no hay datos
```

**Fuente**: Promedio de los últimos 30 días de partes diarios con cargas registradas.

### Costo Estructural Diario

El costo total diario se compone de:

#### A) Mano de Obra

- **Fuente**: Todos los empleados activos
- **Cálculo**: Utiliza el trait `CalculaCostosLaborales`
- **Método**: `$empleado->calcularCostoDia(Carbon::today(), true, null)`
  - Parámetro `true` = día caído (paga jornal diario, no destajo)
  - Consulta histórico de roles laborales para tarifa vigente

#### B) Maquinaria

- **Fuente**: Equipos en alquiler (`tipo_maquinaria = 'alquiler'`)
- **Cálculo**: `precio_alquiler_destajo × 10 toneladas estimadas/día`
- **Justificación**: Costo fijo diario de equipos alquilados

### Ejemplo de Cálculo

```
Empleados: 12 × $200/día = $2,400.00
Maquinaria: 3 equipos × $150/día = $450.00
─────────────────────────────────────
Costo Diario Total = $2,850.00
Días Perdidos = 2
─────────────────────────────────────
Costo Total Riesgo = $2,850.00 × 2 = $5,700.00
```

---

##  Constantes de Configuración

```php
const UMBRAL_LLUVIA = 10;              // mm (lluvia que impide operaciones)
const UMBRAL_NUBOSIDAD = 60;           // % (indica terreno húmedo/barro)
const MAX_AUMENTO_PRODUCCION = 1.25;   // Máximo 25% de aumento permitido
const DIAS_FORECAST = 7;               // Pronóstico a 7 días
```

### Significado

- **UMBRAL_LLUVIA**: Precipitación mínima que hace un día inoperativo
- **UMBRAL_NUBOSIDAD**: Porcentaje de nubes que indica barro (después de lluvia)
- **MAX_AUMENTO_PRODUCCION**: Límite máximo permitido para anticipación
- **DIAS_FORECAST**: Rango temporal del pronóstico analizado

---

## ️ Uso del Sistema

### Comando Artisan

#### Analizar todos los lotes
```bash
php artisan clima:decisiones
```

Analiza todos los lotes activos que tengan coordenadas GPS configuradas.

#### Analizar un lote específico
```bash
php artisan clima:decisiones --lote=5
```

Analiza solo el lote con ID=5.

### Output del Comando

```
️  Sistema de Decisiones Climáticas Inteligentes
═══════════════════════════════════════════════════

 Analizando 3 lote(s)...

 Lote Rapp - Las Tunas

    ANTICIPACION   MEDIA 

   ️ ALERTA DE LLUVIA EN 6 DÍAS (09/12/2025)
    Fecha: 05/12/2025
   ️  Lluvia pronosticada: 15.3 mm
    Riesgo de pérdida: $12,450.00
    Sugerencia: Aumentar producción hoy para compensar.

───────────────────────────────────────────────────

═══════════════════════════════════════════════════
 RESUMEN DE ANÁLISIS
═══════════════════════════════════════════════════
   Total de lotes analizados: 3
   Estrategias de Anticipación: 2
   Estrategias de Reacción: 1

 ACCIÓN REQUERIDA: Revisar alertas de anticipación y ajustar producción HOY.
```

### Uso Programático

```php
use App\Services\ClimaDecisionService;
use App\Models\Lote;

$climaService = new ClimaDecisionService();
$lote = Lote::find(1);

$resultado = $climaService->analizarYRecomendar($lote);

if ($resultado['success']) {
    echo "Estrategia: " . $resultado['estrategia'];
    echo "Nivel: " . $resultado['nivel_urgencia'];
    echo $resultado['recomendacion'];
    
    // Acceder a datos calculados
    $datos = $resultado['datos_calculados'];
    echo "Meta diaria: " . $datos['meta_diaria'];
    echo "Costo diario: " . $datos['costo_diario'];
}
```

### Respuesta de la API

```php
[
    'success' => true,
    'estrategia' => 'ANTICIPACION',           // ó 'REACCION'
    'nivel_urgencia' => 'MEDIA',              // 'BAJA', 'MEDIA', 'ALTA'
    'accion_recomendada' => 'Aumentar producción un 12%',
    'recomendacion' => 'Texto completo con instrucciones...',
    'datos_calculados' => [
        'meta_diaria' => 50.0,
        'dias_perdidos' => 2,
        'costo_diario' => 2850.00,
        'volumen_riesgo' => 100.0,
        'porcentaje_aumento' => 12.5,
    ]
]
```

---

##  Archivos Clave

### Servicio Principal
- **Archivo**: `app/Services/ClimaDecisionService.php`
- **Descripción**: Motor principal que contiene toda la lógica de análisis
- **Métodos principales**:
  - `analizarYRecomendar(Lote $lote)` - Método principal
  - `mapearDiasInactivos($pronostico)` - PASO A
  - `estrategiaAnticipacion($lote, $analisis)` - PASO B
  - `estrategiaReaccion($lote, $analisis)` - PASO C

### Comando Artisan
- **Archivo**: `app/Console/Commands/AnalizarDecisionesClimaticas.php`
- **Descripción**: Comando para ejecutar análisis desde terminal
- **Comando**: `php artisan clima:decisiones`

### Modelos
- **Lote.php**: Contiene propiedades `latitud` y `longitud`
- **ParteDiario.php**: Datos históricos de producción
- **Empleado.php**: Información de trabajadores y costos
- **Maquinaria.php**: Equipos alquilados

### Scripts de Prueba
- **test_clima_decision_service.php**: Pruebas exhaustivas con 3 escenarios
- **scripts/demo_clima_decisions.php**: Demostración interactiva

### Documentación
- **README_CLIMA_DECISION_SERVICE.md**: Documentación técnica detallada
- **SISTEMA_ANALISIS_CLIMATICO.md**: Sistema de análisis climático base
- **TAREAS_PROGRAMADAS_SCHEDULER.md**: Integración con scheduler

---

##  Ejecutar Pruebas

Para verificar que el sistema funciona correctamente:

```bash
# Prueba exhaustiva con múltiples escenarios
php test_clima_decision_service.php

# Demo interactiva
php scripts/demo_clima_decisions.php

# Comando Artisan
php artisan clima:decisiones
```

**Tiempo de ejecución esperado**: ~1200ms por lote

---

## ⏰ Ejecución Automática

El sistema está programado para ejecutarse automáticamente cada 6 horas mediante el scheduler de Laravel:

```php
// en routes/console.php
Schedule::command('clima:decisiones')->everyTwoHours();
```

---

##  Troubleshooting

### Error: "El lote no tiene coordenadas GPS configuradas"

**Solución**: Agregar latitud/longitud desde el menú de lotes

```php
$lote->update([
    'latitud' => -25.695139,
    'longitud' => -54.436389,
]);
```

### Error: "No se pudo obtener el pronóstico climático"

**Causas posibles**:
- Sin conexión a internet
- Open-Meteo API no responde
- Coordenadas inválidas

**Verificación**:
```bash
curl "https://api.open-meteo.com/v1/forecast?latitude=-25.695139&longitude=-54.436389&daily=precipitation_sum,cloudcover_mean&timezone=America/Argentina/Buenos_Aires"
```

### Error: "Error interno al analizar clima"

**Solución**: Revisar el archivo de logs
```bash
tail -f storage/logs/laravel.log
```

---

##  Mejoras Futuras

- [ ] Notificaciones automáticas por email/SMS
- [ ] Dashboard web con visualización de pronósticos
- [ ] Machine Learning para predicciones más precisas
- [ ] Integración con múltiples APIs meteorológicos
- [ ] Análisis histórico de precisión de pronósticos
- [ ] Exportación de reportes en PDF

---

##  Referencias

- **Open-Meteo API**: https://open-meteo.com/en/docs
- **Laravel HTTP Client**: https://laravel.com/docs/12.x/http-client
- **Carbon Date Library**: https://carbon.nesbot.com/
- **Trait CalculaCostosLaborales**: `app/Models/Traits/CalculaCostosLaborales.php`

---

##  Ejemplo de Flujo Completo

### Escenario: Lote en Misiones con lluvia pronosticada

```
DÍA 1 (Hoy):
├─ Latitud: -27.367794, Longitud: -55.896108
├─ Consultar Open-Meteo API → Pronóstico de 7 días
├─ Análisis PASO A:
│  ├─ Día 2: Operativo (0.5mm lluvia)
│  ├─ Día 3: INACTIVO (12mm lluvia) ← DÍA CERO
│  ├─ Día 4: INACTIVO (Barro, 75% nubes)
│  ├─ Día 5: Operativo (5mm lluvia, 50% nubes)
│  └─ Total: 2 días perdidos, 1 día operativo previo
│
├─ ¿Hay días previos operativos? SÍ (1 día)
│
├─ Análisis PASO B (Anticipación):
│  ├─ Meta diaria: 50 toneladas
│  ├─ Volumen riesgo: 50 × 2 = 100 toneladas
│  ├─ Aumento necesario: 100 / 1 = 100 toneladas
│  ├─ % Aumento: (100 / 50) × 100 = 200%
│  └─ Decisión:  NO VIABLE (200% > 25%)
│
└─ Acción Recomendada:
   "Anticipación no es viable. Aumento demasiado agresivo.
    Considere preparar estrategia de reacción con 
    mantenimiento preventivo."
```

---

**Última actualización**: 29 de enero de 2026
