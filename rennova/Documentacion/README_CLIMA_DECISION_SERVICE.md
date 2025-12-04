# Motor de Decisiones Climáticas - ClimaDecisionService

## 📋 Descripción General

Sistema inteligente de 3 fases (Anticipación, Bloqueo, Reacción) que analiza el pronóstico climático de Open-Meteo para tomar decisiones operativas proactivas en lotes forestales.

---

## 🎯 Objetivo

Maximizar productividad y minimizar pérdidas económicas mediante:
1. **Anticipación**: Aumentar producción antes de días de lluvia
2. **Bloqueo**: Identificar períodos de inactividad (lluvia + barro)
3. **Reacción**: Optimizar uso del tiempo durante lluvias (mantenimiento/suspensión)

---

## 🏗️ Arquitectura del Sistema

### Constantes de Configuración

```php
const UMBRAL_LLUVIA = 10; // mm (lluvia moderada que impide operaciones)
const UMBRAL_NUBOSIDAD = 60; // % (indica terreno húmedo/barro)
const MAX_AUMENTO_PRODUCCION = 1.25; // Máximo 25% extra permitido
const DIAS_FORECAST = 7; // Pronóstico a 7 días
```

---

## 🔄 Flujo de Decisión (3 Fases)

### **Método Principal**: `analizarYRecomendar(Lote $lote)`

```
┌─────────────────────────────────────────┐
│  1. Validar coordenadas GPS del lote   │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  2. Obtener pronóstico Open-Meteo API  │
│     - precipitation_sum (mm)            │
│     - cloudcover_mean (%)               │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  PASO A: Mapear Días Inactivos         │
│  - Día lluvia (>10mm) → INACTIVO        │
│  - Día post-lluvia nublado → INACTIVO  │
│  - Calcular: Día Cero, Volumen Riesgo  │
└──────────────┬──────────────────────────┘
               ↓
         ¿Hay días previos
          operativos?
               │
       ┌───────┴───────┐
       │               │
      SÍ              NO
       │               │
       ↓               ↓
┌──────────────┐  ┌──────────────┐
│  PASO B:     │  │  PASO C:     │
│  ANTICIPACIÓN│  │  REACCIÓN    │
└──────────────┘  └──────────────┘
```

---

## 📊 PASO A: Mapeo de Días Inactivos

### Objetivo
Identificar días que no serán operativos por lluvia o barro

### Lógica

```php
foreach (próximos 7 días) {
    if (lluvia >= 10mm) {
        estado = 'INACTIVO';
        razon = "Lluvia: Xmm";
        marcar DÍA CERO (primer día de lluvia);
        huboDiaLluvia = true;
    }
    else if (huboDiaLluvia && nubosidad > 60%) {
        estado = 'INACTIVO';
        razon = "Barro post-lluvia";
    }
    else if (nubosidad <= 60%) {
        huboDiaLluvia = false; // Terreno se secó
    }
}
```

### Output

```php
[
    'dias_detalle' => [
        ['fecha' => '04/12', 'estado' => 'OPERATIVO', 'precipitacion_mm' => 0.2, 'nubosidad' => 45],
        ['fecha' => '05/12', 'estado' => 'INACTIVO', 'razon' => 'Lluvia: 15.3mm'],
        ['fecha' => '06/12', 'estado' => 'INACTIVO', 'razon' => 'Barro (Nubosidad: 75%)'],
        ['fecha' => '07/12', 'estado' => 'OPERATIVO', 'precipitacion_mm' => 0, 'nubosidad' => 30],
    ],
    'total_dias_perdidos' => 2,
    'volumen_riesgo' => 167.8, // ton (2 días × 83.9 ton/día)
    'dia_cero_index' => 1, // Índice del primer día de lluvia
    'dias_operativos_previos' => 1, // Días secos antes del Día Cero
]
```

---

## 🚀 PASO B: Estrategia de Anticipación

### Objetivo
Redistribuir volumen en riesgo entre días operativos previos al Día Cero

### Condición de Activación
```php
if ($dias_operativos_previos > 0) {
    // Ejecutar anticipación
}
```

### Cálculo

```php
$aumento_necesario = $volumen_riesgo / $dias_operativos_previos;
$porcentaje_aumento = ($aumento_necesario / $meta_diaria) * 100;

if ($porcentaje_aumento <= 25%) {
    // ✅ ESCENARIO 1: Aumento VIABLE
    $recomendacion = "Aumentar {$porcentaje}% durante {$dias_previos} días";
    $cobertura = "100%";
}
else {
    // ⚠️ ESCENARIO 2: Aumento al LÍMITE
    $aumento_maximo = $meta_diaria * 0.25;
    $volumen_recuperable = $aumento_maximo * $dias_previos;
    $deficit_residual = $volumen_riesgo - $volumen_recuperable;
    
    $recomendacion = "Aumentar al MÁXIMO (25%). Déficit residual: {$deficit_residual} ton";
    $cobertura = round(($volumen_recuperable / $volumen_riesgo) * 100) . "%";
}
```

