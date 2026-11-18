# Documentacion del Proyecto

Ultima actualizacion: 8 de febrero de 2026.
Indice curado de la documentacion vigente del sistema.

## Vision General
- Resumen ejecutivo, arquitectura, modulos, flujos y BD: `./RESUMEN_PROYECTO.md`
- Arquitectura de asignacion automatica: `./ARQUITECTURA_ASIGNACION_AUTOMATICA_RECURSOS.md`

### Documentos de catedra (canonicos, en la raiz del repo)

Los tres documentos entregables de la catedra viven en la **raiz del repo** y son
la version vigente (alineada con el sistema post F0-F7):

- Requisitos: `../1-Requisitos.docx.md`
- Analisis: `../2.-Análisis.docx.md`
- Diseño: `../3.-Diseño.doc.md`

Las versiones viejas `DOCUMENTACION_PROYECTO_REQUISITOS.md`,
`DOCUMENTACION_PROYECTO_ANALISIS.md` y `DOCUMENTACION_PROYECTO_DISENO.md`
(de aca o de la raiz) quedaron **obsoletas**: no usar como base.

## Guias de Uso
- Gestion de mantenimientos (flujo y UI): `./GUIA_MANTENIMIENTOS_USO.md`
- Recomendaciones por lote (planificacion y parte diario): `./GUIA_RECOMENDACIONES_POR_LOTE.md`
- Liquidacion de pagos: `./GUIA_LIQUIDACION_PAGOS.md`

## Clima y Analisis
- Guia completa de API y decisiones: `./GUIA_API_CLIMA_Y_DECISIONES.md`
- Sistema de analisis climatico (comando y logica): `./SISTEMA_ANALISIS_CLIMATICO.md`
- Guia rapida de analisis climatico: `./README_ANALISIS_CLIMATICO.md`
- Resumen del motor de decisiones: `./README_CLIMA_DECISION_SERVICE.md`

## Mantenimientos (Tecnico)
- Proceso automatico y scheduler: `./PROCESO_AUTOMATICO_MANTENIMIENTO.md`
- Sistema de mantenimiento (referencia tecnica): `./SISTEMA_MANTENIMIENTO_DOCS.md`

## Costos y Pagos
- Sistema de calculo de costos (parte diario): `./SISTEMA_CALCULO_COSTOS.md`
- Calculo de pagos a empleados: `./CALCULO_PAGOS_EMPLEADOS.md`
- Pruebas de calculo de pagos: `./PRUEBAS_CALCULO_PAGOS.md`

## Notificaciones
- Notificaciones internas: `./SISTEMA_NOTIFICACIONES_INTERNAS.md`
- Email (Mailtrap): `./INSTRUCCIONES_NOTIFICACIONES_EMAIL.md`

## Permisos
- Setup Spatie Permission: `./SPATIE_PERMISSION_SETUP.md`

## Optimizacion y UI
- Optimizaciones avanzadas UI: `./OPTIMIZACIONES_AVANZADAS_UI.md`
- Optimizacion de rendimiento: `./OPTIMIZACION_RENDIMIENTO.md`

## Testing
- Indice de pruebas: `./INDICE_PRUEBAS.md`
- Caja blanca: `./PRUEBAS_CAJA_BLANCA.md`
- Caja negra (resultados): `./PRUEBAS_CAJA_NEGRA_RESULTADOS.md`
- Resultados detallados: `./RESULTADOS_PRUEBAS.md`

## Troubleshooting
- Errores frecuentes (Livewire, modal, BD, correo): `./TROUBLESHOOTING.md`

## Comandos Utiles
```powershell
# Iniciar servidor
php artisan serve

# Alternativa (servidor embebido PHP)
php -S 127.0.0.1:8000 -t public

# Limpiar vistas compiladas
php artisan view:clear

# Ver logs (PowerShell)
Get-Content .\storage\logs\laravel.log -Tail 100 -Wait
```
