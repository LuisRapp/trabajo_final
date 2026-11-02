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
- L3 (2026-11-02): Bugs encontrados: (1) empleados.blade.php hacía number_format($empleado->dni) → TypeError cuando el DNI tiene puntos (fix: mostrar crudo, commit f899f95). (2) Latente: EmpleadoFactory genera dni "##.###.###" con puntos que viola la regla digits:8 (pendiente decidir si se arregla el factory). (3) MaquinariaFactory genera estado 'activo'/'mantenimiento'/'fuera_servicio' que NO matchea la validación del componente ('operativa'/'en_mantenimiento'/'fuera_de_servicio').
