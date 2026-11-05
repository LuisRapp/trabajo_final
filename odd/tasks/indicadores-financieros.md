# Indicadores Financieros: modelo de costos unificado + punto de equilibrio + nuevos indicadores

Objetivo: unificar el cálculo de costos y rentabilidad en una sola fuente de verdad, corregir el punto de equilibrio (hoy roto) y agregar indicadores financieros que faltan.
Rama: feat/proyeccion-equilibrio-qa · Entrega: ask-on-risk · Test runner: php artisan test (Pest)

## Specs

- **S1 — Costo/tn unificado.** "Costo por tonelada" tiene UNA definición en todo el sistema: `costo_total_dia ÷ toneladas`. Se eliminan las variantes que usan liquidaciones o mano de obra calculada por separado. *User: "unificar el modelo de costos (una sola fuente de verdad para el costo/tn)".*
- **S2 — Punto de equilibrio correcto.** El punto de equilibrio se calcula con la fórmula correcta (costos fijos ÷ margen de contribución) y se muestra en toneladas/día. Fijo = jornales de días caídos; variable = el resto. *User eligió "jornales de días caídos".*
- **S3 — Rentabilidad real.** Agregar margen bruto (%) además del margen por tonelada. *User: "evaluamos agregar mas indicadores y revisamos los que ya estan".*
- **S4 — Indicadores faltantes.** Ingresos totales, rendimiento tn/ha, toneladas por día operativo, tendencia mes vs mes, precio por categoría de madera. Alcance por definir.

## Tasks

- T1 (S1) — Unificar costo/tn a `costo_total_dia` en `ForestalStatsService` y `ReporteController` (quitar liquidaciones/mano de obra separadas y el doble conteo del PDF). `PanelControlService` ya usaba `costo_total_dia`. Ruta: inline. ✅ commit 2f1491d.
- T2 (S2) — Corregir punto de equilibrio: fórmula, unidad y una sola implementación. Fijo = jornales de días caídos. Ruta: inline. ✅ commit 98843fa.
- T3 (S3-S4) — Nuevos indicadores financieros. Ruta: por definir. Alcance por definir.

## Log

- L1 (request original, verbatim): "te propongo algo, hagamos un commit establecemos esto como punto de control y nos metemos en la parte financiera y no unicamente con el punto de equilibrio sino que evaluamos agregar mas indiciadores y revisamos los que ya estan?"
- L2 (2026-11-05): Decisión — orden: (1) unificar modelo de costos, (2) corregir punto de equilibrio, (3) agregar indicadores. Costo operativo = `costo_total_dia` (devengado, incluye mano de obra calculada). *User: "si, vamos con tu recomendacion".*
- L3 (2026-11-05): T1 cerrado. `getCostoPromedioPorTn` usa `costo_total_dia ÷ toneladas`; se eliminaron los parámetros `incluirLiquidaciones`/`usarManoObraPartes`, el doble conteo de liquidaciones en el PDF y el método muerto `calcularCostoManoObra`. Suite 621 passed / 9 skipped.
- L4 (2026-11-05): T2 cerrado. Punto de equilibrio = `(jornales caídos ÷ días) ÷ (precio − variable ÷ toneladas)`, en tn/día. La vista ahora lo rotula como tn/día (antes $/tn). Se unificó la implementación global (suma del punto diario por lote). Suite 623 passed / 9 skipped.
