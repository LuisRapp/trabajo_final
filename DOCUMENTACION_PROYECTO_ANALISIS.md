# Gestión Rennova — Documentación de Análisis (Fuente de Verdad)
## Fase II - Análisis del Modelo Conceptual y Alineamiento con Código
- Última actualización: 2026-05-03
- Propósito: reconstruir el documento integrado con la implementación real (código) y documentar desalineamientos frente al diseño GRASP/ requisitos.

## Índice
- Modelo Conceptual
- Comportamiento del Sistema
- Especificaciones Detalladas (Casos de Uso críticos)
    - Registrar Parte Diario (UC-61)
    - Cerrar Mantenimiento
    - Liquidar Pagos
    - Gestionar Propuestas de Asignación
    - Diagramas (Conceptual y Secuencias relevantes)
    - Glosario y Desalineamientos GRASP vs Implementación

---

## Modelo Conceptual

Mantener la estructura conceptual validada originalmente: entidades principales (Lote, Carga, ParteDiario, Empleado, Maquinaria, Mantenimiento, MovimientoStock, LoteInventario, AllocationProposal, etc.).

Descripción sintética de entidades clave (resumen):
- Lote: unidad productiva con atributos de ubicación, superficie, especie, estado.
- ParteDiario: registro diario con toneladas, empleados, maquinarias y consumos.
- MovimientoStock: entradas y salidas de insumos; valorización FIFO delegada a BD.
- LoteInventario: entradas FIFO por insumo/proveedor.
- Empleado: modelo con responsabilidad de cálculo de pagos (Empleado::calcularPagoRango).
- Mantenimiento: orden con ciclo (programado, en curso, completado) y cierre por MantenimientoService::completarMantenimiento().
- AllocationProposal: propuesta generada por AutomaticAllocationService::proposeForLotAndTask().

El diagrama conceptual validado se mantiene sin cambios estructurales (véase referencia a diagramas del repositorio).

---

## Comportamiento del Sistema

Esta sección resume flujos principales con foco en cómo el sistema se comporta según la implementación actual.

