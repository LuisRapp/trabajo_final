# Sistema de Cálculo de Costos - Parte Diario

## Resumen

Sistema completo para calcular y almacenar el desglose de costos operacionales diarios en la gestión forestal.

---

## Componentes Implementados

### 1. Migración de Base de Datos
**Archivo**: `database/migrations/2025_12_03_141027_add_costos_fields_to_parte_diarios_table.php`

Agrega campos a `parte_diarios`:
- `costo_mano_obra` (decimal 12,2): Sueldos/jornales del día
- `costo_insumos` (decimal 12,2): Consumo de insumos
- `costo_maquinaria` (decimal 12,2): Alquiler + mantenimientos
- `costo_total_dia` (decimal 12,2): Suma total
- `costo_unitario_calculado` (decimal 12,2): Costo por tonelada

**Ejecutar**:
```bash
php artisan migrate
```

---

### 2. Trait Reutilizable
**Archivo**: `app/Models/Traits/CalculaCostosLaborales.php`

#### Método: `calcularCostoDia($fecha, $esDiaCaido, $cargasDelDia)`

**Parámetros**:
- `$fecha`: Fecha del cálculo
- `$esDiaCaido`: Boolean - true si es día caído
- `$cargasDelDia`: Collection de cargas donde participó el empleado

**Retorna**: `float` - Costo del empleado para ese día

**Lógica**:
1. **Día Caído**: Retorna `jornal_diario` del `HistoricoRolLaboral` vigente
2. **Día Producción**: 
   - Suma toneladas asignadas al empleado (peso_neto / cantidad_empleados_por_carga)
   - Multiplica por `precio_tonelada` del rol
   - Convierte de kg a toneladas automáticamente

**Uso**:
```php
use App\Models\Traits\CalculaCostosLaborales;

class Empleado extends Model {
    use CalculaCostosLaborales;
    
    // ...
}

// En código
$costo = $empleado->calcularCostoDia($fecha, false, $cargas);
```

---

### 3. Método en Modelo ParteDiario
**Archivo**: `app/Models/ParteDiario.php`

#### Método: `calcularYGuardarCostos()`

Calcula y guarda automáticamente todos los costos del parte.

**Proceso**:

#### A. Costo Mano de Obra
```php
foreach ($this->empleados as $empleado) {
    $cargasDelEmpleado = Carga::whereDate('fecha_carga', $this->fecha)
        ->whereHas('empleados', fn($q) => $q->where('empleados.id_empleado', $empleado->id_empleado))
        ->with('empleados')
        ->get();
    
    $costoEmpleado = $empleado->calcularCostoDia($this->fecha, $this->es_dia_caido, $cargasDelEmpleado);
    $costoManoObra += $costoEmpleado;
}
```

#### B. Costo Insumos
```php
$movimientos = MovimientoStock::where('tipo', 'salida')
    ->whereDate('fecha', $this->fecha)
    ->where('motivo', 'LIKE', 'Parte Diario #' . $this->id_parte_diario . '%')
    ->get();

foreach ($movimientos as $mov) {
    $costoInsumos += $mov->costo_total_movimiento ?? ($mov->cantidad * $mov->precio_unitario);
}
```

#### C. Costo Maquinaria

**C1. Alquiler por Destajo**:
```php
foreach ($maquinariasUsadas as $maq) {
    if ($maq->es_alquilada) {
        $precioAlquilerPorTon = $maq->tipoMaquinaria->precio_alquiler_destajo;
        $costoMaquinaria += $totalToneladas * $precioAlquilerPorTon;
    }
}
```

**C2. Mantenimientos Completados**:
```php
$mantenimientos = Mantenimiento::whereIn('id_maquinaria', $idsMaquinarias)
    ->where('estado', 'completado')
    ->whereDate('fecha_fin', $this->fecha)
    ->get();

foreach ($mantenimientos as $mant) {
    $costoMaquinaria += $mant->costo_total;
}
```

#### D. Guardado
```php
$this->updateQuietly([
    'costo_mano_obra' => round($costoManoObra, 2),
    'costo_insumos' => round($costoInsumos, 2),
    'costo_maquinaria' => round($costoMaquinaria, 2),
    'costo_total_dia' => round($costoTotalDia, 2),
    'costo_unitario_calculado' => $costoUnitario
]);
```

---

### 4. Integración Automática
**Archivo**: `app/Http/Livewire/PartesDiarios.php`

En el método `guardar()`, después del commit:
```php
\DB::commit();

// Calcular y guardar costos del parte diario
try {
    $parteDiario->calcularYGuardarCostos();
} catch (\Exception $e) {
    \Log::error('Error al calcular costos del parte diario', [
        'parte_id' => $parteDiario->id_parte_diario,
        'error' => $e->getMessage()
    ]);
    // No lanzar excepción para no bloquear el guardado del parte
}
```

**Características**:
- Ejecuta automáticamente al guardar/editar un parte
- No bloquea el guardado si falla el cálculo (solo registra error)
- Usa `try-catch` para robustez

---

## Casos de Uso

