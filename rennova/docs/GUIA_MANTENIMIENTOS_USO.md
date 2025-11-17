# Guía de Uso: Mantenimientos

Esta guía describe el flujo completo de creación y finalización de órdenes de mantenimiento, incluyendo el uso de insumos y cálculo de costos.

## 1. Crear una Orden
1) Ir a `Mantenimientos` → pestaña "Nuevo Mantenimiento".
2) Completar:
   - Maquinaria: elegir una operativa.
   - Tipo: `Preventivo` o `Correctivo`.
   - Fecha de inicio.
   - Estado inicial: `Programado` o `En curso`.
3) Guardar.

Notas:
- Si el tipo es `Preventivo` y existe kit para la maquinaria, se muestra la lista de insumos configurados (solo informativa en la creación).

## 2. Listado y Acciones
- Pestaña "Listado de Mantenimientos" muestra todas las órdenes.
- Acciones:
  - Completar (si no está completada).
  - Editar (si no está completada).
  - Eliminar.

## 3. Completar una Orden (Modal)
Al pulsar "Completar":
- Se abre un modal (overlay) con:
  - Fecha de finalización (requerida).
  - Costo total (opcional): se sumará automáticamente el costo de insumos correctivos.
  - Para `Correctivo`: bloque para agregar insumos usados.

### 3.1 Insumos (solo Correctivo)
- Agregar filas según necesidad.
- Campos por insumo: `Insumo`, `Cantidad`, `Precio Unitario`.
- Al confirmar:
  - Se generan movimientos de stock (tipo `salida`) por cada insumo.
  - Se registra en `mantenimiento_insumos` con `cantidad_utilizada`, `costo_unitario` y `subtotal`.

### 3.2 Cálculo del Costo Total
- Si ingresás un "Costo total" manual, se usará como base.
- Se suman los subtotales de cada insumo correctivo (`cantidad × precio_unitario`).
- Resultado final se guarda en `mantenimientos.costo_total`.

## 4. Estados
- `Programado`: creado y pendiente de ejecución.
- `En curso`: ejecución iniciada.
- `Completado`: se finaliza desde el modal.

## 5. Buenas Prácticas
- Verificar que la maquinaria esté en estado `operativa` para que entre en lógica de umbrales.
- Mantener actualizado el kit preventivo por maquinaria.
- Registrar insumos correctivos con cantidades y precios reales.

## 6. Errores Frecuentes
- Livewire: si ves "MultipleRootElementsDetected", la vista debe tener un único contenedor raíz.
- BD: columnas correctas en `mantenimiento_insumos`: `cantidad_utilizada`, `costo_unitario`, `subtotal`.
- Correo: Mailtrap en sandbox limita tasa; revisar credenciales en `.env`.

## 7. Comandos Útiles
```powershell
php artisan view:clear
php artisan serve
Get-Content .\storage\logs\laravel.log -Tail 100 -Wait
```
