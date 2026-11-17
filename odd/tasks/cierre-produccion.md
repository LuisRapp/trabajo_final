# Cierre a produccion: CI, Docker, autorizacion, dependencias, indicadores y documentacion

Objetivo: dejar Rennova lista para entrega y produccion, con evidencia verificable por fase (no una demo).
Estrategia: `main` es trunk. Una rama + un PR por fase, con CI como gate y merge commit.
Test runner: `php artisan test` (Pest) + `vendor/bin/pint --test`.

## Specs

- **S1 — CI real (Fase 1).** Los workflows viven en `.github/workflows/` de la raiz, que es el unico lugar que GitHub lee. Ambos jobs verdes sobre PHP 8.4.27. El piso de PHP vive en `composer.json`, no en el YAML: `setup-php` trunca el pin de parche a `major.minor`.
- **S2 — Docker de produccion (Fase 2).** `docker build` reproducible desde un clon limpio: multi-stage (Node compila assets, PHP sirve la app), imagen con codigo + `vendor` + assets compilados, sin `composer install` en runtime. Entrypoint: `key:generate` solo si `APP_KEY` esta vacio; `migrate --force` sin `|| true`. Compose de produccion separado del de desarrollo, sin credenciales hardcodeadas y sin bind mount del codigo. `.dockerignore` presente. Job de `docker build` en CI. **La fase se cierra con build verde en CI; la verificacion por uso NO se hace aca** (ver Gate de entrega).
- **S3 — Autorizacion server-side (Fase 3).** Cada accion Livewire valida el permiso en PHP antes de ejecutarse (`@can` en Blade no protege el endpoint). `/adelantos` con middleware de permiso. Test por dominio que intente la accion sin permiso y verifique que no se ejecuta.
- **S4 — Dependencias y deuda critica (Fase 4).** `dompdf` en version sin los advisories de filtrado y DoS, con los PDFs validados despues del salto. Cero advisories criticos de runtime sin resolver o documentados. D12 resuelto, D6 con stock validado en la ruta FIFO, D1+D2 con las validaciones en el Servicio.
- **S5 — Indicadores (Fase 5).** Verificacion visual de los KPIs de T3 (vista + PDF). Luego tn/dia operativo y tendencia mes vs mes. La tendencia compara **solo meses cerrados**; el mes en curso se marca parcial o se compara contra el mismo tramo del mes anterior. Precio promedio por categoria de madera queda como deuda.
- **S6 — Documentacion de entrega (Fase 6).** `.env.example` real (hoy es el stock de Laravel: `APP_DEBUG=true`, `DB_CONNECTION=sqlite`, `APP_NAME=Laravel`). Guia de despliegue coherente con el artefacto que se entrega. README en la raiz que permita levantar el proyecto desde cero.
- **S7 — Reglas de negocio (Fase 7).** Un documento unico, construido en conjunto con el usuario. No se inventan reglas: lo que no esta claro queda marcado como deuda explicita.
- **S8 — Cierre (Fase 8).** `DEUDA_TECNICA.md` actualizada con lo resuelto y lo aceptado. Judgment day doble ciego sobre el acumulado.

## Tasks

