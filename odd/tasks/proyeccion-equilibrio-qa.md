# Proyección vs Punto de Equilibrio + Cierre QA Front

Objetivo: cerrar los hallazgos de QA del front y agregar una proyección mensual GLOBAL (solo lun-vie) comparada contra el punto de equilibrio AUTOMÁTICO, con alerta si se queda corto.
Rama: feat/proyeccion-equilibrio-qa · Entrega: ask-on-risk · Test runner: php artisan test (Pest)

## Specs

- **S1 — Días en español.** Los días de la semana en el dashboard (pronóstico del clima) se muestran en español, no en inglés. *User: "los dias en el dash principal se muestran en ingles no en espanol".*
- **S2 — Botón "Crear tarea".** El botón "Crear tarea" (cuando un lote no tiene tareas en el Parte Diario) abre el modal de creación rápida. *User: "el boton de agregar tarea cuando un lote no tiene tarea en el parte diario no funciona".*
- **S3 — Mensaje de fallback amigable.** El aviso de clima sin datos es claro para el usuario final, no técnico ("Fallback: sin datos de API" / "Motivo: Fallback: sin datos de clima" son poco explicativos). *User: "me parece poco explicativa para un usuario final".*
- **S4 — Día caído sin tarea.** Si se marca "día caído", NO se exige seleccionar una tarea del lote. *User: "si marco dia caido igual me exige una tarea".*
- **S5 — Cargas por nombre.** En "crear cargas" el lote se elige por nombre/propietario, no por ID. *User: "me permite elegir los lotes por id en lugar de por nombre".*
- **S6 — Override de finde se mantiene (regresión).** Confirmar explícitamente para trabajar sáb/dom. Sin cambio de código; test de regresión que fija el comportamiento. *User: "se mantenga el override".*
- **S7 — Proyección lun-vie.** La proyección mensual asume producción solo de lunes a viernes. *User: "el sistema me tiene que proyectar que voy a producir esas toneladas solo de lunes a viernes".*
- **S8 — Objetivo automático.** El objetivo mensual se calcula del punto de equilibrio (costos fijos ÷ margen), sin campo manual. *User eligió opción A.*
- **S9 — Alerta si corto.** El sistema avisa si la proyección mensual queda por debajo del objetivo. *User: "necesito que avise si me quedo corto".*
- **S10 — Suma real sin tocar (regresión).** El total real de toneladas producidas se mantiene sumando TODO (incluidos findes trabajados). *User: "necesito sumar todas las toneladas que produje" / "dejalo asi".*

## Tasks

- T1 (S1) — Carbon::setLocale('es') global + test de locale. Ruta: inline. ✅ commit 7564187.
- T2 (S2) — Mover flux:modal "Crear tarea rápida" fuera del x-show paso===3. Ruta: inline. ✅ commit fd485cc.
- T3 (S3) — Copy amigable de fallback (ClimaOperativoService, ClimaPersistenciaService, blade). Ruta: inline. ✅ commit fd485cc.
- T4 (S4) — Día caído no exige tarea (PartesDiarios::rules + validarPaso1). Ruta: inline. ✅ commit fd485cc.
- T5 (S5) — Cargas elige lote por nombre/propietario (cargas.blade.php). Ruta: inline. ✅ commit fd485cc.
- T6 (S6-S10) — Proyección global + objetivo automático + alerta; tests de regresión. Ruta: inline. ✅ commit ff26409.

## Log

- L1 (request original, verbatim): "cosas que vi: los dias en el dash principal se muestran en ingles no en espanol / los sabados y domingos no se pueden tomar como dias productivos porque justamente son dias no laborales / el boton de agregar tarea cuando un lote no tiene tarea en el parte diario no funciona / Fallback: sin datos de API ... me parece poco explicativa para un usuario final / si marco dia caido igual me exige una tarea / en la pantalla para crear cargas me permite elegir los lotes por id en lugar de por nombre / ... no podrias automatizar su deteccion y correccion?"
- L2 (2026-11-02): Decisiones — override de finde se mantiene; suma de toneladas real se mantiene (suma todo); proyección solo lun-vie; objetivo automático (punto de equilibrio); alcance GLOBAL (no por lote); alerta si corto; desglose por lote diferido.
- L3 (2026-11-02): APP_LOCALE=en aunque toda la UI es español; fix = Carbon::setLocale('es') explícito (también arregla format('M Y') de reportes).
- L4 (2026-11-02): Cierre T1-T6. Suite 517 passed / 9 skipped. Proyección verificada contra base real (objetivo 1575 tn vs proyección 1050 tn, 21 días hábiles). gga review falla por privacy settings del workspace (modelo pago bloqueado), commits pasan sin review — pendiente que el user habilite el endpoint.
