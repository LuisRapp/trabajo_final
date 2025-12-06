# Sistema de Mantenimiento Preventivo - Documentación

## 📋 RESUMEN DE IMPLEMENTACIÓN

Se ha implementado un sistema completo de gestión de mantenimientos preventivos y correctivos basado en odómetro de toneladas procesadas.

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Nuevos Campos Agregados:

1. **maquinarias**
   - `toneladas_acumuladas` (DECIMAL 12,2): Odómetro que nunca se resetea

2. **tipo_maquinarias**
   - `umbral_toneladas` (DECIMAL 10,2): Umbral para generar mantenimiento preventivo

3. **mantenimientos**
   - `toneladas_snapshot` (DECIMAL 12,2): Snapshot del odómetro al momento del mantenimiento
   - `costo_mano_obra` (DECIMAL 10,2): Costo de mano de obra

4. **mantenimiento_insumos**
   - `costo_unitario` (DECIMAL 10,2): Costo del insumo al momento del uso
   - `subtotal` (DECIMAL 10,2): cantidad × costo_unitario

### Nueva Tabla:

**kit_mantenimiento_preventivo**
```sql
- id_kit (PK)
- id_tipo_maquinaria (FK)
- id_insumo (FK)
- cantidad_requerida
- es_obligatorio
- timestamps
```

---

## ⚙️ COMPONENTES IMPLEMENTADOS

### 1. Evento y Listener (Actualización Inmediata)

**Evento:** `App\Events\CargaRegistrada`
- Se dispara cuando se registra una carga en el parte diario
- Contiene: carga, maquinariaId, toneladas

**Listener:** `App\Listeners\ActualizarOdometroMaquina`
- Actualiza automáticamente `toneladas_acumuladas` de la maquinaria
- Registrado en `App\Providers\AppServiceProvider`

**Uso en el código:**
```php
use App\Events\CargaRegistrada;

// Al guardar una carga en ParteDiarioController
event(new CargaRegistrada($carga, $maquinariaId, $toneladas));
```

---

### 2. Comando Programado (Verificación Diaria)

**Comando:** `App\Console\Commands\CheckMantenimientoUmbrales`

**Signature:** `mantenimiento:check-umbrales`

**Programación:** Diario a las 2:00 AM (configurado en `routes/console.php`)

**Qué hace:**
1. Revisa todas las maquinarias activas
2. Compara `toneladas_acumuladas` vs último `toneladas_snapshot`
3. Si supera el `umbral_toneladas`:
   - Crea orden de mantenimiento en estado "programado"
   - Verifica stock de insumos del kit
   - Notifica si falta stock (por ahora en logs, pendiente email)

**Ejecución manual:**
```bash
php artisan mantenimiento:check-umbrales
```

**Para producción (Cron):**
```bash
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

---

### 3. Servicio de Mantenimiento

**Clase:** `App\Services\MantenimientoService`

**Métodos:**

**a) `verificarStockParaAprobacion($mantenimientoId)`**
- Verifica si hay stock suficiente del kit preventivo
- Retorna:
  ```php
  [
    'puede_aprobar' => true|false,
    'insuficientes' => [...],  // Array de insumos sin stock
    'kit' => [...]              // Kit completo
  ]
  ```

**b) `completarMantenimiento($mantenimientoId, $insumos, $costoManoObra)`**
- Descuenta insumos del stock
- Calcula costo total: SUM(subtotales) + mano_obra
- Toma snapshot del odómetro actual
- Cambia estado a "completado"

**c) `obtenerKitPreventivo($tipoMaquinariaId)`**
- Obtiene insumos del kit preventivo para un tipo de maquinaria

---

### 4. Controlador con Métodos Críticos

**Clase:** `App\Http\Controllers\MantenimientoController`

#### **Método: `approve($id)`**

**Ruta sugerida:** `POST /mantenimientos/{id}/approve`

**Función:** Aprobar una orden de mantenimiento

**Flujo:**
1. Verifica que esté en estado "programado"
2. **BLOQUEA** si no hay stock suficiente
3. Si hay stock, cambia estado a "en curso"

**Ejemplo de uso (AJAX):**
```javascript
fetch(`/mantenimientos/${id}/approve`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        alert('Orden aprobada');
    } else {
        alert('No hay stock: ' + JSON.stringify(data.insumos_insuficientes));
    }
});
```

#### **Método: `complete(Request $request, $id)`**

**Ruta sugerida:** `POST /mantenimientos/{id}/complete`

**Request esperado:**
```json
{
  "insumos": [
    {
      "id_insumo": 1,
      "cantidad_utilizada": 2.5,
      "costo_unitario": 1500.00  // Opcional, toma precio_compra si no viene
    },
    {
      "id_insumo": 3,
      "cantidad_utilizada": 1.0
    }
  ],
  "costo_mano_obra": 5000.00
}
```

**Función:** Completar un mantenimiento

**Flujo:**
1. Valida datos de entrada
2. Descuenta cada insumo del stock (si hay disponible)
3. Registra en `mantenimiento_insumos` con costos
4. Calcula `costo_total = SUM(subtotales) + costo_mano_obra`
5. Actualiza `toneladas_snapshot` con el odómetro actual
6. Cambia estado a "completado"

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Mantenimiento completado exitosamente",
  "mantenimiento": {...},
  "costo_total": 12500.00
}
```

---