### Output de Ejemplo - Escenario 1 (Viable)

```
⚠️ ALERTA DE LLUVIA EN 6 DÍAS (09/12/2025)

📊 ESTRATEGIA DE ANTICIPACIÓN:
   • Aumentar producción un 20% durante los próximos 5 días
   • Meta diaria ajustada: 100.68 toneladas
   • Volumen a recuperar: 83.9 toneladas
   • Días perdidos proyectados: 1

💡 ACCIÓN INMEDIATA:
   Coordinar con capataz para aumentar ritmo de trabajo hoy y mañana.
   Esta anticipación permitirá cubrir el 100% del déficit proyectado.
```

### Output de Ejemplo - Escenario 2 (Límite)

```
🚨 LLUVIA INMINENTE EN 2 DÍAS (05/12/2025)

📊 ESTRATEGIA DE ANTICIPACIÓN (LÍMITE ALCANZADO):
   • Aumentar producción al MÁXIMO: 25%
   • Meta diaria ajustada: 104.88 toneladas
   • Volumen recuperable: 41.95 toneladas (50%)
   • Déficit residual: 41.95 toneladas

⚠️ ADVERTENCIA:
   No será posible cubrir el 100% del déficit proyectado.
   Se recomienda priorizar cargas de mayor valor durante estos días.

💡 ACCIÓN INMEDIATA:
   Movilizar todos los recursos disponibles. Considerar horas extras.
```

---

## 🛠️ PASO C: Estrategia de Reacción

### Objetivo
Optimizar uso de tiempo durante lluvia activa o inminente sin días previos

### Condición de Activación
```php
if ($dias_operativos_previos == 0) {
    // Ya está lloviendo HOY o no hay tiempo de anticipación
}
```

### Decisión Binaria

```
┌────────────────────────────────────────┐
│  Buscar maquinarias con desgaste > 80% │
└─────────────────┬──────────────────────┘
                  │
         ¿Hay maquinarias
       para mantenimiento?
                  │
          ┌───────┴───────┐
          │               │
         SÍ              NO
          │               │
          ↓               ↓
  ┌────────────────┐  ┌──────────────────┐
  │  OPCIÓN 1:     │  │  OPCIÓN 2:       │
  │  MANTENIMIENTO │  │  SUSPENSIÓN      │
  │  PREVENTIVO    │  │  DE JORNADA      │
  └────────────────┘  └──────────────────┘
```

### OPCIÓN 1: Mantenimiento Preventivo

**Criterio**: Maquinarias con `(odometro / horas_proximo_mant) * 100 >= 80%`

**Output**:
```
🌧️ LLUVIA ACTIVA O INMINENTE

📊 ESTRATEGIA DE REACCIÓN:
   Opción recomendada: MANTENIMIENTO PREVENTIVO ADELANTADO

🔧 MAQUINARIAS IDENTIFICADAS (2):
   • Tractor Forestal - Desgaste: 85.3%
     Odómetro: 1280 hs | Próximo mant.: 1500 hs
   • Grúa Cargadora - Desgaste: 92.1%
     Odómetro: 920 hs | Próximo mant.: 1000 hs

💡 ACCIÓN INMEDIATA:
   1. Coordinar con taller para adelantar mantenimientos
   2. Aprovechar parada por lluvia para trabajos preventivos
   3. Reducir riesgo de fallas futuras y tiempos de inactividad

💰 BENEFICIO:
   Convertir tiempo de inactividad en mantenimiento productivo.
   Evitar fallas inesperadas durante días operativos.
```

### OPCIÓN 2: Suspensión de Jornada

**Criterio**: No hay maquinarias que requieran mantenimiento urgente

**Output**:
```
🌧️ LLUVIA ACTIVA O INMINENTE

📊 ESTRATEGIA DE REACCIÓN:
   Opción recomendada: SUSPENSIÓN DE JORNADA

💰 ANÁLISIS DE COSTOS:
   • Costo estructural diario: $12,450.00
   • Días de lluvia proyectados: 2
   • Pérdida total estimada: $24,900.00

🔍 MAQUINARIAS REVISADAS:
   No se detectaron equipos que requieran mantenimiento urgente.

💡 ACCIÓN INMEDIATA:
   1. Suspender jornada para ahorro de costos operativos
   2. Notificar a empleados sobre suspensión por clima
   3. Asegurar equipos y cerrar el lote

⏱️ MONITOREO:
   Revisar pronóstico cada 24hs para retomar operaciones.
```

