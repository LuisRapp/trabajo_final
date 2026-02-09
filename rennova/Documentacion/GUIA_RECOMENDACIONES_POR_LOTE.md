# Guia IU: Recomendaciones automaticas por Lote

Ultima actualizacion: 8 de febrero de 2026.

Este documento describe **cómo se usa la IU** para obtener recomendaciones automáticas (empleados/maquinarias/insumos) en el **contexto del Lote**, basadas en **histórico real**.

> Objetivo: que el usuario trabaje como en la realidad.
>
> 1) En el Lote se **planifican tareas reales** (ej: 5 ha raleo + 5 ha tala rasa).
>
> 2) Luego el sistema genera recomendaciones **por cada tarea planificada**.
>
> 3) En el Parte Diario se registra qué tarea se hizo ese día (1 tarea por lote por día).

---

## 1) Punto de entrada: Pantalla de Lotes

Ruta: `GET /lotes`

### Acciones disponibles por lote (en el listado)

- **Editar**: permite cambiar datos del lote y su estado.
- **Planificar tareas** (icono checklist): abre la pantalla para definir las `lote_tareas`.
- **Recomendaciones** (icono magia): abre las recomendaciones para ese lote.

### Comportamiento clave al pasar el lote a `en_proceso`

Cuando en “Editar” se cambia el estado a **En explotación (`en_proceso`)**:

- Si el lote **NO** tiene tareas activas (`planificada`/`en_ejecucion`):
  - La IU redirige a **Planificar tareas**.
  - Mensaje sugerido: “Antes, planificá las tareas para generar recomendaciones reales (ej: 5 ha raleo + 5 ha tala rasa)”.

- Si el lote **SÍ** tiene tareas activas:
  - La IU redirige directo a **Recomendaciones**.

Esto evita que el motor trabaje “a ciegas” sin tareas reales.

---

## 2) Planificación: Pantalla “Planificar tareas”

Ruta: `GET /lotes/{loteId}/tareas`

### Qué se define aquí

Esta pantalla se usa para planificar el trabajo real del lote:

- **Tipo de tarea**: Tala rasa / Raleo / Poda / Limpieza.
- **Superficie afectada (ha)**: recomendada para dividir el lote (ej: 5 ha + 5 ha).
- **Observaciones**: opcional.

### Reglas / validaciones importantes

- Debe haber **al menos 1 tarea**.
- Si el lote tiene superficie cargada, la suma de superficies planificadas **no puede superar** la superficie del lote.
- Si una fila no tiene superficie, el sistema puede estimar usando el lote como referencia (pero para casos tipo 5/5 conviene completar ambas superficies).

### Botón principal

**Guardar y generar recomendaciones**:

- Guarda las tareas con estado **`planificada`**.
- Dispara la generación de recomendaciones para el lote.
- Redirige automáticamente a la pantalla de **Recomendaciones**.

Nota de seguridad:
- Esta pantalla sólo reemplaza la planificación (`planificada`).
- No toca tareas `en_ejecucion`.
- Evita borrar tareas que ya tengan Partes Diarios asociados.

---

## 3) Consumo: Pantalla “Recomendaciones automáticas”

Ruta: `GET /lotes/{loteId}/recomendaciones`

### Qué muestra

- Recomendaciones **en contexto del lote**.
- Propuestas generadas por el motor (candidatos sugeridos) para:
  - **Empleados**
  - **Maquinarias**
  - **Insumos** (con **cantidad/costo estimado semana 1**)

### Cómo se usa

1. Entrar desde Lotes → botón **Recomendaciones**.
2. Si el lote no tiene propuestas todavía, usar **Generar ahora**.
3. Revisar los candidatos sugeridos y seleccionar (si aplica) quienes se van a asignar.
4. **Confirmar** para dejar la propuesta cerrada (y opcionalmente disparar envío de OC por mail si corresponde).
5. **Aplicar** para sincronizar las selecciones al lote (pivotes de asignación) y dejar el lote listo para operar.

### Por qué es “por tarea”

Si el lote tiene varias tareas (ej: raleo vs tala rasa), el consumo de insumos y el esfuerzo cambian.

La recomendación usa el histórico para estimar **por tipo de tarea**, y si no hay datos suficientes, hace fallbacks conservadores.

---

## 4) Registro operativo: Parte Diario

Ruta: `GET /partes-diarios`

### Relación con las tareas del lote

- El Parte Diario obliga a seleccionar **una tarea del lote** (`id_lote_tarea`).
- Mantiene la regla: **1 tarea por lote por día**.

### “Crear tarea rápida”

Existe un atajo para crear una tarea desde el Parte Diario, pensado para emergencias.

Recomendación de uso:
- Para el flujo normal, **planificar tareas en el Lote** antes de empezar.
- Usar “Crear tarea rápida” solo si se olvidó planificar o aparece una tarea inesperada.

---

## 5) Checklist de flujo recomendado (resumen)

1. Ir a **Lotes**.
2. En el lote, entrar a **Planificar tareas**.
3. Crear tareas (ej: 5 ha raleo + 5 ha tala rasa) y guardar.
4. Revisar **Recomendaciones** (generadas por tarea).
5. Confirmar / Aplicar asignaciones.
6. En **Parte Diario**, seleccionar la tarea realizada ese día y registrar producción/insumos/jornales.

---

## 6) Referencia técnica (para mantenimiento)

Rutas:
- `/lotes` → `lotes.index`
- `/lotes/{loteId}/tareas` → `lotes.tareas`
- `/lotes/{loteId}/recomendaciones` → `lotes.recomendaciones`

Componentes Livewire:
- `Lotes` (ABM + redirección en cambio a `en_proceso`)
- `LotePlanificacionTareas` (gestión de tareas planificadas)
- `AllocationProposals` (vista/acciones de recomendaciones)
- `PartesDiarios` (selección de tarea y registro diario)