- F0 (checkpoint) — Commit del cambio PHP y push de los commits pendientes. _inline_. ✅ commit 48af635.
- F1 (S1) — Mover workflows a la raiz con `working-directory`, quitar credenciales Flux inexistentes, versionar `storage/framework/views`, corregir Pint y el `tearDownAfterClass` que forzaba exit code 2. _inline_. ✅ commits 1bb1ce6, 9493ebd, a747c1e, d7887c4. CI verde en PHP 8.4.27.
- F2 (S2) — Docker de produccion: multi-stage, entrypoint corregido, compose dev/prod, `.dockerignore`, job de build en CI. _inline_. ✅ commits ca2497b, a749fd1, 095bf2d, eacf1fa, bf2af59, 393232d. PR #25 mergeado en d18b124. CI verde: `docker`, `tests` y `linter`; el job `docker` ademas verifica el contenido de la imagen.
- F3 (S3) — Autorizacion server-side: chequeo de permiso dentro de cada accion mutadora de `app/Http/Livewire/`, middleware de permiso en `/adelantos`, y tests por dominio. _inline_. ✅ commit c3a402d. Suite `632 passed / 9 skipped`.
- F4 (S4) — Dependencias + D12/D6/D1/D2. Pendiente.
- F5 (S5) — Verificacion visual de T3 + tn/dia operativo + tendencia mes vs mes. Pendiente. Ver `odd/tasks/indicadores-financieros.md` T4.
- F6 (S6) — Documentacion de entrega: `.env.example`, guia de despliegue, README raiz. Pendiente.
- F7 (S7) — Reglas de negocio en conjunto con el usuario. Pendiente. Ultima fase por decision del usuario.
- F8 (S8) — DEUDA_TECNICA + judgment day. Pendiente.

## Decisiones confirmadas (2026-11-15)

- **Indicadores (F5)**: primero la verificacion visual de T3, despues tn/dia operativo y tendencia mes vs mes con la regla de meses cerrados. Precio promedio por categoria de madera → deuda.
- **D12**: best-effort post-commit. Las notificaciones salen de la transaccion (via `DB::afterCommit()`) con log de contexto; se documenta que son no atomicas **por diseno**. El invariante es la orden de mantenimiento, no el aviso. Ya existe el precedente en el mismo archivo: el email se manda fuera de la transaccion (linea 136).
- **Estrategia de entrega**: una rama + un PR por fase. Razon: el PR #24 se mergeo con 106 archivos y +3.597 lineas sin revision humana real; con un solo desarrollador el PR es checkpoint, y su valor es poder revertir un slice sin perder el resto.
- **Destino de produccion**: imagen de produccion + compose prod, porque sirve tanto para VPS propio como para entrega de repo y elimina la deriva de entorno. El destino concreto todavia no esta definido. Hallazgo: hoy la documentacion y el compose se contradicen — `docker-compose.yml` es de desarrollo (bind mount del codigo, `postgres/postgres` hardcodeado, puerto 5432 expuesto, sin `APP_ENV`/`APP_KEY`) y `GUIA_DESPLIEGUE_PRODUCCION.md` describe un despliegue nativo (PHP, nginx, crontab, systemd). Se elige Docker; la guia nativa se reescribe o se retira.

## F3 — Mapa de autorizacion (2026-11-17)

Hallazgo: los componentes Livewire viven en `app/Http/Livewire/` (namespace legado, no `App\Livewire`): 42 componentes y 4 traits. **Ninguno** valida permisos en PHP. La autorizacion existe solo en Blade (`@can`, y `:canEdit="auth()->user()->can(...)"` en los componentes de tabla), que oculta el boton pero no protege el endpoint. `Livewire\Component` ya incluye `AuthorizesRequests`, asi que la solucion es `$this->authorize('permiso')` como primera linea de cada accion mutadora: es la via nativa de Laravel y no agrega abstracciones.

Las vistas ya declaran el permiso de cada operacion, asi que el mapeo sale de ahi; no se inventan permisos nuevos.

### ABM estandar: `guardar` → `crear-<modulo>`, `editar` → `editar-<modulo>`, `eliminar` → `eliminar-<modulo>`

