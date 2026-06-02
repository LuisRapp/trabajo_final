# Diagramas de Secuencia de Diseno - 10 Casos de Uso Criticos

Arquitecto de Software | Principios GRASP (Larman) | Proceso Unificado (UP)
---

## UC-61: Cargar Parte Diario (Operacion Critica de Produccion)


```mermaid
sequenceDiagram
actor usuario
participant ui as CargarParteDiarioUI
participant controlador as CargarParteDiarioControlador
participant validador as ValidadorParteClimatico
participant servicio as ServicioParteDiario
participant repoLote as RepositorioLote
participant repoParte as RepositorioParteDiario
participant repoStock as RepositorioMovimientoStock
participant inventario as CalculadorInventarioFIFO
participant auditoria as ServicioAuditoria

usuario->>ui: navegaCargarParteDiario()
ui->>controlador: mostrarFormulario()
controlador->>repoLote: obtenerLotesActivos()
repoLote-->>controlador: lotes[]
controlador-->>ui: formularioVacio(lotes)
usuario->>ui: completaDatos()
ui->>controlador: procesarParteDiario(datos)
controlador->>repoLote: obtenerLote(loteId)
repoLote-->>controlador: lote
controlador->>validador: validarDiaOperativoClimatico(lote, fecha)
validador-->>controlador: resultado
alt dia no operativo y no hay override
  controlador-->>ui: mostrarAlertaClimatica()
else autorizado
  controlador->>servicio: calcularCostosDelParte(lote, cargas, consumos)
  servicio->>inventario: aplicarFIFO(consumos)
  inventario-->>servicio: movimientosStock, costoTotal
  controlador->>repoParte: crearParteDiario(lote, fecha, cargas, consumos, movimientosStock)
  repoParte->>repoStock: registrarMovimientosStock(movimientosStock)
  repoStock-->>repoParte: ok
  repoParte-->>controlador: parteCreado
  controlador->>auditoria: registrarCambio("ParteDiario", "created")
  auditoria-->>controlador: ok
  controlador-->>ui: parteDiarioGuardado()
end
```

**Nota post-diagrama:**
- Forzado GRASP: `ValidadorParteClimatico` e `CalculadorInventarioFIFO` concentran conocimiento experto y evitan un controlador anemico.
- Concesion pragmatica: `ServicioParteDiario` orquesta los pasos mas largos para no fragmentar una operacion que tiene varias variaciones reales.

---

## UC-59: Liquidar Pagos (Complejidad de Reglas Financieras)


```mermaid
sequenceDiagram
actor usuario
participant ui as LiquidarPagosUI
participant controlador as LiquidarPagosControlador
participant repoEmpleado as RepositorioEmpleado
participant calculador as CalculadorJornalProductividad
participant repoAdelanto as RepositorioAdelanto
participant servicio as ServicioLiquidacion
participant repoLiquidacion as RepositorioLiquidacion
participant auditoria as ServicioAuditoria

usuario->>ui: navegaLiquidarPagos()
ui->>controlador: mostrarFormulario()
controlador->>repoEmpleado: obtenerEmpleadosActivos()
repoEmpleado-->>controlador: empleados[]
usuario->>ui: seleccionaEmpleadoYRango()
ui->>controlador: calcularLiquidacion(empleadoId, rango)
controlador->>repoEmpleado: obtenerEmpleado(empleadoId)
repoEmpleado-->>controlador: empleado
controlador->>calculador: calcularJornalProductividad(empleado, rango)
calculador-->>controlador: bruto, detalle
controlador->>repoAdelanto: obtenerAdelantosPendientes(empleado, rango)
repoAdelanto-->>controlador: adelantos[]
controlador->>servicio: aplicarDescuentos(bruto, adelantos)
servicio-->>controlador: descuentos, neto
controlador->>repoLiquidacion: crearLiquidacion(empleado, rango, bruto, descuentos, neto)
repoLiquidacion-->>controlador: liquidacion
controlador->>auditoria: registrarCambio("Liquidacion", "created")
auditoria-->>controlador: ok
controlador-->>ui: liquidacionCalculada(liquidacion)
```

**Nota post-diagrama:**
- Forzado GRASP: `CalculadorJornalProductividad` es el experto en formulas; el controlador solo coordina.
- Concesion pragmatica: `ServicioLiquidacion` concentra descuentos y adelantos porque es una zona de cambio frecuente.

---

## UC-65: Planificacion de Tareas por Lote (Soporte Operativo)



