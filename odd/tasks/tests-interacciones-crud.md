# Tests de interacción — CRUDs de parametría (QA punto 2)

Objetivo: agregar tests de interacción (crear/editar/eliminar/validación) a los CRUDs de parametría que hoy no tienen cobertura.
Rama: feat/proyeccion-equilibrio-qa · Entrega: ask-on-risk · Test runner: php artisan test (Pest)

## Specs

- **S1** — Cada CRUD de parametría tiene un test Livewire que cubre: render autenticado, crear (happy path), editar, eliminar (SoftDelete) y validación de campos obligatorios.
- **S2** — Los tests siguen el patrón de `ProveedoresTest.php` (Livewire::actingAs + set + call + assert).

## Tasks

- T1 — CategoriasMaderaTest. ✅ commit a9ad248 (test(parametria)).
- T2 — UnidadesMedidaTest. ✅ idem.
- T3 — TiposMaquinariaTest. ✅ idem.
- T4 — RolesLaboralesTest. ✅ idem.
- T5 — TipoMantenimientoTest. ✅ idem.
- T6 — ChoferesTest. ✅ idem.
- T7 — EmpleadosTest. ✅ idem.
- T8 — MaquinariasTest. ✅ idem.

## Log

- L1 (2026-11-02): El user pidió automatizar el QA "probando botones". Tanda 1 = CRUDs de parametría sin test. Ya existen: CategoriasMadera, UnidadesMedida, TiposMaquinaria, RolesLaborales, TipoMantenimiento, Choferes, Empleados, Maquinarias (sin test). PartesDiarios y CargaForm ya tienen cobertura.
- L2 (2026-11-02): Tanda 1 cerrada — 8 archivos, 40 tests en verde. Suite total 604 passed / 9 skipped.
- L3 (2026-11-02): Bugs encontrados: (1) empleados.blade.php hacía number_format($empleado->dni) → TypeError cuando el DNI tiene puntos (fix: mostrar crudo, commit f899f95). (2) EmpleadoFactory dni "##.###.###" con puntos viola digits:8 (fix commit e802cf9). (3) MaquinariaFactory estado no matchea la validación.
- L4 (2026-11-02): Estados de maquinaria MUCHO más profundo: blade usa 'disponible'/'asignada'/'en_mantenimiento'; validación 'operativa'/'en_mantenimiento'/'fuera_de_servicio'; factory 'activo'/'mantenimiento'/'fuera_servicio'; migración default 'activo'; servicios 'activo'/'operativa'. Fix: alineé todo a 'disponible'/'asignada'/'en_mantenimiento' (factory + validación + 2 servicios + tests). Commit 0bfd609. Pendiente: migración default, DemoDaySeeder, indices_optimizacion.sql.
- L5 (2026-11-02): DEUDA ARQUITECTÓNICA (gga la detectó): TODOS los CRUDs de Livewire hacen consultas directas a BD (updateOrCreate/findOrFail/delete) violando AGENTS.md. Es deuda de todo el proyecto.
