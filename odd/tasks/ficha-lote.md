# Ficha del lote: un solo lugar para el ciclo lote -> tareas -> recursos

Rama: `main` (flujo del proyecto; el deploy del gate corre sobre main).
Runner: `php artisan test`.

## Specs

S1. `/lotes/{loteId}` muestra la ficha del lote: propietario, especie, ubicación,
superficie y estado.
S2. La ficha muestra el ciclo en tres pasos (datos, tareas, recursos) con el paso
actual marcado y los anteriores como completados.
S3. La ficha tiene **una sola acción principal**, según el estado:
- sin tareas: "Planificar tareas".
- con tareas y sin recursos: "Ver sugerencias" (y "Asignar manualmente" como secundaria).
- con recursos: "Finalizar lote".
- cerrado o de baja: ninguna acción de escritura.
S4. La ficha lista las tareas planificadas (tipo, superficie, estado) y enlaza a editarlas.
S5. La ficha lista los recursos asignados (empleados con su rol, maquinarias con su tipo)
o el vacío correspondiente.
S6. El listado de lotes entra a la ficha con "Abrir"; la fila ya no expone "Propuestas"
ni "Finalizar" (esas operaciones viven en el lote).
S7. Al guardar tareas se vuelve a la ficha, no a propuestas.
S8. Desde tareas y desde sugerencias se vuelve a la ficha.
S9. Textos en lenguaje de usuario: la ficha no muestra "histórico", "fallback" ni
números de propuesta.
S10. Permisos: la ficha requiere `ver-lotes`; "Finalizar lote" respeta `editar-lotes`.

## Tasks

| ID | Specs | Ruta | Estado | Evidencia |
|----|-------|------|--------|-----------|
| T1 | S1, S2, S3 | inline | hecho | `FichaLote` + `/lotes/{id}`; verificado en lote activo y cerrado |
| T2 | S4 | inline | hecho | tabla de tareas con tipo, superficie y estado |
| T3 | S5 | inline | hecho | empleados con rol y maquinarias con tipo |
| T4 | S6 | inline | hecho | listado con "Abrir"; sin "Propuestas" ni "Finalizar" en la fila |
| T5 | S7, S8 | inline | hecho | guardar tareas vuelve a la ficha; tareas y sugerencias vuelven al lote |
| T6 | S9 | inline | hecho | sin "historico", "fallback" ni numeros de propuesta |
| T7 | S10 | inline | hecho | ruta con `ver-lotes`; finalizar con `editar-lotes` |

## Log

L1 (verbatim): "lo recomendable seria arreglar eso, es parte del mvp y ahora esta mal".
L1b (verbatim): "necesito que lo arregles como decis vos hacer la ficha del lote ahora
—implica tocar rutas y navegación, y el gate se pausa unos días".