| Componente | Modulo |
|---|---|
| `Adelantos` | `adelantos` |
| `Cargas` | `cargas` |
| `CategoriasMadera` | `categorias-madera` |
| `Choferes` | `choferes` |
| `Clientes` | `clientes` |
| `Empleados` | `empleados` |
| `HistoricoCostosMaquinarias` | `historico-costos-maquinarias` |
| `HistoricoRolesLaborales` | `historico-roles-laborales` |
| `Insumos` | `insumos` |
| `ListaPrecios` | `lista-precios` |
| `Lotes` | `lotes` |
| `Mantenimientos` | `mantenimientos` |
| `Maquinarias` | `maquinarias` |
| `PartesDiarios` | `partes-diarios` |
| `Proveedores` | `proveedores` |
| `Recibos` | `recibos` |
| `RolesLaborales` | `roles-laborales` |
| `TipoMantenimiento` | `tipos-mantenimiento` |
| `TiposMaquinaria` | `tipos-maquinaria` |
| `UnidadesMedida` | `unidades-medida` |
| `Usuarios` | `usuarios` |

### Acciones de operacion (permiso ya declarado en la vista)

| Componente | Accion | Permiso |
|---|---|---|
| `Ventas` | `guardarVenta` / `guardarEdicion` / `darDeBaja` | `crear-ventas` / `editar-ventas` / `eliminar-ventas` |
| `AsignacionesLote` | `guardar` / `cancelar` | `crear-asignaciones-lote` / `editar-asignaciones-lote` |
| `AllocationProposals` | `confirmar` | `crear-propuestas-asignacion` |
| `GestionStock` | `guardar` | `crear-gestion-stock` |
| `ConfiguracionKits` | `guardar` / `eliminar` / `restaurar` | `crear-` o `editar-kits-mantenimiento` segun `item_id` / `eliminar-kits-mantenimiento` / `editar-kits-mantenimiento` |
| `LiquidacionPagos` | `calcularLiquidacion`, `liquidarTodos` / `generarRecibo` | `crear-liquidacion-pagos` / `crear-recibos` |
| `GestionMantenimientos` | `aprobarOrden` / `completarMantenimiento` | `confirmar-mantenimiento` / `completar-mantenimiento` |
| `CompletarOrdenModal` | `completarOrden` | `completar-mantenimiento` |
| `ProgramarMantenimiento` | `programarOrden` / `guardarFecha` | `crear-mantenimientos` / `reprogramar-mantenimiento` |
| `ConfiguracionMantenimiento` | `guardarConfiguracion`, `ejecutarVerificacionUmbrales`, `ejecutarVerificacionProgramados` | `configurar-mantenimiento` |
| `ConfiguracionNotificacionesMantenimiento` | `guardarConfiguracion` | `configurar-notificaciones-mantenimiento` |
| `NotificacionesCampana` | `programarMantenimiento` | `reprogramar-mantenimiento` |
| `RolesPermisos` | `createRole`, `deleteRole`, `updateRolePermissions`, `updateUserRoles` | `gestionar-permisos` |
| `LotePlanificacionTareas` | `guardar` | `editar-lotes` |
| `AllocationProposals` | `generarAhora` / `guardarSeleccion` / `aplicar` | `crear-propuestas-asignacion` / `editar-propuestas-asignacion` / `editar-propuestas-asignacion` |
| `AsignacionesLote` | `eliminarAsignacion` / `liberar` | `eliminar-asignaciones-lote` / `editar-asignaciones-lote` |
| `ConfiguracionKits` | `eliminarKit` | `eliminar-kits-mantenimiento` |
| `Lotes` | `finalizarLote` | `editar-lotes` |
| `Mantenimientos` | `confirmarMantenimiento` / `reprogramarMantenimiento` | `confirmar-mantenimiento` / `reprogramar-mantenimiento` |
| `PartesDiarios` | `crearTareaRapida` | `crear-partes-diarios` |

Las seis ultimas filas salieron de una **auditoria estatica posterior** al primer barrido: un script busca `::create`, `->save`, `->update`, `->delete`, `updateOrCreate`, `->sync` y llamadas a Servicios dentro de cada metodo publico y marca los que no tienen `authorize(`. El primer barrido se habia guiado por nombres de metodo y por eso las dejo pasar. Total final: **101 chequeos en 36 archivos**; la auditoria solo deja acciones de lectura, filtros, estado local del modal o notificaciones propias, todas listadas abajo.