---

## 🌐 Integración con Open-Meteo API

### Endpoint

```
https://api.open-meteo.com/v1/forecast
```

### Parámetros

```php
[
    'latitude' => $lote->latitud,  // Ej: -25.695139
    'longitude' => $lote->longitud, // Ej: -54.436389
    'daily' => 'precipitation_sum,cloudcover_mean',
    'timezone' => 'America/Argentina/Buenos_Aires',
    'forecast_days' => 7,
]
```

### Respuesta JSON

```json
{
  "daily": {
    "time": ["2025-12-04", "2025-12-05", "2025-12-06", ...],
    "precipitation_sum": [0.2, 15.3, 2.1, ...],
    "cloudcover_mean": [45, 80, 75, ...]
  }
}
```

---

## 💰 Cálculo de Costos

### 1. Meta Diaria Estimada

```php
$promedio_historico = ParteDiario::where('fecha', '>=', Carbon::now()->subDays(30))
    ->whereHas('cargas')
    ->withSum('cargas', 'peso_neto')
    ->get()
    ->avg('cargas_sum_peso_neto');

$meta_diaria = $promedio_historico ?: 50; // Default 50 ton
```

### 2. Costo Estructural Diario

```php
// A) Mano de obra
foreach ($empleados_activos as $empleado) {
    $costo += $empleado->calcularCostoDia($fecha, true, null);
    // true = día caído (jornal fijo, no destajo)
}

// B) Maquinaria alquilada
foreach ($maquinarias_alquiladas as $maquinaria) {
    $toneladas = min($maquinaria->toneladas_acumuladas ?? 10, 10);
    $costo += $maquinaria->tipoMaquinaria->precio_alquiler_destajo * $toneladas;
}
```

---

## 🖥️ Uso del Sistema

### Comando Artisan

```bash
# Analizar todos los lotes activos con coordenadas
php artisan clima:decisiones

# Analizar lote específico
php artisan clima:decisiones --lote=5
```

### Output del Comando

```
🌦️  Sistema de Decisiones Climáticas Inteligentes
═══════════════════════════════════════════════════

📍 Analizando 3 lote(s)...

🌲 Lote Rapp - Las Tunas

   📋 ANTICIPACION   MEDIA 

   ⚠️ ALERTA DE LLUVIA EN 6 DÍAS (09/12/2025)
   ...
   [Recomendación completa]

───────────────────────────────────────────────────

═══════════════════════════════════════════════════
📊 RESUMEN DE ANÁLISIS
═══════════════════════════════════════════════════
   Total de lotes analizados: 3
   Estrategias de Anticipación: 2
   Estrategias de Reacción: 1

💡 ACCIÓN REQUERIDA: Revisar alertas de anticipación y ajustar producción HOY.
🚨 ATENCIÓN: Hay lotes con lluvia activa/inminente. Ejecutar estrategias de reacción.
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
}
```

---

## 📁 Estructura de Archivos

```
app/
├── Services/
│   └── ClimaDecisionService.php  ← Motor principal
│
├── Console/Commands/
│   └── AnalizarDecisionesClimaticas.php  ← Comando Artisan
│
└── Models/
    ├── Lote.php (requiere: latitud, longitud)
    ├── ParteDiario.php
    ├── Empleado.php
    └── Maquinaria.php
```

---

## 🧪 Scripts de Prueba

### test_clima_decision_service.php

Prueba exhaustiva con 3 escenarios:
1. Buenos Aires (clima estable)
2. Puerto Iguazú (zona húmeda)
3. Posadas (punto intermedio)

```bash
php test_clima_decision_service.php
```

**Output esperado**:
- Tiempo promedio: ~1200ms por lote
- Estrategias detectadas según pronóstico real
- Tablas con pronóstico detallado de 7 días

---

## 🔍 Troubleshooting

### Error: "El lote no tiene coordenadas GPS configuradas"

**Solución**: Agregar latitud/longitud desde menú Lotes

```php
$lote->update([
    'latitud' => -25.695139,
    'longitud' => -54.436389,
]);
```

### Error: "No se pudo obtener el pronóstico climático"

**Causas**:
- Sin conexión a internet
- Open-Meteo API no responde
- Coordenadas inválidas