### Notas generales de implementación
- Muchas responsabilidades definidas en el diseño GRASP están reasignadas a servicios y modelos concretos en pp/Services/* y pp/Models/*.
- Hay decisiones operativas que son «recomendativas» y asíncronas (ej. validación climática) en lugar de bloqueantes.
- Valoraciones FIFO se realizan en la base de datos mediante función SQL (calcular_costo_fifo()), lo que hace al cálculo opaco para la capa aplicativa.

---

## Especificaciones Detalladas (Casos de Uso críticos)

Las siguientes especificaciones fueron actualizadas para reflejar la implementación real y los puntos de riesgo identificados.

### UC-61 — Registrar Parte Diario

Resumen: registrar la jornada productiva de un lote, con empleados, maquinarias, cargas y consumos.

Implementación principal:
- UI / controlador: pp/Http/Livewire/PartesDiarios.php.
- Servicio de decisiones climáticas: pp/Services/ClimaDecisionService.php.
- Repositorio de movimientos de stock: RepositorioMovimientoStock (capa de persistencia que invoca la función BD calcular_costo_fifo()).

Reglas y umbrales implementados en ClimaDecisionService (efectivos en código):
- UMBRAL_LLUVIA = 10 mm
- UMBRAL_NUBOSIDAD = 60 %
- Viento crítico: iento > 15 km/h
- ET0 > 4 mm (impacto en productividad)

Comportamiento clave:
- La validación climática es asíncrona y recomendativa. ClimaDecisionService::analizarYRecomendar() puede ejecutarse en solicitudes interactivas o por tareas programadas (clima:decisiones). Sus recomendaciones no siempre bloquean el registro; el usuario puede sobrepasar la advertencia cuando el flujo lo permite.
- El cálculo y valorización de consumos FIFO se realiza llamando la función SQL calcular_costo_fifo() desde el repositorio de movimientos (RepositorioMovimientoStock). NO se utiliza una clase CalculadorInventarioFIFO en PHP.
- En caso de insuficiencia de stock, el sistema puede registrar movimientos que provoquen stock negativo y deja constancia (advertencias), pero no impide el registro del parte.

Diagrama de secuencia (GRASP REAL) — participantes: ClimaDecisionService, RepositorioMovimientoStock, función FIFO en BD:

`mermaid
sequenceDiagram
        participant Usuario
        participant UI as PartesDiarios (Livewire)
        participant ParteService as ParteDiarioService
        participant Clima as ClimaDecisionService
        participant StockRepo as RepositorioMovimientoStock
        participant FIFO as BD: calcular_costo_fifo()
        participant Auditoria

        Usuario->>UI: enviarDatosParte(lote, fecha, cargas, consumos, override?)
        UI->>ParteService: validarYCrearParte(datos)
        ParteService->>Clima: analizarYRecomendar(lote, fecha)
        Clima-->>ParteService: recomendacion (ok|alerta|bloqueo_recomendado)
        alt recomendacion alerta y no override
                ParteService-->>UI: mostrarAlertaClimatica()
        else (override o recomendacion ok)
                ParteService->>StockRepo: registrarMovimientos(consumos)
                StockRepo->>FIFO: calcular_costo_fifo(insumo_id, cantidad)
                FIFO-->>StockRepo: costo_unitario, movimientos_fifo
                StockRepo-->>ParteService: movimientos_registrados, costo_total
                ParteService->>Auditoria: registrarAccion(usuario, 'registrarParteDiario', idParte)
                ParteService-->>UI: confirmacion(idParte, costo_total)
        end
`

Excepciones y comportamientos especiales:
- Si el lote está inactivo/cerrado → error y bloqueo.
- Si faltan asignaciones, se registra el parte con advertencia.
- Stock insuficiente → se puede registrar salida que deje stock negativo (se documenta en auditoría y se notifica).

Resultados esperados:
- Registro de parte_diario, signacion, maquinaria_parte_diario, movimiento_stock (salidas) y auditoría.

Referencias de código:
- pp/Http/Livewire/PartesDiarios.php
- pp/Services/ClimaDecisionService.php
- Repositorio/DAO que invoca calcular_costo_fifo() (ubicado junto a MovimientoStock en la capa de persistencia).

---

### Cerrar Mantenimiento

Resumen: cerrar una orden de mantenimiento y registrar costos reales.

Implementación principal:
- Servicio: pp/Services/MantenimientoService.php
- Método clave: MantenimientoService::completarMantenimiento() (cierra orden, registra costos y movimientos de insumo).

Comportamiento e invariantes:
- completarMantenimiento() registra salidas de insumos en MovimientoStock y actualiza histórico de costos para la maquinaria.
- El flujo permite registrar movimientos aunque resulten en stock negativo; es una política de la implementación actual (permite cierre aun con stock insuficiente) y queda reflejado en auditoría.

Diagrama de secuencia (resumen):

`mermaid
sequenceDiagram
        participant Tecnico
        participant MantenimientoSvc as MantenimientoService
        participant MantenimientoRepo
        participant MovimientoStockRepo
        participant Auditoria

        Tecnico->>MantenimientoSvc: completarMantenimiento(idMant, datosCierre)
        MantenimientoSvc->>MantenimientoRepo: obtener(idMant)
        MantenimientoRepo-->>MantenimientoSvc: datos
        MantenimientoSvc->>MovimientoStockRepo: registrarSalidas(insumosConsumidos)
        MovimientoStockRepo-->>MantenimientoSvc: movimientos (posible stock negativo)
        MantenimientoSvc->>MantenimientoRepo: actualizarEstado(idMant, 'completado', costos)
        MantenimientoSvc->>Auditoria: registrarAccion(usuario, 'cerrarMantenimiento', idMant)
        MantenimientoSvc-->>Tecnico: confirmacion(costo_total)
`

Notas de riesgo:
- Política de permitir stock negativo simplifica cierres operativos, pero introduce deuda de inventario que requiere controles posteriores.

Referencias de código:
- pp/Services/MantenimientoService.php

---

### Liquidar Pagos

Resumen: proceso de registrar el pago de recibos ya generados. La lógica de cálculo NO reside en un servicio ServicioLiquidacion separado; el experto es el modelo Empleado.

Implementación principal:
- Modelo: pp/Models/Empleado.php — Empleado::calcularPagoRango(, )
- UI: pp/Http/Livewire/LiquidacionPagos.php (interfaz para revisar y confirmar liquidación)

Responsabilidades y fórmula (implementada):
- Empleado::calcularPagoRango() agrupa partes diarios/asignaciones del empleado en el rango y calcula subtotales.
- Fórmula explicada (simplificada a lo implementado):
    - toneladas_por_empleado = (peso_neto_total_del_lote ÷ empleados_asignados) — cuando la tonelada se reparte entre empleados asignados
    - pago_por_empleado = toneladas_por_empleado × valor_por_ton_del_rol
    - En términos operativos: peso_neto ÷ empleados_asignados = toneladas/empleado; toneladas/empleado × valor_rol = monto
- Se consideran adelantos y jornales donde aplica; el método devuelve totales y desgloses.

Comportamiento:
- La UI LiquidacionPagos llama a Empleado::calcularPagoRango() para presentar el detalle y luego confirma la liquidación (actualiza Recibo a liquidado).
- El modelo Empleado es el experto del cálculo (encapsula reglas de negocio y acceso a HistoricoRolLaboral).

Referencias de código:
- pp/Models/Empleado.php::calcularPagoRango()
- pp/Http/Livewire/LiquidacionPagos.php

Excepciones:
- Si el recibo ya está liquidado → rechazo.
- Si no hay partes diarios en rango → no hay calculo y se informa.

---

### Gestionar Propuestas de Asignación

Resumen: generación de propuestas automáticas de asignación de empleados/maquinaria para lote/tarea.

Implementación principal:
- Servicio: pp/Services/AutomaticAllocationService.php.
- Método central: AutomaticAllocationService::proposeForLotAndTask().

Parámetros y política implementada:
- minSamples = 5 (mínimo de registros históricos requeridos para confiar en la métrica)
- since = 24 meses (histórico a considerar)
- Si no se alcanza minSamples, se aplican fallback rates (valores por defecto plausibles) para generar la propuesta.

Validaciones y responsabilidades reales:
- NO existe una clase formal ValidadorAsignaciones en el código actual.
- La comprobación de solapamiento/ disponibilidad se realiza por métodos del servicio: usyEmployeeIds() y usyMaquinariaIds() que filtran recursos ocupados por rango de fechas.
- Además, existen validaciones parciales en Livewire/controllers al aceptar la propuesta; no hay un validador centralizado responsable único.

Riesgo identificado:
- Falta de una regla centralizada y explícita de no-solapamiento (posibles brechas en escenarios complejos de traslapes entre tareas/turnos).

Diagrama de secuencia (resumen):

`mermaid
sequenceDiagram
        participant Planificador
        participant AllocationSvc as AutomaticAllocationService
        participant EmpleadoRepo
        participant MaquinariaRepo
        participant Auditoria

        Planificador->>AllocationSvc: proposeForLotAndTask(idLote, idTarea, params)
        AllocationSvc->>EmpleadoRepo: obtenerHistorico(empleados, since=24m)
        EmpleadoRepo-->>AllocationSvc: historicoEmpleados
        AllocationSvc->>MaquinariaRepo: obtenerHistorico(maquinas, since=24m)
        MaquinariaRepo-->>AllocationSvc: historicoMaquinas
        AllocationSvc->>AllocationSvc: calcularCandidatos(minSamples=5, fallbackRates)
        AllocationSvc->>AllocationSvc: busyEmployeeIds(fechaDesde, fechaHasta)
        AllocationSvc->>AllocationSvc: busyMaquinariaIds(fechaDesde, fechaHasta)
        AllocationSvc-->>Planificador: propuesta (candidatos, metrics)
        AllocationSvc->>Auditoria: registrarAccion(usuario, 'generarPropuesta', idProposal)
`

Referencias de código:
- pp/Services/AutomaticAllocationService.php (métodos: proposeForLotAndTask(), populateProposalCandidates(), usyEmployeeIds(), usyMaquinariaIds())

---

## Diagramas

- Diagrama conceptual: se mantiene el diagrama validado en el repositorio (no se modifica aquí). Véase DIAGRAMAS_SECUENCE_DISENO.md para diagramas GRASP/ diseño.
- Diagramas de secuencia genéricos y diagramas de estados: se mantienen los aprobados; los cambios implementacionales se documentan en las secciones anteriores y en el glosario (desalineamientos).

---

## Glosario y Desalineamientos GRASP vs Implementación

Nota: esta sección documenta, para cada concepto, lo que el diseño GRASP esperaba y lo que efectivamente implementa el código — debe usarse como fuente de verdad durante mantenimientos y refactorings.

- ValidadorParteClimatico
    - Diseño esperado (GRASP): clase ValidadorParteClimatico encargada de validar/clasificar partes por clima y bloquear registros cuando sea necesario.
    - Implementación actual: ClimaDecisionService::analizarYRecomendar() (servicio asíncrono/recomendativo). Ejecutable también de forma sincrónica para advertencias, pero por defecto funciona como tarea programada (clima:decisiones). No siempre bloquea.

- CalculadorInventarioFIFO
    - Diseño esperado (GRASP): clase central CalculadorInventarioFIFO en capa de aplicación, transparente y testeable.
    - Implementación actual: función SQL calcular_costo_fifo() invocada desde el repositorio de movimientos (RepositorioMovimientoStock) y la valorización se realiza en BD. El cálculo es eficiente pero opaco en la capa aplicación y más difícil de auditar/ probar a nivel unitario.

- ServicioLiquidacion + CalculadorJornalProductividad
    - Diseño esperado (GRASP): servicios separados que encapsulan la lógica de liquidación y cálculo de jornales/productividad.
    - Implementación actual: el modelo Empleado actúa como experto: Empleado::calcularPagoRango() realiza la agregación y cálculo; la UI LiquidacionPagos visualiza/coordina. No existe un ServicioLiquidacion dedicado.

- ValidadorAsignaciones
    - Diseño esperado (GRASP): ValidadorAsignaciones central que previene solapamientos y valida reglas complejas de asignación.
    - Implementación actual: validación distribuida en AutomaticAllocationService mediante usyEmployeeIds() y usyMaquinariaIds(), y validaciones en Livewire/controllers. No hay una clase formal única; la lógica está fragmentada.

Impacto y recomendaciones (resumido):
- La asimetría entre diseño y código introduce riesgos operativos: validaciones no centralizadas, cálculos opacos en BD y reglas de negocio repartidas. Para mitigar:
    1. Considerar documentar y exponer la lógica de calcular_costo_fifo() (procedimiento/función BD) o encapsularla con pruebas de integración que la verifiquen.
    2. Evaluar la creación de un ValidadorAsignaciones consolidado o una fachada en AutomaticAllocationService que normalice reglas de solapamiento.
    3. Revisar la política de stock negativo (reglas de negocio + alertas + conciliaciones periódicas).

---

## Referencias cruzadas (código y diagramas)
- pp/Services/ClimaDecisionService.php — validación climática y heurísticas.
- pp/Services/MantenimientoService.php — cierre/completado de mantenimientos.
- pp/Services/AutomaticAllocationService.php — generación de propuestas de asignación.
- pp/Models/Empleado.php::calcularPagoRango() — cálculo de liquidaciones por empleado.
- pp/Http/Livewire/LiquidacionPagos.php — UI para liquidaciones.
- Repositorio: llamada a calcular_costo_fifo() (función SQL, ver esquema y scripts DB).
- Diagramas: DIAGRAMAS_SECUENCIA_DISENO.md (diagramas GRASP originales mantenidos para trazabilidad).

---

Si quieres, puedo:
- ejecutar una búsqueda en el código para extraer firmas exactas y líneas donde están definidos los métodos referenciados, y añadir enlaces de archivo con líneas concretas a este documento;
- preparar pruebas de integración pequeñas que validen calcular_costo_fifo() y Empleado::calcularPagoRango().

Fin del documento.
