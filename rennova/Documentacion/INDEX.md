# Documentación del Proyecto

Este índice reúne la documentación clave del proyecto y cómo navegarla.

## Visión General
- Resumen ejecutivo, arquitectura, módulos, flujos y BD: `../RESUMEN_PROYECTO.md`

## Guías de Uso
- Gestión de Mantenimientos (crear, completar, insumos, costos, stock): `./GUIA_MANTENIMIENTOS_USO.md`

## Referencias y Docs Existentes
- Sistema de Mantenimiento (docs previos): `../SISTEMA_MANTENIMIENTO_DOCS.md`
- Manual de Mantenimientos (docs previos): `../MANUAL_MANTENIMIENTOS.md`
- Instrucciones de Notificaciones Email (Mailtrap): `../INSTRUCCIONES_NOTIFICACIONES_EMAIL.md`
- Setup de Permisos (Spatie): `../SPATIE_PERMISSION_SETUP.md`

## Solución de Problemas
- Errores frecuentes (Livewire, modal, BD, correo): `./TROUBLESHOOTING.md`

## Comandos Útiles
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

## Siguientes Pasos Propuestos
- Filtros avanzados del listado de mantenimientos.
- Reportes de costos e insumos por maquinaria/periodo.
- Validación de stock al completar correctivo (bloquear o confirmar).
- Tests automatizados (Pest/PHPUnit) para flujos críticos.