```mermaid
sequenceDiagram
actor usuario
participant ui as PlanificacionTareasUI
participant controlador as PlanificacionTareasControlador
participant repoLote as RepositorioLote
participant repoTarea as RepositorioTareaPlanificada
participant validador as ValidadorPlanificacion
participant recomendaciones as ServicioRecomendaciones
participant auditoria as ServicioAuditoria

usuario->>ui: navegaPlanificacionLote(loteId)
ui->>controlador: cargarPlanificacionActual(loteId)
controlador->>repoLote: obtenerLote(loteId)
repoLote-->>controlador: lote
controlador->>repoTarea: obtenerTareasPlanificadas(lote)
repoTarea-->>controlador: tareas[]
usuario->>ui: editaTareas()
ui->>controlador: guardarPlanificacion(loteId, tareasNuevas)
controlador->>validador: validarPlanificacion(lote, tareasNuevas)
alt validacion fallida
  validador-->>controlador: errores
  controlador-->>ui: mostrarErrores()
else valida
  validador-->>controlador: ok
  controlador->>repoTarea: reemplazarTareas(lote, tareasNuevas)
  repoTarea-->>controlador: guardado
  controlador->>recomendaciones: generarRecomendacionesAutomaticas(lote)
  recomendaciones-->>controlador: propuestas
  controlador->>auditoria: registrarCambio("TareaPlanificada", "updated")
  auditoria-->>controlador: ok
  controlador-->>ui: planificacionGuardada(propuestas)
end
```

**Nota post-diagrama:**
- Forzado GRASP: `Lote` y `ValidadorPlanificacion` absorben reglas de superficie y consistencia.
- Concesion pragmatica: las recomendaciones se disparan al final para no bloquear la confirmacion del usuario.

---

## UC-63: Programar Mantenimiento (Evento Critico de Recurso)
```mermaid
sequenceDiagram
actor usuario
participant ui as ProgramarMantenimientoUI
participant controlador as ProgramarMantenimientoControlador
participant repoMaquinaria as RepositorioMaquinaria
participant repoTipo as RepositorioTipoMantenimiento
participant repoOrden as RepositorioOrdenMantenimiento
participant notificador as NotificadorMantenimiento
participant auditoria as ServicioAuditoria

usuario->>ui: navegaProgramarMantenimiento()
ui->>controlador: mostrarFormulario()
controlador->>repoMaquinaria: obtenerMaquinariasActivas()
repoMaquinaria-->>controlador: maquinarias[]
controlador->>repoTipo: obtenerTiposMantenimiento()
repoTipo-->>controlador: tipos[]
usuario->>ui: completaDatos()
ui->>controlador: crearOrdenMantenimiento(datos)
controlador->>repoOrden: crearOrdenMantenimiento(datos)
repoOrden-->>controlador: orden
controlador->>repoMaquinaria: actualizarEstado(maquinariaId, "en_mantenimiento")
repoMaquinaria-->>controlador: ok
controlador->>auditoria: registrarCambio("OrdenMantenimiento", "created")
auditoria-->>controlador: ok
controlador->>notificador: notificarProgramacion(orden)
notificador-->>controlador: encolado
controlador-->>ui: ordenCreada(orden)
```

**Nota post-diagrama:**
- Forzado GRASP: el controlador crea la orden raiz y mantiene el flujo de negocio simple y localizable.
- Concesion pragmatica: notificacion asincrona con un servicio simple, sin introducir infraestructura adicional.

---

## UC-62: Cerrar Orden de Mantenimiento (Cierre Complejo con FIFO)



```mermaid
sequenceDiagram
actor usuario
participant ui as CerrarMantenimientoUI
participant controlador as CerrarMantenimientoControlador
participant repoOrden as RepositorioOrdenMantenimiento
participant inventario as CalculadorInventarioFIFO
participant repoStock as RepositorioMovimientoStock
participant repoMaquinaria as RepositorioMaquinaria
participant auditoria as ServicioAuditoria

usuario->>ui: navegaCerrarOrden(ordenId)
ui->>controlador: cargarOrden(ordenId)
controlador->>repoOrden: obtenerOrden(ordenId)
repoOrden-->>controlador: orden
usuario->>ui: completaCierre(fechaFin, costoAdicional, insumos)
ui->>controlador: cerrarOrden(ordenId, fechaFin, costoAdicional, insumos)
controlador->>inventario: aplicarFIFO(insumos)
inventario-->>controlador: movimientos, costoFIFO
controlador->>repoStock: registrarMovimientosStock(movimientos)
repoStock-->>controlador: ok
controlador->>repoOrden: actualizarOrdenCierre(orden, fechaFin, costoFIFO, costoAdicional)
repoOrden-->>controlador: ordenCerrada
controlador->>repoMaquinaria: actualizarEstado(orden.maquinariaId, "operativa")
repoMaquinaria-->>controlador: ok
controlador->>auditoria: registrarCambio("OrdenMantenimiento", "updated")
auditoria-->>controlador: ok
controlador-->>ui: ordenCerrada()
```