### Caso 1: Parte Diario de Producción
```
Empleados: 2 (Juan, Pedro)
Cargas: 1 carga de 10 toneladas
Juan trabaja en la carga → 5 ton
Pedro trabaja en la carga → 5 ton

Cálculo:
- Juan: 5 ton * $100/ton = $500
- Pedro: 5 ton * $100/ton = $500
- Costo Mano de Obra: $1000
```

### Caso 2: Día Caído
```
Empleados: 3 (Ana, Luis, Carlos)
Jornal: $150/día

Cálculo:
- Ana: $150
- Luis: $150
- Carlos: $150
- Costo Mano de Obra: $450
```

### Caso 3: Costo Completo
```
Mano de Obra: $1000
Insumos (combustible): $200
Maquinaria:
  - Alquiler: 10 ton * $50/ton = $500
  - Mantenimiento completado: $300
  Total Maquinaria: $800

COSTO TOTAL DÍA: $2000
Toneladas: 10
COSTO UNITARIO: $200/ton
```

---

## Pruebas

### Script de Validación
**Archivo**: `test_calculo_costos.php`

```bash
php test_calculo_costos.php
```

**Verifica**:
- Búsqueda de parte diario con cargas
- Ejecución del cálculo
- Validación de suma de componentes
- Desglose por empleado
- Performance (tiempo de ejecución)

**Salida Esperada**:
```
=== TEST DE CÁLCULO DE COSTOS - PARTE DIARIO ===

✅ Parte Diario encontrado:
   ID: 1
   Fecha: 2025-11-10
   ...

✅ Cálculo completado en 39.21 ms

--- RESULTADO DEL CÁLCULO ---
   Costo Mano de Obra: $XXX.XX
   Costo Insumos: $XXX.XX
   Costo Maquinaria: $XXX.XX
   --------------------------------
   COSTO TOTAL DÍA: $XXX.XX
   Costo Unitario ($/ton): $XXX.XX

✅ VALIDACIÓN: La suma de componentes coincide con el total
✅ Test completado exitosamente
```

---

## Consideraciones Técnicas

### Conversión de Unidades
- `peso_neto` en BD: **kilogramos**
- Cálculos internos: **toneladas** (kg / 1000)
- Todos los costos: redondeados a 2 decimales

### Histórico de Tarifas
El trait consulta `HistoricoRolLaboral` para obtener tarifas vigentes en la fecha del parte:
- `jornal_diario`: Para días caídos
- `precio_tonelada`: Para días de producción

### Performance
- Cálculo típico: ~40ms
- Usa `with()` para eager loading
- `updateQuietly()` evita eventos recursivos

### Manejo de Errores
- Try-catch en integración Livewire
- Log de errores sin bloquear guardado
- Validaciones de datos nulos/vacíos

---

## Reportes Sugeridos

Con estos datos almacenados, se pueden crear reportes de:

### 1. Costo por Período
```sql
SELECT 
    DATE_TRUNC('month', fecha) as mes,
    SUM(costo_total_dia) as costo_total_mes,
    AVG(costo_unitario_calculado) as costo_promedio_tonelada
FROM parte_diarios
WHERE fecha BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY DATE_TRUNC('month', fecha)
ORDER BY mes;
```

### 2. Desglose de Costos
```sql
SELECT 
    SUM(costo_mano_obra) as total_mano_obra,
    SUM(costo_insumos) as total_insumos,
    SUM(costo_maquinaria) as total_maquinaria,
    SUM(costo_total_dia) as costo_total
FROM parte_diarios
WHERE fecha BETWEEN :inicio AND :fin;
```

### 3. Rentabilidad por Lote
```sql
SELECT 
    l.nombre as lote,
    SUM(pd.costo_total_dia) as costo_total,
    SUM(c.peso_neto) / 1000 as toneladas_totales,
    AVG(pd.costo_unitario_calculado) as costo_promedio_ton
FROM parte_diarios pd
JOIN lotes l ON pd.id_lote = l.id_lote
JOIN cargas c ON c.id_parte_diario = pd.id_parte_diario
GROUP BY l.id_lote, l.nombre
ORDER BY costo_total DESC;
```

---

## Mantenimiento Futuro

### Agregar Nuevos Componentes de Costo
1. Agregar campo a `parte_diarios`
2. Actualizar `calcularYGuardarCostos()` con nueva sección
3. Actualizar `costo_total_dia` con nuevo componente

### Cambiar Lógica de Cálculo
Modificar solo el Trait o el método en `ParteDiario` según corresponda:
- **Cambios en empleados**: Editar Trait
- **Cambios en otros costos**: Editar `calcularYGuardarCostos()`

### Recalcular Costos Históricos
```php
$partes = ParteDiario::all();
foreach ($partes as $parte) {
    $parte->calcularYGuardarCostos();
}
```

---

## Conclusión

Sistema completo y robusto para:
- ✅ Calcular costos operacionales diarios
- ✅ Reutilizar lógica de liquidación de sueldos
- ✅ Almacenar desglose detallado
- ✅ Generar reportes de rentabilidad
- ✅ Integración automática en flujo existente
- ✅ Performance optimizado (~40ms)
- ✅ Manejo de errores robusto
