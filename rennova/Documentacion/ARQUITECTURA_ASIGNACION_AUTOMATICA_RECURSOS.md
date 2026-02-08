# Arquitectura (Revisada) – Asignación Automática de Recursos basada en Historial Real (en **días**)

Fecha: 31/01/2026  
Stack: Laravel 11 + PostgreSQL (implementación portable)  
Objetivo: Al pasar un Lote a “Explotación”, estimar **duración esperada (en días)** y **recursos** (persona‑día / máquina‑día / insumos), usando **datos históricos reales**.

Decisiones adoptadas:
- **1C (superficie parcial opcional):** cada tarea puede tener `superficie_afectada_ha`; si no se indica, se asume `lote.superficie`.
- **2C (cierre mixto):** la tarea se cierra manualmente, con sugerencia automática por inactividad.
- **3C (insumos por Parte Diario):** el Parte Diario es el centro; los insumos del día se imputan a la tarea del Parte Diario.

Regla operativa adicional (confirmada):
- **1 tarea por día (por lote):** un Parte Diario representa 1 lote + 1 fecha + 1 tarea.

---

## 1) Punto de partida: qué datos reales existen hoy
En el modelo actual del proyecto, el histórico operativo se infiere principalmente desde:

- **lotes**: `especie`, `superficie`, `estado`, ubicación.
- **parte_diarios**: registro por día (fecha) + `es_dia_caido` + costos (mano de obra/insumos/maquinaria) calculados.
- **pivotes** de recursos (por parte diario):
  - `parte_diario_empleado` (empleados que trabajaron ese día)
  - `maquinaria_parte_diarios` (maquinaria usada ese día)
- **cargas**: producción con `peso_neto` y `fecha_carga` (útil solo para tareas que generan salida en toneladas).

Conclusión práctica:
- Es consistente estimar en **días** (persona‑día y máquina‑día), porque el sistema ya registra “quién trabajó qué día”.
- **No** es consistente estimar “horas” sin capturar horas reales.

---

## 2) Problema clave: “la tarea se registra en el lote” pero un lote puede tener múltiples tareas
Registrar `tipo_tarea` directamente en `lotes` se vuelve ambiguo cuando:
- En el mismo lote ocurren **Raleo + Poda**, o **Tala Rasa + Limpieza**, etc.
- Las tareas tienen perfiles de consumo muy diferentes.

### Recomendación de modelado (diseño)
Introduce una entidad explícita **Tarea de Lote** (unidad de análisis y planificación).

**Nota (estado actual de la implementación MVP):** no se creó `lote_tareas`. En su lugar, se resolvió con:
- `parte_diarios.tipo_tarea` (enum en PHP) como “tarea del día”.
- Regla: **1 Parte Diario por (lote, fecha)** ⇒ **1 tarea por día (por lote)**.

**Entidad sugerida:** `lote_tareas`
- `id_lote_tarea`
- `id_lote`
- `tipo_tarea` (catálogo/enum)
- `estado` (planificada/en_ejecucion/cerrada)
- `fecha_inicio` (opcional)
- `fecha_fin` (opcional)
- `superficie_afectada_ha` (crítico para KPI por hectárea)
- `observaciones`

Definiciones operativas recomendadas:
- **Superficie parcial opcional (1C):**
  - Si `superficie_afectada_ha` es NULL, el sistema usa `lote.superficie` para cálculo de KPI y proyección.
  - Si el usuario informa una superficie parcial, esa superficie gobierna KPI y proyección.
  - Regla de validación conceptual: `0 < superficie_afectada_ha ≤ lote.superficie`.
- **Cierre mixto (2C):**
  - Estado principal: `planificada` → `en_ejecucion` → `cerrada`.
  - Cierre manual: acción explícita “Cerrar tarea” que fija `fecha_fin`.
  - Sugerencia automática (no cierre duro): si hay **X** días sin actividad asociada (recomendado: 7–14), marcar “Sugerida para cierre” y pedir confirmación.

