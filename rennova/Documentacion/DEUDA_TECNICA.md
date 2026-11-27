# Deuda Técnica Dimensionada

Ultima actualizacion: 18 de noviembre de 2026.

Estado del trabajo validado al cierre de F8: `php artisan test` = **711 passed, 9 skipped**. Este documento dimensiona lo que queda: severidad, esfuerzo estimado y riesgo. Es el insumo para planificar refactors a demanda.

---

## Resuelto y verificado (no rehacer)

- Cierre de mantenimiento unificado en `MantenimientoService::completarMantenimiento` (FIFO, snapshot del odometro, costo_mano_obra, guard anti-doble-cierre, fecha opcional).
- Cierre de lotes unificado en `AsignacionLoteService::finalizar` (detach con auditoria, estado 'cerrado' valido, guard en el servicio).
- Split de `MantenimientoService` en core + `ProcesoMantenimientoService` (PA-01) + `PropuestaCompraMantenimientoService` + `MailMantenimientoService`.
- Delegacion completa de `CompletarOrdenModal` a servicios (la pantalla duplicada `GestionMantenimientos` se elimino).
- Renombre de tablas/PKs/estados de propuestas de compra al espanol.
- SoftDeletes verificados en `Carga`, `Mantenimiento`, `Lote`, `PropuestaAsignacion`, `MovimientoStock`.
- Excepcion PostgreSQL (funciones `calcular_costo_fifo`, `obtener_stock_disponible`, `obtener_precio_promedio`) usada conforme: desde Servicio, documentada, con fallback.

### Resueltas en F4 (2026-11-17)

- **D12 — Notificaciones fuera de la transaccion**: `crearNotificacionInterna` y `crearNotificacionVencido` pasan a llamarse despues del `DB::transaction`, igual que el email. El invariante es la orden; el aviso es best-effort con contexto en el log (`id_mantenimiento`, `id_maquinaria`, `error`). Se descarto `DB::afterCommit()`: con `RefreshDatabase` los callbacks nunca se ejecutan.
- **D6 — Stock en la ruta FIFO manual**: `calcularCostoFifoManual` valida el total disponible antes de consumir (tolerancia `1e-6`).
- **D1 — Conflictos de asignacion en el Servicio**: `AsignacionLoteService::asignarRecursos` valida la exclusividad dentro de la transaccion con `lockForUpdate` sobre las filas de los recursos. El componente conserva el prechequeo solo para mostrar conflictos. Nota F7: la exclusividad ahora cubre lotes `activo` **y** `en_proceso`.
- **D2 — Queries fuera de los componentes**: `AsignacionesLote` delega `mount`/`cargarAsignaciones`/`cargarHistorial` a `AsignacionLoteService`; `Lotes` ya delegaba en `LoteService`.

### Resueltas en F7 (2026-11-17)

- **Exclusividad activo/en_proceso**: los 6 metodos que chequeaban "recurso ocupado" (manual, propuesta, candidatos) usaban estados inconsistentes; unificados a `['activo', 'en_proceso']`.
- **Estados operativos**: `hayLotesActivos()` del panel y el filtro de lotes del reporte de estadisticas solo miraban `activo`; ahora incluyen `en_proceso`.
- **Cierre de lote por ABM**: `crearOActualizar` libera recursos al pasar a `cerrado`/`baja` (antes quedaban colgados).

---

## Pendiente — prioridad ALTA

### D3. SQL crudo en `InventarioService::queryInsumosConStockYPrecio`
- **Donde:** `selectRaw('COALESCE(SUM(...))')` y `selectRaw('CASE WHEN ...')`.
- **Riesgo:** ERROR del gate (no entra en la excepcion de funciones PostgreSQL).
- **Fix:** Query Builder con `withSum`/subqueries. **Esfuerzo:** ~1 h.

---

## Pendiente — prioridad MEDIA

### D4. `VentaService`: SQL crudo
- **Donde:** `buscarCargasPendientes` (`DB::raw` para categoria/ROUND/COALESCE) y `listarVentas` (`orWhereRaw` con LOWER/CAST).
- **Fix:** Query Builder / `whereLike`. **Esfuerzo:** ~1 h.

### D5. Renombre de `AutomaticAllocationService` al espanol
- **Donde:** clase + metodos `proposeForLotAndTask`, `proposeForLoteTarea`, `ensureWeek1SupplyEstimates`, `findBestFallback` + variables en ingles en `PropuestaAsignacionService`.
- **Callers:** 2 Jobs, `LotePlanificacionTareas`, comandos `allocation:*`, tests, `ARQUITECTURA_ASIGNACION_AUTOMATICA_RECURSOS.md`.
- **Esfuerzo:** ~2 h. Las tablas `allocation_proposals*` siguen en ingles (renombrar en migracion dedicada junto con este trabajo).

---

## Pendiente — prioridad BAJA (deuda aceptada, planificar a demanda)

