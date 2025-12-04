# Guía Rápida: UI de Liquidación de Pagos

## Acceso
**Ruta**: http://localhost/liquidacion-pagos  
**Ubicación en menú**: Sidebar → Empleados → "Liquidación de Pagos"

## Flujo de Uso

### Paso 1: Seleccionar Empleado y Período
1. Abrir `/liquidacion-pagos`
2. Seleccionar empleado del dropdown
3. Ajustar fechas (por defecto: mes actual)
4. Clic en **"Calcular"**

### Paso 2: Revisar Cálculo
El sistema muestra dos paneles:

**Panel Izquierdo - "Detalle del Cálculo"** (solo lectura):
```
✓ Días caídos trabajados: 5 días
✓ Jornal diario: $15,000.00
✓ Subtotal jornales: $75,000.00
─────────────────────────
✓ Toneladas producidas: 25.50 ton
✓ Tarifa por tonelada: $8,000.00
✓ Subtotal producción: $204,000.00
─────────────────────────
✓ TOTAL CALCULADO: $279,000.00
```

**Panel Derecho - "Datos del Recibo"** (editable):
- **Monto Bruto**: $279,000.00 (modificable)
- **Descuentos**: $0.00 (para adelantos, retenciones, etc.)
- **Monto Neto a Pagar**: $279,000.00 (se actualiza automáticamente)
- **Observaciones**: Texto prellenado editable

### Paso 3: Opciones del Admin

#### Opción A: Aceptar Cálculo
1. Revisar que los datos son correctos
2. Clic en **"Generar Recibo"** (botón verde)
3. Sistema crea el recibo automáticamente
4. Muestra confirmación con número de recibo

#### Opción B: Modificar Datos
1. Ajustar **Monto Bruto** si es necesario (ej: bonos adicionales)
2. Agregar **Descuentos** (adelantos, seguros, etc.)
3. El **Monto Neto** se recalcula en tiempo real
4. Modificar **Observaciones** si es necesario
5. Clic en **"Generar Recibo"**

#### Opción C: Cancelar
- Clic en **"Cancelar"** (botón gris)
- Vuelve al formulario de selección

### Paso 4: Después de Generar
- Se muestra pantalla de éxito con número de recibo
- Botón **"Nueva Liquidación"** para procesar otro empleado
- El recibo queda registrado en la tabla `recibos`

## Ejemplos de Uso

### Caso 1: Liquidación Normal
```
Empleado: García, Juan
Período: 01/10/2025 a 31/10/2025
Días caídos: 3
Producción: 18.5 ton
Total calculado: $213,000

→ Admin acepta → Genera recibo por $213,000
```

### Caso 2: Liquidación con Adelanto
```
Empleado: Pérez, María
Período: 01/10/2025 a 31/10/2025
Total calculado: $150,000
Adelantos del mes: $30,000

→ Admin modifica:
   - Monto bruto: $150,000
   - Descuentos: $30,000
   - Monto neto: $120,000
→ Genera recibo por $120,000 neto
```

### Caso 3: Liquidación con Bono
```
Empleado: López, Carlos
Período: 01/10/2025 a 31/10/2025
Total calculado: $180,000
Bono por desempeño: $20,000

→ Admin modifica:
   - Monto bruto: $200,000 (180k + 20k)
   - Descuentos: $0
   - Observaciones: "Incluye bono por desempeño"
→ Genera recibo por $200,000
```

## Validaciones Implementadas

✓ Empleado requerido  
✓ Fechas requeridas  
✓ Fecha fin debe ser >= fecha inicio  
✓ Monto bruto debe ser >= 0  
✓ Descuentos deben ser >= 0  
✓ Observaciones máximo 150 caracteres  

## Vista de Recibo Generado

El recibo creado tendrá:
```php
[
    'id_empleado' => <id>,
    'fecha_emision' => <hoy>,
    'monto_bruto' => <valor del form>,
    'descuentos' => <valor del form>,
    'monto' => <monto_bruto - descuentos>,
    'observaciones' => <texto del form>,
    'activo' => true
]
```

## Notas Importantes

1. **No se crean recibos automáticamente** al guardar Partes Diarios
2. Esta es la **única forma correcta** de generar recibos de pago
3. El cálculo es **automático** pero **revisable y ajustable**
4. Los montos se **redondean a 2 decimales**
5. El recibo queda **vinculado al empleado** en la BD

## Troubleshooting

**Problema**: "Total calculado es $0.00"  
**Causa**: No hay partes diarios registrados para ese empleado en el período  
**Solución**: Verificar que se hayan guardado partes diarios con ese empleado asignado

**Problema**: "Empleado no tiene jornal_diario"  
**Causa**: El rol laboral no tiene valores configurados  
**Solución**: Ir a Roles Laborales y configurar jornal_diario y precio_tonelada

**Problema**: "No aparecen las cargas en el cálculo"  
**Causa**: Las cargas no están asignadas al empleado en la tabla pivote  
**Solución**: Verificar que al guardar el parte diario se hayan seleccionado los empleados

## Capturas de Pantalla (Descripción)

1. **Selección**: Dropdown empleados + 2 campos fecha + botón Calcular
2. **Cálculo**: 2 columnas (Detalle read-only | Form editable)
3. **Confirmación**: Ícono check verde grande + mensaje + botón Nueva Liquidación