## 📦 MODELOS ACTUALIZADOS

### Nuevos Modelos:
- `App\Models\KitMantenimientoPreventivo`
- `App\Models\MantenimientoInsumo`

### Modelos Modificados:
- `Maquinaria`: Agregado `toneladas_acumuladas` en fillable
- `TipoMaquinaria`: Agregado `umbral_toneladas` y relación `kitsPreventivos()`
- `Mantenimiento`: Agregados `toneladas_snapshot` y `costo_mano_obra`

### Auditoría:
Todos los modelos implementan `OwenIt\Auditing\Auditable` para trazabilidad automática.

---

## 🔄 FLUJO COMPLETO DEL SISTEMA

### 1. Registro de Producción (ParteDiario)
```
Usuario registra carga
      ↓
Se guarda en BD
      ↓
event(CargaRegistrada) dispara
      ↓
Listener actualiza toneladas_acumuladas
```

### 2. Verificación Automática (Diaria 2:00 AM)
```
Comando CheckMantenimientoUmbrales ejecuta
      ↓
Recorre maquinarias activas
      ↓
Calcula: toneladas_acumuladas - último_snapshot
      ↓
¿Supera umbral? → SÍ
      ↓
Crea orden "programado"
      ↓
Verifica stock del kit
      ↓
Notifica si falta stock (Log + Email pendiente)
```

### 3. Aprobación de Orden
```
Admin intenta aprobar
      ↓
Controller llama verificarStockParaAprobacion()
      ↓
¿Hay stock suficiente?
      ↓ NO → BLOQUEA con mensaje
      ↓ SÍ → Estado = "en curso"
```

### 4. Completar Mantenimiento
```
Técnico registra insumos usados + mano de obra
      ↓
Controller llama completarMantenimiento()
      ↓
Descuenta stock de cada insumo
      ↓
Calcula costo_total
      ↓
Toma snapshot del odómetro
      ↓
Estado = "completado"
```

---

## 🧪 PRUEBAS SUGERIDAS

### 1. Probar Actualización de Odómetro
```php
// En ParteDiarioController o donde guardes cargas
$carga = Carga::create([...]);
$maquinaria = Maquinaria::find($maquinariaId);
$toneladas = $carga->peso_neto / 1000;

event(new \App\Events\CargaRegistrada($carga, $maquinariaId, $toneladas));

// Verificar que toneladas_acumuladas aumentó
dd($maquinaria->fresh()->toneladas_acumuladas);
```

### 2. Probar Comando de Umbrales
```bash
# Primero configurar umbral en tipo_maquinaria
UPDATE tipo_maquinarias SET umbral_toneladas = 100 WHERE id_tipo_maquinaria = 1;

# Ejecutar comando
php artisan mantenimiento:check-umbrales

# Revisar órdenes creadas
SELECT * FROM mantenimientos WHERE estado = 'programado' ORDER BY created_at DESC;
```

### 3. Probar Aprobación con Stock Insuficiente
```bash
# Reducir stock de algún insumo del kit
UPDATE insumos SET stock_disponible = 0 WHERE id_insumo = 1;

# Intentar aprobar (debe fallar)
POST /mantenimientos/1/approve
```

### 4. Probar Completar Mantenimiento
```bash
POST /mantenimientos/1/complete
{
  "insumos": [
    {"id_insumo": 1, "cantidad_utilizada": 2, "costo_unitario": 1000},
    {"id_insumo": 2, "cantidad_utilizada": 1}
  ],
  "costo_mano_obra": 3000
}

# Verificar:
# 1. Estado = completado
# 2. toneladas_snapshot guardado
# 3. costo_total calculado
# 4. Stock descontado
```

---

## 🚨 PENDIENTE / TODO

1. **Notificaciones por Email:**
   - Implementar envío cuando el comando detecte falta de stock
   - Notificar cuando se crea orden automática
   - Usar librería de email a definir

2. **Rutas:**
   - Agregar en `routes/web.php`:
     ```php
     Route::post('mantenimientos/{id}/approve', [MantenimientoController::class, 'approve']);
     Route::post('mantenimientos/{id}/complete', [MantenimientoController::class, 'complete']);
     ```

3. **Interfaz de Usuario:**
   - Vista para configurar kits preventivos por tipo de maquinaria
   - Vista para aprobar órdenes con validación de stock
   - Vista para completar mantenimiento con formulario de insumos

4. **Compras Automáticas:**
   - Definir si se generan órdenes de compra automáticas o solo notificación

5. **Reportes:**
   - Historial de costos por maquinaria
   - Proyección de próximos mantenimientos
   - Dashboard de órdenes pendientes

---

## 📚 REFERENCIAS

- **Auditoría:** `owen-it/laravel-auditing` (ya instalado)
- **Eventos Laravel:** https://laravel.com/docs/events
- **Task Scheduling:** https://laravel.com/docs/scheduling
- **Service Pattern:** Separación de lógica de negocio del controlador

---

## 🛠️ COMANDOS ÚTILES

```bash
# Ejecutar migraciones
php artisan migrate

# Ver estado de migraciones
php artisan migrate:status

# Ejecutar comando manual
php artisan mantenimiento:check-umbrales

# Ver logs
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan config:clear
php artisan cache:clear
```

---

**Implementado el:** 10 de noviembre de 2025
**Versión:** 1.0
**Estado:** ✅ Base implementada - Pendiente UI y notificaciones