Motivo: el KPI histórico debe calcularse por **tarea cerrada**, no por lote completo, porque el lote puede mezclar trabajos.

### Cómo se conecta a lo existente
El diseño mínimo viable para explotar histórico real es permitir vincular cada registro diario a una tarea:
- `parte_diarios` debería poder referenciar `id_lote_tarea`.
- Si hay producción (cargas), también conviene que `cargas` pueda asociarse a `id_lote_tarea` (si la producción corresponde a una tarea de extracción/cosecha).
- Movimientos de stock (insumos) idealmente también referenciables por tarea, si el consumo se quiere estimar por tarea.

Decisión 3C (Parte Diario como centro) + regla “1 tarea por día”:
- `parte_diarios` se asocia a **exactamente 1 tarea** mediante `parte_diarios.id_lote_tarea`.
- Regla conceptual de consistencia:
  - Un (lote, fecha) no debería tener más de un Parte Diario.
  - Esa unicidad es la que hace que “1 tarea por día” sea enforceable y auditable.

Imputación de insumos (3C):
- La “verdad” del consumo del día se registra en el Parte Diario y queda imputada a su `id_lote_tarea`.
- No se requiere prorrateo si se sostiene la regla “1 tarea por Parte Diario”.

Sin esta unión, el historial quedará “mezclado” y la segmentación por tipo de tarea será endeble.

---

## 3) Métricas recomendadas (basadas en días, no horas)
### 3.1 KPIs base (funcionan con tus datos actuales)
Para cada **tarea cerrada** (o aproximación si aún no existe la tabla):

1) **Persona‑día totales**
- Definición: suma por día de la cantidad de empleados asignados al parte diario.
- Ejemplo: si un día trabajaron 5 empleados y otro día 3, total = 8 persona‑día.

2) **Máquina‑día totales**
- Definición: suma por día de cantidad de maquinarias usadas.

3) **Duración en días calendario**
- Definición: cantidad de fechas distintas con actividad (o días entre inicio/fin si se registra) excluyendo o marcando `es_dia_caido`.

4) **Duración en días productivos**
- Definición: cantidad de fechas con actividad donde `es_dia_caido = false`.

5) **KPI normalizado por tamaño del trabajo**
- `persona_dias_por_ha = persona_dias / superficie_afectada_ha`
- `maquina_dias_por_ha = maquina_dias / superficie_afectada_ha`

> Nota: la normalización por hectárea es la forma más directa de “escalado” cuando el input del lote es superficie.

### 3.2 KPIs opcionales (si hay cargas y aplica)
Solo para tareas donde “toneladas” tengan sentido (p.ej. Tala Rasa / Extracción):
- `toneladas_totales` desde `cargas.peso_neto` asociado a la tarea
- `toneladas_por_persona_dia = toneladas / persona_dias`

### 3.3 Insumos
Si se puede asociar consumo a tarea:
- `insumo_costo_por_ha` o `insumo_unidades_por_ha` por tipo de insumo

Definición KPI por tarea (con 3C + 1 tarea por Parte Diario):
- `insumo_costo_por_ha` por tarea = (costo imputado a la tarea en sus Partes Diarios) / `superficie_afectada_ha`.
- `insumo_unidades_por_ha` por tarea = (unidades imputadas a la tarea en sus Partes Diarios) / `superficie_afectada_ha`.

---

## 4) Flujo lógico revisado (alineado a días y múltiples tareas)
### 4.1 Disparador
Evento: cuando `lote.estado` cambia a “Explotación” (p.ej. `en_proceso`).

Implementación:
- Trigger en modelo: [rennova/app/Models/Lote.php](rennova/app/Models/Lote.php)
- Job en cola: [rennova/app/Jobs/GenerateAllocationProposalsForLote.php](rennova/app/Jobs/GenerateAllocationProposalsForLote.php)

Pero: el usuario debe definir/seleccionar **la(s) tareas** a ejecutar.

En el MVP actual, como `parte_diarios.tipo_tarea` es por día, las propuestas se generan por **(lote, tipo_tarea)** usando el histórico y se almacenan en `allocation_proposals`.

