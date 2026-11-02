# Tests de interacción — CRUDs de parametría (QA punto 2)

Objetivo: agregar tests de interacción (crear/editar/eliminar/validación) a los CRUDs de parametría que hoy no tienen cobertura.
Rama: feat/proyeccion-equilibrio-qa · Entrega: ask-on-risk · Test runner: php artisan test (Pest)

## Specs

- **S1** — Cada CRUD de parametría tiene un test Livewire que cubre: render autenticado, crear (happy path), editar, eliminar (SoftDelete) y validación de campos obligatorios.
- **S2** — Los tests siguen el patrón de `ProveedoresTest.php` (Livewire::actingAs + set + call + assert).

## Tasks

- T1 — CategoriasMaderaTest. Ruta: inline.
- T2 — UnidadesMedidaTest. Ruta: inline.
- T3 — TiposMaquinariaTest. Ruta: inline.
- T4 — RolesLaboralesTest. Ruta: inline.
- T5 — TipoMantenimientoTest. Ruta: inline.
- T6 — ChoferesTest. Ruta: inline.
- T7 — EmpleadosTest. Ruta: inline.
- T8 — MaquinariasTest. Ruta: inline.

## Log

- L1 (2026-11-02): El user pidió automatizar el QA "probando botones". Tanda 1 = CRUDs de parametría sin test. Ya existen: CategoriasMadera, UnidadesMedida, TiposMaquinaria, RolesLaborales, TipoMantenimiento, Choferes, Empleados, Maquinarias (sin test). PartesDiarios y CargaForm ya tienen cobertura.
