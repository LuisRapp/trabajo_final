# Cierre a produccion: CI, Docker, autorizacion, dependencias, indicadores y documentacion

Objetivo: dejar Rennova lista para entrega y produccion, con evidencia verificable por fase (no una demo).
Estrategia: `main` es trunk. Una rama + un PR por fase, con CI como gate y merge commit.
Test runner: `php artisan test` (Pest) + `vendor/bin/pint --test`.

## Specs

- **S1 — CI real (Fase 1).** Los workflows viven en `.github/workflows/` de la raiz, que es el unico lugar que GitHub lee. Ambos jobs verdes sobre PHP 8.4.27. El piso de PHP vive en `composer.json`, no en el YAML: `setup-php` trunca el pin de parche a `major.minor`.
- **S2 — Docker de produccion (Fase 2).** `docker build` reproducible desde un clon limpio: multi-stage (Node compila assets, PHP sirve la app), imagen con codigo + `vendor` + assets compilados, sin `composer install` en runtime. Entrypoint: `key:generate` solo si `APP_KEY` esta vacio; `migrate --force` sin `|| true`. Compose de produccion separado del de desarrollo, sin credenciales hardcodeadas y sin bind mount del codigo. `.dockerignore` presente. Job de `docker build` en CI.
- **S3 — Autorizacion server-side (Fase 3).** Cada accion Livewire valida el permiso en PHP antes de ejecutarse (`@can` en Blade no protege el endpoint). `/adelantos` con middleware de permiso. Test por dominio que intente la accion sin permiso y verifique que no se ejecuta.
- **S4 — Dependencias y deuda critica (Fase 4).** `dompdf` en version sin los advisories de filtrado y DoS, con los PDFs validados despues del salto. Cero advisories criticos de runtime sin resolver o documentados. D12 resuelto, D6 con stock validado en la ruta FIFO, D1+D2 con las validaciones en el Servicio.
- **S5 — Indicadores (Fase 5).** Verificacion visual de los KPIs de T3 (vista + PDF). Luego tn/dia operativo y tendencia mes vs mes. La tendencia compara **solo meses cerrados**; el mes en curso se marca parcial o se compara contra el mismo tramo del mes anterior. Precio promedio por categoria de madera queda como deuda.
- **S6 — Documentacion de entrega (Fase 6).** `.env.example` real (hoy es el stock de Laravel: `APP_DEBUG=true`, `DB_CONNECTION=sqlite`, `APP_NAME=Laravel`). Guia de despliegue coherente con el artefacto que se entrega. README en la raiz que permita levantar el proyecto desde cero.
- **S7 — Reglas de negocio (Fase 7).** Un documento unico, construido en conjunto con el usuario. No se inventan reglas: lo que no esta claro queda marcado como deuda explicita.
- **S8 — Cierre (Fase 8).** `DEUDA_TECNICA.md` actualizada con lo resuelto y lo aceptado. Judgment day doble ciego sobre el acumulado.

## Tasks

- F0 (checkpoint) — Commit del cambio PHP y push de los commits pendientes. _inline_. ✅ commit 48af635.
- F1 (S1) — Mover workflows a la raiz con `working-directory`, quitar credenciales Flux inexistentes, versionar `storage/framework/views`, corregir Pint y el `tearDownAfterClass` que forzaba exit code 2. _inline_. ✅ commits 1bb1ce6, 9493ebd, a747c1e, d7887c4. CI verde en PHP 8.4.27.
- F2 (S2) — Docker de produccion: multi-stage, entrypoint corregido, compose dev/prod, `.dockerignore`, job de build en CI. _inline/delegado_. Pendiente.
- F3 (S3) — Autorizacion server-side por dominio (adelantos, ventas, lotes/asignaciones, mantenimientos, insumos, usuarios/roles). Pendiente.
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

## Log

- L1 (request original, verbatim): "podes hacer la sesion 1 de plan? que combina fase 0 y 1"
- L2 (2026-11-15): Sesion 1 cerrada. CI verde en ambos jobs sobre PHP 8.4.27 (tests 623 passed / 9 skipped / 1325 assertions; lint 471 archivos). El primer run real expuso tres defectos preexistentes: `storage/framework/views` no existia en un clon limpio, `SystemWhiteBoxTest::tearDownAfterClass()` usaba la facade `Log` con el contenedor ya destruido (exit code 2 en toda la suite, tambien en local) y Pint con `no_unused_imports`. PR #24 mergeado a `main` en `2c8c7d2`; CI de `main` verde.
- L3 (2026-11-15): PR #19 (`refactor/ventas-pr1-extract-validate`) cerrado por obsoleto. Sus dos commits reales ya eran ancestros de `main` (entraron por el PR #20, que estaba encadenado sobre esa rama); lo unico que quedaba era un merge de `main` hacia la rama, 84 commits atras. Mergearlo habria revertido el trait `MensajesErrorUsuario` y devuelto los mensajes de validacion a ingles.
- L4 (2026-11-15): Decisiones de F2-F5 confirmadas. (verbatim) "bueno, mira no tengo el lugar donde va a correr todavia, asi que vamos con tu recomendacion sobre eso. en el punto 3 de estrategia de entrega vamos con tu estrategia de un per por fase, punto 2 vamos con el best-effort que decis vos y en el punto 1 lo que digas vos. algo mas?"
- L5 (2026-11-15): Higiene de ramas detectada antes de arrancar F2: 14 ramas locales ya mergeadas en `main` (limpiables) y una rama sin mergear, `refactor-ia`, con un solo commit del 2026-06-05 ("perf: agrega paginacion a UnidadesMedida (primer componente)") y archivos de `storage/framework/views` commiteados. Pendiente de decision: descartar o retomar.