**Nota post-diagrama:**
- Forzado GRASP: `CalculadorInventarioFIFO` es el experto real en consumo y costo; la orden valida coherencia temporal.
- Concesion pragmatica: la operacion se mantiene en dos bloques claros para facilitar rollback y auditoria.

---

## UC-13: Alta Venta (Facturacion Critica)


```mermaid
sequenceDiagram
actor usuario
participant ui as RegistrarVentaUI
participant controlador as RegistrarVentaControlador
participant repoCliente as RepositorioCliente
participant repoCarga as RepositorioCarga
participant validador as ValidadorVenta
participant repoVenta as RepositorioVenta
participant auditoria as ServicioAuditoria

usuario->>ui: navegaAltaVenta()
ui->>controlador: mostrarFormulario()
controlador->>repoCliente: obtenerClientesActivos()
repoCliente-->>controlador: clientes[]
usuario->>ui: seleccionaClienteYRango()
ui->>controlador: buscarCargasPendientes(clienteId, rango)
controlador->>repoCarga: obtenerCargasPendientes(clienteId, rango)
repoCarga-->>controlador: cargas[]
controlador->>validador: validarCargasParaFacturacion(cargas)
validador-->>controlador: total
controlador-->>ui: mostrarCargasPendientes(total)
usuario->>ui: confirmaRegistrarVenta()
ui->>controlador: registrarVenta(clienteId, cargas, observaciones)
controlador->>repoVenta: crearVenta(clienteId, cargas, observaciones)
repoVenta-->>controlador: venta
controlador->>repoCarga: actualizarEstadoCargas(cargas, "facturada")
repoCarga-->>controlador: ok
controlador->>auditoria: registrarCambio("Venta", "created")
auditoria-->>controlador: ok
controlador-->>ui: ventaGuardada(venta)
```

**Nota post-diagrama:**
- Forzado GRASP: `Venta` y `ValidadorVenta` concentran las reglas de facturacion y evitan un controlador anemico.
- Concesion pragmatica: no se introduce una fabrica abstracta; la creacion de la venta es directa y suficiente.

---

## UC-41: Alta Carga (Registro de Produccion)


```mermaid
sequenceDiagram
actor usuario
participant ui as RegistrarCargaUI
participant controlador as RegistrarCargaControlador
participant repoLote as RepositorioLote
participant repoCarga as RepositorioCarga
participant auditoria as ServicioAuditoria

usuario->>ui: navegaAltaCarga()
ui->>controlador: mostrarFormulario()
controlador->>repoLote: obtenerLotesActivos()
repoLote-->>controlador: lotes[]
usuario->>ui: completaDatos()
ui->>controlador: crearCarga(datos)
controlador->>repoLote: obtenerLote(datos.loteId)
repoLote-->>controlador: lote
controlador->>repoCarga: crearCarga(datos)
repoCarga-->>controlador: carga
controlador->>auditoria: registrarCambio("Carga", "created")
auditoria-->>controlador: ok
controlador-->>ui: cargaCreada(carga)
```

**Nota post-diagrama:**
- Forzado GRASP: el repositorio y la entidad de carga absorben la consistencia del dominio; el controlador solo coordina.
- Concesion pragmatica: validaciones de detalle se mantienen cercanas a la entidad para no inflar el flujo.

---

## UC-66: Gestionar Asignaciones y Propuestas (Orquestacion de Recursos)