**Verificación**:
```bash
curl "https://api.open-meteo.com/v1/forecast?latitude=-25.695139&longitude=-54.436389&daily=precipitation_sum,cloudcover_mean&timezone=America/Argentina/Buenos_Aires"
```

### Meta diaria = 50 ton (default)

**Causa**: No hay histórico de partes diarios en últimos 30 días

**Solución**: Crear partes diarios con cargas para entrenar el cálculo de meta

---

## 📈 Mejoras Futuras

### 1. Notificaciones Automáticas

```php
// En analizarYRecomendar()
if ($resultado['nivel_urgencia'] === 'ALTA') {
    Notification::send($capataces, new AlertaClimatica($resultado));
}
```

### 2. Dashboard Web

- Vista Livewire con pronóstico visual
- Gráficos de precipitación (Chart.js)
- Botón "Aplicar recomendación" que ajuste metas automáticamente

### 3. Machine Learning

- Analizar efectividad de recomendaciones históricas
- Ajustar umbral de lluvia según tipo de terreno del lote
- Predecir meta diaria con regresión basada en estacionalidad

### 4. Integración con ParteDiario

```php
// Al crear parte diario, mostrar alerta si hay recomendación activa
$recomendacion = ClimaDecisionService::getRecomendacionVigente($lote);
if ($recomendacion) {
    alert($recomendacion['recomendacion']);
}
```

### 5. Análisis Histórico

- Comparar días caídos reales vs pronosticados
- Calcular precisión de Open-Meteo API
- Ajustar umbrales dinámicamente

---

## ✅ Checklist de Implementación

- [x] Servicio ClimaDecisionService completo
- [x] Comando `clima:decisiones`
- [x] Integración Open-Meteo API (precipitation_sum + cloudcover_mean)
- [x] PASO A: Mapeo de días inactivos
- [x] PASO B: Estrategia de anticipación (2 escenarios)
- [x] PASO C: Estrategia de reacción (2 opciones)
- [x] Cálculo de meta diaria histórica
- [x] Cálculo de costo estructural diario
- [x] Scripts de prueba exhaustivos
- [x] Documentación completa
- [ ] Notificaciones automáticas (futuro)
- [ ] Dashboard web (futuro)
- [ ] Machine learning (futuro)

---

## 📚 Referencias

- **Open-Meteo API**: https://open-meteo.com/en/docs
- **Laravel HTTP Client**: https://laravel.com/docs/12.x/http-client
- **Trait CalculaCostosLaborales**: `app/Models/Traits/CalculaCostosLaborales.php`
- **Sistema de Análisis Climático**: `SISTEMA_ANALISIS_CLIMATICO.md`

---

## 🤝 Ejemplo de Flujo Completo

```
LOTE: Rapp - Las Tunas
PRONÓSTICO:
  04/12: 0.2mm, 45% nubosidad → OPERATIVO
  05/12: 0.5mm, 50% nubosidad → OPERATIVO
  06/12: 2.1mm, 55% nubosidad → OPERATIVO
  07/12: 15.3mm, 80% nubosidad → INACTIVO (DÍA CERO)
  08/12: 3.2mm, 75% nubosidad → INACTIVO (BARRO)
  09/12: 0mm, 40% nubosidad → OPERATIVO (Terreno seco)
  10/12: 0mm, 30% nubosidad → OPERATIVO

ANÁLISIS PASO A:
  - Día Cero: 07/12 (índice 3)
  - Días inactivos: 2 (07/12 y 08/12)
  - Días previos operativos: 3 (04, 05, 06/12)
  - Meta diaria: 83.9 ton
  - Volumen en riesgo: 167.8 ton (2 × 83.9)

DECISIÓN PASO B (ANTICIPACIÓN):
  - Aumento necesario: 167.8 / 3 = 55.93 ton/día
  - Porcentaje: (55.93 / 83.9) × 100 = 66.7%
  - 66.7% > 25% → LÍMITE ALCANZADO
  
  - Aumento máximo: 83.9 × 0.25 = 20.98 ton
  - Volumen recuperable: 20.98 × 3 = 62.94 ton
  - Déficit residual: 167.8 - 62.94 = 104.86 ton
  - Cobertura: 37.5%

RECOMENDACIÓN:
  "Aumentar producción al MÁXIMO (25%) durante 3 días.
   Meta ajustada: 104.88 ton/día
   Cobertura: 37.5% del déficit
   Déficit residual: 104.86 ton
   
   ADVERTENCIA: No se podrá cubrir el 100%.
   Priorizar cargas de mayor valor."
```

---

**Sistema listo para producción** ✅
