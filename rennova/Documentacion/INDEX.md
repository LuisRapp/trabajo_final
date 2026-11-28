# Documentacion del Proyecto

Ultima actualizacion: 27 de noviembre de 2026.
Indice curado de la documentacion vigente del sistema.

## Vision General
- Resumen ejecutivo, arquitectura, modulos, flujos y BD: `./RESUMEN_PROYECTO.md`
- Arquitectura de asignacion automatica: `./ARQUITECTURA_ASIGNACION_AUTOMATICA_RECURSOS.md`

### Documentos de catedra (canonicos, en la raiz del repo)

- Requisitos: `../1-Requisitos.docx.md`
- Analisis: `../2.-Análisis.docx.md`
- Diseño: `../3.-Diseño.doc.md`

## Guias de Uso
- Recomendaciones por lote (planificacion y parte diario): `./GUIA_RECOMENDACIONES_POR_LOTE.md`
- Liquidacion de pagos: `./GUIA_LIQUIDACION_PAGOS.md`

## Scheduler
- Tareas programadas: `./TAREAS_PROGRAMADAS_SCHEDULER.md`

## Costos y Pagos
- Sistema de calculo de costos (parte diario): `./SISTEMA_CALCULO_COSTOS.md`
- Calculo de pagos a empleados: `./CALCULO_PAGOS_EMPLEADOS.md`

## Notificaciones
- Notificaciones internas: `./SISTEMA_NOTIFICACIONES_INTERNAS.md`
- Email: `./INSTRUCCIONES_NOTIFICACIONES_EMAIL.md`

## Permisos
- Setup Spatie Permission: `./SPATIE_PERMISSION_SETUP.md`

## Optimizacion y UI
- Optimizaciones avanzadas UI: `./OPTIMIZACIONES_AVANZADAS_UI.md`
- Optimizacion de rendimiento: `./OPTIMIZACION_RENDIMIENTO.md`

## Testing
- Suite: `php artisan test` (Pest, SQLite en memoria).
- Estilo: `vendor/bin/pint --test`.

## Despliegue y deuda
- Guia de despliegue en produccion: `./GUIA_DESPLIEGUE_PRODUCCION.md`
- Deuda tecnica conocida: `./DEUDA_TECNICA.md`

## Troubleshooting
- Errores frecuentes (Livewire, modal, BD, correo): `./TROUBLESHOOTING.md`

## Comandos utiles

```bash
# Iniciar servidor local
php artisan serve

# Limpiar vistas compiladas
php artisan view:clear

# Ver logs
tail -f storage/logs/laravel.log
```
