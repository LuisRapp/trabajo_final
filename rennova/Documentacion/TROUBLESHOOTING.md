# Troubleshooting

## Livewire
- Error: `MultipleRootElementsDetected`
  - Causa: la vista del componente tiene más de un elemento raíz.
  - Fix: envolver todo en un único `<div>`.

- El modal no aparece
  - Verificar que `abrirModalCompletar()` se ejecute (puedes despachar un evento y ver log en consola).
  - Si hay log pero no se ve, revisar CSS/z-index. Usar overlay propio evita depender de Bootstrap JS.

## Modal de Finalización
- Botón "Procesando..." no cierra
  - Ver logs en `storage/logs/laravel.log`.
  - Validar fecha (`fecha_fin` ≥ `fecha_inicio`).
  - Revisar excepciones de BD y nombres de columna.

## Base de Datos
- `mantenimiento_insumos` columnas:
  - `cantidad_utilizada`, `costo_unitario`, `subtotal` (no `cantidad_usada`, ni `precio_unitario`).
- Movimientos de stock (salida) por cada insumo correctivo.

## Correo (Mailtrap)
- Si bloquea el segundo correo: límite de sandbox. Confirmar configuración SMTP en `.env`.

## Comandos
```powershell
php artisan view:clear
php artisan serve
Get-Content .\storage\logs\laravel.log -Tail 100 -Wait
```