### Tests

- Helper `usuarioConPermisos(array $permisos)` en `tests/Pest.php`: crea el usuario y los permisos con `Permission::firstOrCreate` + `givePermissionTo`, limpiando el cache de Spatie. Se usa en lugar del seeder completo (1,6 s por test).
- 30 archivos de test existentes actualizados: los tests de camino feliz ahora otorgan el permiso que la accion exige. Los tests de denegacion que ya existian (`UsuariosTest`, `RolesPermisosTest`) siguen creando el usuario sin permisos y verifican el 403.
- `tests/Feature/Livewire/AutorizacionTest.php` (nuevo, 9 tests): un caso por dominio (adelantos, ventas, lotes, asignaciones, mantenimientos, insumos, usuarios, roles) mas la ruta `/adelantos`. Cada uno autentica un usuario **con otro permiso del mismo modulo** (para probar que el 403 es por el permiso faltante, no por falta de todo), llama la accion y verifica el 403 junto con la no-persistencia (conteo en 0, pivote vacio, registro inexistente).
- Resultado: `632 passed / 9 skipped` (641 tests), sin fallos. Antes de F3 la linea base era `449 passed / 174 failed / 9 skipped`.

### Hallazgo pendiente para F7: paginas sin `ver-*` a nivel ruta

Ninguna ruta de modulo (salvo `/usuarios`, las de mantenimiento especiales, `/roles-permisos` y ahora `/adelantos`) tiene middleware `permission:ver-<modulo>`. Las paginas Livewire se pueden abrir con solo estar autenticado; lo que queda protegido es cada accion mutadora. Cerrar eso exige decidir en F7 si `ver-*` bloquea la pagina o solo el sidebar, porque hoy el sidebar oculta el link pero la URL directa funciona. Se documenta como decision de negocio, no como defecto de F3.

### Formularios hijos: no se protegen porque no persisten

`CargaForm`, `JornalForm` y `MovimientoForm` agregan items a una lista local (`::create`, `->save` y `->delete` no aparecen en esos archivos). La persistencia ocurre en el `guardar` del componente padre, que si queda protegido. Un chequeo ahi seria ruido sin proteccion.

### Acciones que no se protegen

- `NotificacionesCampana` y `NotificacionesSistema`: marcar leida o accionada cambia solo el estado de las notificaciones del propio usuario.
- `Auditorias`, `EstadoProcesos`, `SelectorLote` y los traits: solo filtros, calculos o lectura.

### Supuestos a confirmar en F7 (reglas de negocio)

- `ProgramarMantenimiento::programarOrden` usa `crear-mantenimientos`, no `reprogramar-mantenimiento`: este ultimo es para reprogramar, y usarlo aca le quitaria la accion al Operador y al Supervisor, que hoy pueden programar.
- `LotePlanificacionTareas::guardar` usa `editar-lotes` porque no existe un modulo de tareas.
- `/adelantos` usa `permission:ver-adelantos`, coherente con el sidebar, que ya oculta el link con ese permiso.
- `Cargas`, `PartesDiarios` y otros modulos solo tienen `ver-`/`crear-`/`editar-` en algunos roles; los chequeos usan el permiso que la vista ya exige, asi que no cambian quien puede operar.
- `AllocationProposals::guardarSeleccion` y `aplicar` usan `editar-propuestas-asignacion` (modifican una propuesta existente); `generarAhora` y `confirmar` usan `crear-propuestas-asignacion`. La vista acepta `@canany` de ambos; hoy solo el Administrador tiene los dos, asi que no cambia quien opera. Confirmar en F7.
- `PartesDiarios::crearTareaRapida` usa `crear-partes-diarios`, no `editar-lotes`: la accion vive en el flujo del parte diario y la usa el Operador, que no tiene `editar-lotes`. Confirmar en F7.
- `Mantenimientos::reprogramarMantenimiento` llama internamente a `editar($id)` (que exige `editar-mantenimientos`) cuando el servicio responde bien. Hoy solo el Administrador tiene `reprogramar-mantenimiento`, asi que no se ve; si F7 se lo da a otro rol, hay que alinear ambos permisos.

