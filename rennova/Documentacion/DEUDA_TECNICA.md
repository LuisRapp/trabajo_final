# Deuda Técnica Dimensionada

Ultima actualizacion: 21 de septiembre de 2026.

Estado del trabajo validado al cierre: `php artisan test` = **511 passed, 9 skipped**. Los fixes criticos de las sesiones de refactor (cierres unificados, split de servicios, eliminacion de hard deletes y estados invalidos) fueron verificados como conformes por el review del hook.

Esta documento dimensiona lo que queda: severidad, esfuerzo estimado y riesgo. Es el insumo para planificar las proximas sesiones junto con el plan de 2 sesiones ya guardado en memoria.

---

## Resuelto y verificado (no rehacer)

- Cierre de mantenimiento unificado en `MantenimientoService::completarMantenimiento` (FIFO, snapshot del odometro, costo_mano_obra, guard anti-doble-cierre, fecha opcional). El reviewer confirma conformidad.
- Cierre de lotes unificado en `AsignacionLoteService::finalizar` (detach con auditoria, estado 'cerrado' valido, guard en el servicio). El hard delete de pivotes y el estado invalido 'terminado' fueron eliminados.
- Split de `MantenimientoService` en core + `ProcesoMantenimientoService` (PA-01) + `PropuestaCompraMantenimientoService` + `MailMantenimientoService`.
- Delegacion completa de `CompletarOrdenModal` y `GestionMantenimientos` a servicios (queries, reglas preventivo/correctivo, aprobacion por servicio).
- Renombre de tablas/PKs/estados de propuestas de compra al espanol (migracion `2026_09_21_010000`).
- SoftDeletes verificados en `Carga`, `Mantenimiento`, `Lote`, `PropuestaAsignacion`, `MovimientoStock`: todos los `delete()` sobre ellos son logicos.
- Excepcion PostgreSQL (funciones `calcular_costo_fifo`, `obtener_stock_disponible`, `obtener_precio_promedio`) usada conforme: desde Servicio, documentada, con fallback.

---

## Pendiente — prioridad ALTA

### D1. Validacion de conflictos de asignacion fuera del Servicio
- **Donde:** `app/Services/AsignacionLoteService::asignarRecursos` (la regla de "recurso ya asignado a otro lote activo" vive solo en el componente `AsignacionesLote::detectarConflictosEmpleados/Maquinarias`).
- **Riesgo:** cualquier llamador directo del servicio puede crear dobles asignaciones. Regla critica sin validar en la capa de Servicios (viola el estandar).
- **Fix:** mover la deteccion al servicio, dentro de la transaccion con `lockForUpdate`.
- **Esfuerzo:** ~2 h (con tests).

### D2. Queries y reglas de negocio en `AsignacionesLote` y `Lotes`
- **Donde:** `AsignacionesLote` (mount/cargarAsignaciones/cargarHistorial ~ lineas 50-92, detectarConflictos ~ 246-303), `Lotes` (cargarLotes/guardar/editar/eliminar ~ 51-158, transicion de estado a en_proceso en guardar).
- **Riesgo:** violacion directa de las prohibiciones arquitectonicas (ERROR del gate). Es lo que bloquea el commit con review.
- **Fix:** delegar a `AsignacionLoteService` (obtenerRecursosAsignables, detectarConflictos) y a un servicio de lotes para el ABM + transicion de estado.
- **Esfuerzo:** ~3-4 h (con tests). Incluye D1 naturalmente.

### D3. SQL crudo en `InventarioService::queryInsumosConStockYPrecio`
- **Donde:** lineas ~352-374: `selectRaw('COALESCE(SUM(...))')` y `selectRaw('CASE WHEN ...')`.
- **Riesgo:** ERROR del gate (no entra en la excepcion de funciones PostgreSQL).
- **Fix:** Query Builder con `withSum`/subqueries.
- **Esfuerzo:** ~1 h.

---

## Pendiente — prioridad MEDIA

### D4. `VentaService`: SQL crudo
- **Donde:** `buscarCargasPendientes` (~195-215, `DB::raw` para categoria/ROUND/COALESCE) y `listarVentas` (~292, `orWhereRaw` con LOWER/CAST).
- **Fix:** Query Builder / `whereLike`. **Esfuerzo:** ~1 h. (Sus docblocks en ingles se revertieron a HEAD a proposito para no ampliar el scope del gate; traducir junto con este fix.)

