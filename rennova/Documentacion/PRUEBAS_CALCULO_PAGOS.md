# Pruebas Manuales - Sistema de Pagos

## Prueba en Tinker

```bash
cd d:\trabajo_final\rennova
php artisan tinker
```

### 1. Verificar empleado y su rol laboral
```php
$empleado = App\Models\Empleado::with('rolLaboral')->first();
$empleado->apellido . ', ' . $empleado->nombre;
$empleado->rolLaboral->jornal_diario ?? 'NO TIENE';
$empleado->rolLaboral->precio_tonelada ?? 'NO TIENE';
```

### 2. Probar cálculo de pagos
```php
// Calcular para un rango de fechas
$resultado = $empleado->calcularPagoRango('2025-10-01', '2025-10-31');
print_r($resultado);

// Ver estructura esperada:
// Array
// (
//     [cantidad_dias_caidos] => 0
//     [total_peso_neto] => 0.00
//     [valor_jornal] => 15000.00
//     [tarifa_fija_por_tonelada] => 8000.00
//     [total_pagar_jornales] => 0.00
//     [total_pagar_produccion] => 0.00
//     [total_pagar_final] => 0.00
// )
```

### 3. Verificar relaciones (después de crear partes con empleados)
```php
// Ver cargas asignadas a un empleado
$empleado->cargas()->count();
$empleado->cargas()->with('parteDiario')->get();

// Ver partes diarios (días caídos) donde trabajó
$empleado->partesDiarios()->where('es_dia_caido', true)->count();
$empleado->partesDiarios()->get();
```

### 4. Escenario completo simulado

**NOTA**: Para probar completamente, necesitas primero crear un ParteDiario desde la UI o manualmente:

```php
// 1. Crear un parte de producción (no día caído)
$parte = App\Models\ParteDiario::create([
    'id_lote' => 1,
    'fecha' => '2025-10-15',
    'es_dia_caido' => false,
    'observaciones' => 'Parte de prueba',
    'activo' => true
]);

// 2. Crear una carga relacionada
$carga = App\Models\Carga::create([
    'id_lote' => 1,
    'id_categoria_madera' => 1,
    'id_chofer' => 1,
    'id_parte_diario' => $parte->id_parte_diario,
    'ticket' => 'TEST-001',
    'peso_bruto' => 30,
    'tara' => 5,
    'peso_neto' => 25,
    'destino' => 'Aserradero XYZ',
    'fecha_carga' => '2025-10-15'
]);

// 3. Asignar empleados a la carga (2 empleados para probar división)
$empleado1 = App\Models\Empleado::first();
$empleado2 = App\Models\Empleado::skip(1)->first();

$carga->empleados()->attach([$empleado1->id_empleado, $empleado2->id_empleado]);

// 4. Calcular pago para empleado1
$resultado = $empleado1->calcularPagoRango('2025-10-01', '2025-10-31');
print_r($resultado);

// Debería mostrar:
// total_peso_neto => 12.5 (25 / 2 empleados)
// total_pagar_produccion => 100000 (12.5 * 8000)

// 5. Verificar que empleado2 tiene el mismo cálculo
$resultado2 = $empleado2->calcularPagoRango('2025-10-01', '2025-10-31');
$resultado2['total_peso_neto']; // Debe ser 12.5
```

### 5. Escenario de día caído

```php
// 1. Crear parte día caído
$parteCaido = App\Models\ParteDiario::create([
    'id_lote' => 1,
    'fecha' => '2025-10-20',
    'es_dia_caido' => true,
    'observaciones' => 'Lluvia - Día caído',
    'activo' => true
]);

// 2. Asignar empleados que trabajaron ese día
$empleado1 = App\Models\Empleado::first();
$empleado2 = App\Models\Empleado::skip(1)->first();

$parteCaido->empleados()->attach([$empleado1->id_empleado, $empleado2->id_empleado]);

// 3. Calcular pago
$resultado = $empleado1->calcularPagoRango('2025-10-01', '2025-10-31');
print_r($resultado);

// Debería mostrar:
// cantidad_dias_caidos => 1
// total_pagar_jornales => 15000 (1 día * jornal_diario)
```

## Verificación de Datos

### Ver todas las asignaciones actuales
```php
// Cargas con empleados
DB::table('carga_empleado')->get();

// Partes con empleados (días caídos)
DB::table('parte_diario_empleado')->get();
```

### Limpiar datos de prueba
```php
// Eliminar asignaciones
DB::table('carga_empleado')->truncate();
DB::table('parte_diario_empleado')->truncate();

// Eliminar partes de prueba
App\Models\ParteDiario::where('observaciones', 'LIKE', '%prueba%')->delete();
App\Models\Carga::where('ticket', 'LIKE', 'TEST-%')->delete();
```

## Casos a Probar

- [ ] Empleado con solo días caídos
- [ ] Empleado con solo producción
- [ ] Empleado con ambos (mixto)
- [ ] Empleado con múltiples cargas en un día
- [ ] Carga con 1 solo empleado (peso completo)
- [ ] Carga con múltiples empleados (peso dividido)
- [ ] Rango sin actividad (debe dar todo en 0)
- [ ] Empleado sin rol laboral (debe dar 0 en tarifas/jornales)