```mermaid
sequenceDiagram
actor usuario
participant ui as AsignacionesUI
participant controlador as GestionarAsignacionesControlador
participant repoLote as RepositorioLote
participant repoPropuesta as RepositorioPropuesta
participant repoEmpleado as RepositorioEmpleado
participant repoMaquinaria as RepositorioMaquinaria
participant validador as ValidadorAsignaciones
participant repoAsignacion as RepositorioAsignacion
participant auditoria as ServicioAuditoria

usuario->>ui: navegaPropuestasOAsignaciones(loteId)
ui->>controlador: cargarLote(loteId)
controlador->>repoLote: obtenerLote(loteId)
repoLote-->>controlador: lote
controlador->>repoPropuesta: obtenerPropuestasLote(lote)
repoPropuesta-->>controlador: propuestas[]
usuario->>ui: seleccionaRecursos()
ui->>controlador: validarYAsignar(loteId, empleados, maquinarias)
controlador->>validador: validarNoSolapamiento(lote, empleados, maquinarias)
alt conflicto de recursos
  validador-->>controlador: conflictos
  controlador-->>ui: mostrarAdvertencia()
else sin conflicto
  validador-->>controlador: ok
  controlador->>repoAsignacion: crearAsignacion(lote, empleados, maquinarias)
  repoAsignacion-->>controlador: asignacion
  controlador->>repoEmpleado: marcarEmpleadosAsignados(empleados, loteId)
  repoEmpleado-->>controlador: ok
  controlador->>repoMaquinaria: marcarMaquinariasAsignadas(maquinarias, loteId)
  repoMaquinaria-->>controlador: ok
  controlador->>auditoria: registrarCambio("AsignacionRecursos", "created")
  auditoria-->>controlador: ok
  controlador-->>ui: asignacionGuardada(asignacion)
end
```

**Nota post-diagrama:**
- Forzado GRASP: `ValidadorAsignaciones` contiene la regla de no solapamiento y evita que el controlador concentre todo.
- Concesion pragmatica: asumimos transacciones del repositorio para no modelar infraestructura de compensacion compleja.

---

## UC-01: Alta Lote (Entidad Raiz Fundamental)

```mermaid
sequenceDiagram
actor usuario
participant ui as AltaLoteUI
participant controlador as AltaLoteControlador
participant repoLote as RepositorioLote
participant auditoria as ServicioAuditoria

usuario->>ui: navegaAltaLote()
ui->>controlador: mostrarFormulario()
usuario->>ui: completaDatos()
ui->>controlador: crearLote(datos)
controlador->>repoLote: crearLote(datos)
repoLote-->>controlador: lote
controlador->>auditoria: registrarCambio("Lote", "created")
auditoria-->>controlador: ok
controlador-->>ui: loteCreado(lote)
```

**Nota post-diagrama:**
- Forzado GRASP: el controlador actua como Creador de la raiz del agregado `Lote`.
- Concesion pragmatica: se omite un builder porque la construccion es directa y no hay variantes dinamicas relevantes.

---

## UC-57: Informes Generales (Consolidacion Analitica)


```mermaid
sequenceDiagram
actor usuario
participant ui as ReportesUI
participant controlador as ReportesControlador
participant repoCarga as RepositorioCarga
participant repoParte as RepositorioParteDiario
participant repoVenta as RepositorioVenta
participant repoRecibo as RepositorioRecibo
participant calculador as CalculadorKPIs
participant pdf as GeneradorPDF
participant auditoria as ServicioAuditoria

usuario->>ui: navegaReportes()
ui->>controlador: mostrarFormulario()
usuario->>ui: aplicaFiltros(rango)
ui->>controlador: generarEstadisticas(rango)
controlador->>repoCarga: obtenerCargasEnRango(rango)
repoCarga-->>controlador: cargas[]
controlador->>repoParte: obtenerPartesEnRango(rango)
repoParte-->>controlador: partes[]
controlador->>repoVenta: obtenerVentasEnRango(rango)
repoVenta-->>controlador: ventas[]
controlador->>repoRecibo: obtenerRecibosEnRango(rango)
repoRecibo-->>controlador: recibos[]
controlador->>calculador: calcularKPIs(cargas, partes, ventas, recibos)
calculador-->>controlador: kpis
controlador-->>ui: mostrarEstadisticas(kpis)
usuario->>ui: exportarPDF()
ui->>controlador: generarPDF(kpis)
controlador->>pdf: generarReportePDF(kpis)
pdf-->>controlador: pdfGenerado
controlador->>auditoria: registrarAcceso("Reportes")
auditoria-->>controlador: ok
controlador-->>ui: pdfListo()
```

**Nota post-diagrama:**
- Forzado GRASP: `CalculadorKPIs` concentra las formulas y deja al controlador como coordinador de consulta.
- Concesion pragmatica: los KPIs se calculan bajo demanda; no se persisten reportes estaticos.

---

## Sintesis de Aplicacion GRASP

- Creador: `Lote`, `Venta`, `OrdenMantenimiento` y `ParteDiario` se crean donde corresponde, sin fabricas abstractas innecesarias.
- Experto: las reglas viven en validadores y calculadores especializados, no en controladores.
- Controlador: uno por caso de uso, facil de localizar y de probar.
- Bajo acoplamiento: repositorios y servicios especializados separan coordinacion de negocio.