### D7. Transacciones manuales → `DB::transaction()`
`InventarioService` (registrarSalida/registrarEntrada), `EmpleadoPagoService` (generarRecibo/liquidarTodos), `PartesDiariosService::guardar`, `VentaService` (registrarVenta/darDeBaja/editarVenta). ~1 h mecanico.

### D8. N+1 y colecciones en memoria
- `AnalizarRiesgoClimatico::calcularCostoEstructuralDiario`: por lote carga TODOS los empleados activos y maquinarias alquiladas.
- `EmpleadoPagoService::calcularPagoRango`: `exists()` por cada parte diario del rango.
- `MantenimientoService::obtenerInsumosParaCierre`: 2 consultas por insumo.
- `PartesDiariosService::buscarJornalVigente`: consulta por empleado dentro del bucle. ~2-3 h.

### D9. Regla climatica duplicada y calculo hardcodeado
`AnalizarRiesgoClimatico`: `const UMBRAL_LLUVIA` duplicada con `ClimaDecisionService` y estimacion "10 tn/dia" hardcodeada. ~30 min.

### D10. Tamanos/metodos > 80 lineas y servicios con multiples responsabilidades
- `AnalizarDecisionesClimaticas::handle`, `AnalizarRiesgoClimatico::handle`, `EmpleadoPagoService::calcularPagoRango` (~90).
- `MantenimientoService` (~600 lineas): candidato a `MantenimientoCierreService` / `MantenimientoConsultaService`. Refactor estructural futuro; no urgente.

### D11. Menores
- `LoteService::listar` usa `ILIKE` directo (tolerable en PostgreSQL-only; alinear a `whereLike`).
- `LoteInventario::estaProximoAgotar()` deprecated: eliminar cuando se migren los usos.

### D13. Reglas de clima y "tipo preventivo" duplicadas (MEDIA)
- **Donde:** `obtenerTipoPreventivo()` re-implementa `whereLike('nombre','%preventivo%')` en vez de `MantenimientoService::esTipoPreventivo()`. `resolverFechaProgramadaPorClima` re-deriva la decision de lluvia (`UMBRAL_LLUVIA` + `str_contains($razon,'lluvia')`) en vez de consumir `ClimaAnalisisService::mapearDiasInactivos()`.
- **Fix:** delegar en los metodos canonicos. **Esfuerzo:** ~2 h.

### D14. Identificadores en ingles y dedup en memoria (BAJA)
- `$fallbackDate`, `$fechaRaw`, `$keyword`, `$correoAdmin`; `->get()->unique('mantenimiento_id')->values()`.
- **Fix:** renombrar a espanol y deduplicar a nivel query. **Esfuerzo:** ~30 min.

---

## Deuda nueva — detectada en F5-F7 (2026-11-17)

- **`ReporteController`**: `DB::raw('peso_neto / 1000.0')` para aritmetica de toneladas, `buildChartImage` con nombre en ingles, y `desde`/`hasta` alimentados a `Carbon::parse` sin validar. Aparecio en el hook `gga run` durante F5.
- **`contarInsumosCriticos()`** (codigo muerto): cuenta TODOS los insumos bajo umbral, pero la regla documentada es "kits de mantenimiento + combustible" (identificado por nombre). Si se conecta, no va a coincidir.
- **`completarMantenimiento()`** no exigia pasar por "en curso". **Resuelta en el Gate (L45):** ahora solo completa ordenes `en curso` y la UI oculta el cierre en las demas.
- **`ReporteController::buildChartImage`** usa `quickchart.io` con `Http::get` sin fake en algunos flujos de test (acoplado a red externa).

---

## Deuda nueva — detectada en el Gate de entrega (2026-11-23)

- **`SelectorLote` consulta la base directamente desde el componente Livewire** (`::where()`, `::find()`), lo que viola la prohibicion arquitectonica de AGENTS.md. El hook `gga run` la marco al tocar el archivo para corregir el bug del snapshot del dashboard. Es deuda **preexistente**, no introducida en el gate. **Fix:** extraer `SelectorLoteService` y dejar el componente con interaccion + validacion solamente. **Esfuerzo:** ~1 h.
- **`ConfiguracionKits`**: resuelta en el gate. El acceso a datos se movio a `KitMantenimientoPreventivoService`, `eliminarKit()` corre dentro de `DB::transaction()` y se elimino el historial de bajas de la vista (el metodo `restaurar()` queda sin UI, cubierto por tests).

---

## Sugerencia de orden para la proxima sesion de refactor

1. D3 + D4 (SQL crudo, InventarioService/VentaService, desbloquean el gate).
2. D13 (duplicacion de reglas de clima/tipo, toca la programacion por clima).
3. D5 (renombre, mecanico pero transversal).
4. D7-D14 a demanda.

Despues de cada bloque: commit con gate (deberia pasar), y al final: auditoria formal con jueces (judgment day, doble ciego) sobre el acumulado.