### D5. Renombre de `AutomaticAllocationService` al espanol
- **Donde:** clase + metodos `proposeForLotAndTask`, `proposeForLoteTarea`, `ensureWeek1SupplyEstimates`, `findBestFallback` + variables en ingles en `PropuestaAsignacionService` (`$employeeSelected`, `$proposalId`, `$editData`, etc.).
- **Callers a actualizar:** 2 Jobs (`GenerateAllocationProposalsForLote`, `ProcessAllocationProposal`), `LotePlanificacionTareas`, comandos `allocation:*`, tests, `ARQUITECTURA_ASIGNACION_AUTOMATICA_RECURSOS.md`.
- **Esfuerzo:** ~2 h. Nota: las tablas `allocation_proposals*` siguen con nombre en ingles (mismo criterio de `D3`-docs: renombrar en una migracion dedicada junto con este trabajo).

### D6. Validacion de stock en la ruta FIFO manual
- **Donde:** `InventarioService::calcularCostoFifoManual` (no verifica stock total antes de confirmar; en la ruta no-PostgreSQL puede confirmar una salida parcial sin error).
- **Riesgo:** viola "toda salida de stock debe validar existencia disponible". Solo afecta SQLite/testing (en PG valida la funcion), pero es una regla critica.
- **Esfuerzo:** ~30 min.

---

## Pendiente — prioridad BAJA (deuda aceptada, planificar a demanda)

### D7. Transacciones manuales → `DB::transaction()`
`InventarioService` (registrarSalida/registrarEntrada), `EmpleadoPagoService` (generarRecibo/liquidarTodos), `PartesDiariosService::guardar`, `VentaService` (registrarVenta/darDeBaja/editarVenta). ~1 h mecanico (verificar retornos tempranos antes de convertir, como se hizo en MantenimientoService).

### D8. N+1 y colecciones en memoria
- `AnalizarRiesgoClimatico::calcularCostoEstructuralDiario`: por lote carga TODOS los empleados activos y maquinarias alquiladas; ademas la consulta de empleados no filtra por lote pese al comentario.
- `EmpleadoPagoService::calcularPagoRango`: `exists()` por cada parte diario del rango.
- `MantenimientoService::obtenerInsumosParaCierre`: 2 consultas por insumo.
- `PartesDiariosService::buscarJornalVigente`: consulta por empleado dentro del bucle.
- ~2-3 h en total.

### D9. Regla climatica duplicada y calculo hardcodeado
`AnalizarRiesgoClimatico`: `const UMBRAL_LLUVIA` duplicada con `ClimaDecisionService` y estimacion "10 tn/dia" hardcodeada. Mover el calculo de costo estructural a un servicio. ~30 min.

### D10. Tamanos/metodos > 80 lineas y servicios con multiples responsabilidades
- `AnalizarDecisionesClimaticas::handle` (~100), `AnalizarRiesgoClimatico::handle` (~90), `EmpleadoPagoService::calcularPagoRango` (~90).
- `MantenimientoService` (~600 lineas): candidato a `MantenimientoCierreService` / `MantenimientoConsultaService`.
- `ProcesoMantenimientoService`: mezcla umbrales + clima + personal + notificaciones (candidato a dividir si crece).
- Refactor estructural futuro; no urgente.

### D11. Menores
- `Lotes.php` linea ~54: operador `ILIKE` directo (tolerable en PostgreSQL-only; alinear a `whereLike` cuando se toque el archivo).
- `LoteInventario::estaProximoAgotar()` deprecated: eliminar cuando se migren los usos.

---

## Sugerencia de orden para la proxima sesion de refactor

1. D2 + D1 juntos (mismo dominio, desbloquea el gate).
2. D3 + D4 + D6 (SQL crudo + stock, mismo archivo InventarioService/VentaService).
3. D5 (renombre, mecanico pero transversal).
4. D7-D11 a demanda.

Despues de cada bloque: commit con gate (deberia pasar), y al final del plan: auditoria formal con jueces (judgment day, doble ciego) sobre el acumulado.