## Gate de entrega (antes de entregar, despues de F8)

Se instala Docker **una sola vez**, al final del plan, para no abrir cambios de alcance a mitad de camino. Esto NO es deuda: es un paso obligatorio y verificable.

- [ ] Instalar Docker y agregar el usuario al grupo `docker`.
- [ ] Clonar el repo en un directorio limpio (no el working copy).
- [ ] `docker compose -f docker-compose.prod.yml up --build` sin tocar nada a mano.
- [ ] Verificar: la app responde, el login funciona, las migraciones corrieron, el queue worker procesa un job y el cron esta programado.
- [ ] Verificar que el `.env` del working copy **no se modifico** (la APP_KEY local queda intacta).
- [ ] Recien con eso verde: entregar.

Si algo falla aca, se corrige y se vuelve a correr el gate completo. No se entrega con el gate parcialmente verificado.

## Backlog post-entrega (parking)

Todo lo que aparezca durante F2-F8 y no sea un defecto de la fase en curso se anota aca y **no entra al alcance**. Esto es lo que evita el "ya que estoy, cambio esto" a mitad de plan.

- Precio promedio por categoria de madera (descartado de F5, ver `indicadores-financieros.md` L7).
- D13, D14 (deuda aceptada).
- Cualquier mejora de UI que surja al ver la app corriendo durante el Gate de entrega.

## Log