Idempotencia:
- El Job es único por lote durante una ventana temporal (evita encolados duplicados).
- Además, por cada `(lote, tipo_tarea)` se evita regenerar si ya existe una propuesta creada en el día.

### 4.1.1 Operación (Colas)

Recomendación: ejecutar el cálculo en cola para no bloquear la request que cambia el estado del lote.

Configuración recomendada:
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`

Worker:
- `php artisan queue:work`
- Para probar un único Job (útil en desarrollo): `php artisan queue:work --once`

### 4.2 Creación/selección de Tareas
En vez de “la tarea vive en el lote”, el lote **tiene** tareas:
- El usuario crea 1..N `lote_tareas` para ese lote.
- Cada tarea tiene `tipo_tarea` y `superficie_afectada_ha`.

Regla 1C:
- Si el usuario no informa `superficie_afectada_ha`, el sistema la completa con `lote.superficie`.

### 4.3 Análisis histórico (núcleo)
Para cada `lote_tarea`:
- Filtrar histórico por:
  - `tipo_tarea`
  - `especie`
  - estado “cerrada”
  - ventana temporal (recomendado: últimos 12–24 meses, configurable)
- Calcular KPIs robustos:
  - `mediana(persona_dias_por_ha)`
  - `mediana(maquina_dias_por_ha)`
  - percentiles P25/P75 para rango

Nota sobre histórico actual:
- Si el dataset actual es de prueba, la arquitectura debe igual contemplar **n_muestras mínimo**, **confidence score** y **fallback** para evitar que el sistema parezca “preciso” cuando no lo es.

### 4.4 Proyección
Con los KPIs:
- `persona_dias_estimados = mediana(persona_dias_por_ha) * superficie_afectada_ha`
- `maquina_dias_estimados = mediana(maquina_dias_por_ha) * superficie_afectada_ha`

Duración (en días) depende de recursos disponibles:
- Si quieres estimar **duración**:
  - `dias_estimados ≈ persona_dias_estimados / tamaño_equipo_propuesto`
- Esto te permite proponer equipo mínimo para cumplir un plazo.

### 4.5 Emparejamiento de recursos
Buscar recursos con historial **en el mismo tipo de tarea**:
- Empleados: ranking por desempeño (p.ej. menor persona_dias_por_ha, o mayor toneladas_por_persona_dia si aplica).
- Maquinarias: ranking por continuidad y costo/día si aplica.

El criterio “superior al promedio” debe basarse en una métrica estable:
- Recomendación: comparar contra **mediana** del grupo, no contra la media.

### 4.6 Salida
Crear `AllocationProposal` por tarea (o por lote con detalle por tarea):
- Equipo sugerido (roles, cantidad)
- Maquinaria sugerida
- Estimación de días
- Insumos estimados
- **explicabilidad**: datos usados (n_muestras, rango P25–P75, outliers excluidos)

---

## 5) Pregunta 1 – Cold Start (sin historia de tipo_tarea o especie)
### Objetivo
Evitar “defaults inventados”. Usar **fallbacks basados en datos reales**, y si no hay datos, forzar revisión humana.

### Estrategia de fallback recomendada
Para estimar `persona_dias_por_ha` (igual para `maquina_dias_por_ha`):

1) Grupo estricto: `(tipo_tarea, especie)` con mínimo N (ej. N≥5)
2) Si no alcanza N: `(tipo_tarea)` sin especie
3) Si no alcanza N: `(especie)` sin tipo_tarea
4) Si no hay casi nada: global

### Qué valor usar en el fallback
- Usar **mediana** como estimación central.
- Para “plan conservador”, usar **P75** (sigue siendo dato real).

### Señales de “baja confianza”
- `n_muestras < 5`
- IQR alto (variabilidad grande)
- datos viejos (sin registros recientes)

Cuando baja confianza:
- Propuesta debe marcar “Requiere validación” y/o solicitar al usuario un rango manual.

---

## 6) Pregunta 2 – Outliers (anomalías históricas)
### Recomendación
Sí: **mediana > media** para rendimiento operativo.

### Cómo manejar outliers sin “manualidad” excesiva
1) Excluir o separar `es_dia_caido = true` (días perdidos distorsionan rendimiento)
2) Detectar outliers por grupo usando IQR:
- Outlier si KPI está fuera de `[Q1 − 1.5·IQR, Q3 + 1.5·IQR]`
3) Guardar conteo de outliers excluidos y mostrarlo en la propuesta.

### Importante
No mezclar KPIs de tareas que no son comparables:
- Si una tarea no tiene producción en toneladas, no uses toneladas como KPI.

---

## 7) Pregunta 3 – Índices MySQL para consulta analítica rápida
Tu consulta típica va a filtrar por:
- `tipo_tarea`
- `especie`
- estado “cerrada”
- rango de fechas

### Si existe `lote_tareas` y un log agregado por tarea
Índices recomendados:
- `lote_tareas(tipo_tarea, estado, fecha_fin)`
- `lote_tareas(id_lote, estado)`
- Si guardas `especie` denormalizada en `lote_tareas`: `lote_tareas(tipo_tarea, especie, estado, fecha_fin)`

### Si calculas desde `parte_diarios` (y los vinculas a tarea)
- `parte_diarios(id_lote_tarea, fecha)`
- `parte_diarios(id_lote, fecha)` (útil también)

Consistencia operativa (1 tarea por día):
- índice único recomendado: `parte_diarios(id_lote, fecha)` para evitar doble carga del mismo día.

Pivotes:
- `parte_diario_empleado(id_parte_diario, id_empleado)`
- `parte_diario_empleado(id_empleado, id_parte_diario)` (si rankeas por empleado)
- `maquinaria_parte_diarios(id_parte_diario, id_maquinaria)` y el inverso si aplica

Producción:
- `cargas(id_lote_tarea, fecha_carga)` (si se vincula a tarea)
- `cargas(id_lote, fecha_carga)` (si no)

Regla general:
- Índices compuestos deben seguir el orden real de filtros: primero columnas de igualdad (tipo/especie/estado), después la columna de rango (fecha).

---

## 8) Pregunta 4 – Implementación en Laravel: Service vs Repository
### Recomendación
- **Repository**: consultas (SQL/Eloquent), optimización, caché, retornos de datasets.
- **Service**: reglas de negocio y estadística (fallback, outliers, selección de recursos, confidence score).
- **Job/Queue**: ejecutar el cálculo en background al cambiar el estado del lote, para no bloquear UI.

### Motivo
El cálculo es “decisional” (estadística + políticas + explicación). Eso pertenece a Service y se testea mejor.

---

## 9) Recomendación de “explicabilidad” (imprescindible en sistemas automáticos)
Cada `AllocationProposal` debería persistir:
- `kpi_usado`: mediana, P25, P75
- `n_muestras`
- `ventana_tiempo`
- `fallback_aplicado` (estricto / por tipo / por especie / global)
- `outliers_excluidos`

Esto evita que el sistema sea una “caja negra” y te permite auditar decisiones.

---

## 10) Decisión de diseño sugerida (para que el proyecto avance sin inventar datos)
1) Aceptar “días” como unidad principal (persona‑día, máquina‑día).
2) Modelar múltiples tareas por lote con `lote_tareas`.
3) Vincular el Parte Diario a tarea(s) (3C) y usar esa relación como base para imputación de insumos.
4) Estimar con **mediana + percentiles** y fallback por jerarquía.

---

## 11) Parámetros que hay que fijar (para cerrar el diseño)
1) Ventana temporal del histórico: recomendado 12–24 meses (configurable).
2) Mínimo de muestra `n_muestras` para grupo estricto: recomendado 5.
3) Umbral de inactividad para sugerir cierre: recomendado 7–14 días.
4) Regla de unicidad del Parte Diario:
  - recomendado: no permitir más de 1 Parte Diario por (lote, fecha), asegurando “1 tarea por día”.

---

Fin del documento.