- L1 (request original, verbatim): "podes hacer la sesion 1 de plan? que combina fase 0 y 1"
- L2 (2026-11-15): Sesion 1 cerrada. CI verde en ambos jobs sobre PHP 8.4.27 (tests 623 passed / 9 skipped / 1325 assertions; lint 471 archivos). El primer run real expuso tres defectos preexistentes: `storage/framework/views` no existia en un clon limpio, `SystemWhiteBoxTest::tearDownAfterClass()` usaba la facade `Log` con el contenedor ya destruido (exit code 2 en toda la suite, tambien en local) y Pint con `no_unused_imports`. PR #24 mergeado a `main` en `2c8c7d2`; CI de `main` verde.
- L3 (2026-11-15): PR #19 (`refactor/ventas-pr1-extract-validate`) cerrado por obsoleto. Sus dos commits reales ya eran ancestros de `main` (entraron por el PR #20, que estaba encadenado sobre esa rama); lo unico que quedaba era un merge de `main` hacia la rama, 84 commits atras. Mergearlo habria revertido el trait `MensajesErrorUsuario` y devuelto los mensajes de validacion a ingles.
- L4 (2026-11-15): Decisiones de F2-F5 confirmadas. (verbatim) "bueno, mira no tengo el lugar donde va a correr todavia, asi que vamos con tu recomendacion sobre eso. en el punto 3 de estrategia de entrega vamos con tu estrategia de un per por fase, punto 2 vamos con el best-effort que decis vos y en el punto 1 lo que digas vos. algo mas?"
- L5 (2026-11-15): Higiene de ramas detectada antes de arrancar F2: 14 ramas locales ya mergeadas en `main` (limpiables) y una rama sin mergear, `refactor-ia`, con un solo commit del 2026-06-05 ("perf: agrega paginacion a UnidadesMedida (primer componente)") y archivos de `storage/framework/views` commiteados. Pendiente de decision: descartar o retomar.
- L6 (2026-11-15): `refactor-ia` resuelta: sus dos cambios reales ya estan en `main` (paginacion en `UnidadesMedida` y extraccion a `UnidadMedidaService`). Rama descartada junto con las 14 mergeadas.
- L7 (2026-11-15): (verbatim) "sobre esto de que no voy a poder verificar por uso. si me instalo docker voy a poder ver y corroborar, no tiene sentido hacer eso ahora que esta todo tan roto". Se verifica y la objecion es correcta por un motivo mas fuerte: el `Dockerfile` anterior no copiaba el codigo, dependia del bind mount `./rennova` de `docker-compose.yml`, y ese montaje hacia que `key:generate --force` reescribiera el `.env` real del working copy en cada arranque. Levantar Docker antes de F2 no mostraba la app: atacaba el entorno local.
- L8 (2026-11-15): (verbatim) "na chill, no te parece que mejor terminemos el plan que tenemos de las 8 fases y qe de ahi me instale docker, sino voy a ir queriendo meter cambios y se arma un embole". Docker se instala una sola vez al final; se agregan las secciones Gate de entrega y Backlog post-entrega. F2 se cierra con build verde en CI, no con verificacion por uso.
- L9 (2026-11-15): F2 cerrada. El primer build fallo en `npm run build` (`Can't resolve '../../vendor/livewire/flux/dist/flux.css'`): `resources/css/app.css` importa el CSS de Flux y usa `@source` sobre los stubs de Flux y las vistas de paginacion de Laravel, asi que la etapa de assets necesita `vendor/`. Se resolvio con una etapa `vendor` (`composer install --no-dev --no-scripts`) compartida por `assets` y `runtime`, y una etapa `base` con las extensiones para no compilarlas dos veces. Otros tres defectos detectados en el camino: `docker-php-ext-install pdo` recompilaba una extension ya incluida ("pdo is already loaded"); el entrypoint corria `migrate` y el worker como root mientras Apache escribe como `www-data`, lo que dejaba `laravel.log` sin permiso para `www-data` (se resolvio con `gosu`, y `USUARIO_APP` permite que el compose de desarrollo siga usando root sobre el bind mount); y el job de CI solo construia, sin comprobar que la imagen contuviera el codigo, el vendor y los assets (se agrego `load: true` y una verificacion estructural). Verificado: 95 paquetes de produccion con `dist` (no hace falta `git`), 13 `ext-*` ya compiladas en la imagen oficial, `opcache` ya habilitado por la imagen base.
- L10 (2026-11-17): F3 cerrada. 101 chequeos `authorize()` en 36 componentes. Dos correcciones de rumbo en el camino: (1) el primer barrido inserto los permisos sin comillas (`authorize(crear-adelantos)`), se revirtio con `git checkout --` y se rehizo; (2) el barrido inicial se guio por nombres de metodo y dejo 10 acciones mutadoras sin guard, que aparecieron con una auditoria estatica posterior (`::create`, `->save`, `->update`, `->delete`, `updateOrCreate`, `->sync`, servicios): `AllocationProposals::generarAhora|guardarSeleccion|aplicar`, `AsignacionesLote::eliminarAsignacion|liberar`, `ConfiguracionKits::eliminarKit`, `Lotes::finalizarLote`, `Mantenimientos::confirmarMantenimiento|reprogramarMantenimiento`, `PartesDiarios::crearTareaRapida`. Los `guardar` de los ABM son upsert (`crearOActualizar($this-><x>_id, ...)`), asi que el guard es condicional (`editar-X` o `crear-X` segun el id) para no bloquear a quien solo tiene `editar-X`. Tests: helper `usuarioConPermisos()` en `tests/Pest.php` (sin seeder, que cuesta 1,6 s por test), 30 archivos existentes actualizados y `AutorizacionTest` nuevo con un caso por dominio; cada caso autentica un usuario con otro permiso del mismo modulo y verifica 403 + no-persistencia. Suite final `632 passed / 9 skipped`; antes de F3 era `449 passed / 174 failed / 9 skipped`. Queda anotado para F7 que ninguna ruta de modulo (salvo `/usuarios`, las de mantenimiento especiales, `/roles-permisos` y ahora `/adelantos`) exige `ver-*`: la pagina se abre con solo estar autenticado y lo protegido es la accion.
