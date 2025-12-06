# Sistema de Gestión de Mantenimientos - Guía de Uso

## 📋 Descripción General

Sistema completo de mantenimientos preventivos y correctivos basado en odómetro de toneladas procesadas. Incluye:

- ✅ Actualización automática de odómetro al registrar cargas
- ✅ Verificación diaria de umbrales (2:00 AM)
- ✅ Creación automática de órdenes preventivas
- ✅ Validación de stock antes de aprobar
- ✅ Notificaciones por email (Mailtrap)
- ✅ Auditoría completa con OwenIt\Auditing
- ✅ Interfaz Livewire con tabs y modales

## 🚀 Componentes Implementados

### Backend

1. **Migraciones** (5 archivos ejecutados exitosamente):
   - `add_fields_to_maquinarias_table`: Campo `toneladas_acumuladas`
   - `add_umbral_toneladas_to_tipo_maquinarias_table`: Umbral para preventivos
   - `create_kit_mantenimiento_preventivo_table`: Definición de kits
   - `add_fields_to_mantenimientos_table`: `toneladas_snapshot`, `costo_mano_obra`
   - `add_costo_fields_to_mantenimiento_insumos_table`: Costos unitarios y subtotales

2. **Modelos**:
   - `KitMantenimientoPreventivo`: Kits por tipo de maquinaria
   - `MantenimientoInsumo`: Relación many-to-many con costos
   - Auditoría habilitada en todos los modelos

3. **Event + Listener**:
   - `CargaRegistrada`: Evento cuando se registra una carga
   - `ActualizarOdometroMaquina`: Incrementa `toneladas_acumuladas`
   - **Pendiente**: Integrar en `ParteDiarioController`

4. **Comando Programado**:
   - `CheckMantenimientoUmbrales`: Ejecuta diariamente a las 2:00 AM
   - Compara `toneladas_acumuladas - ultimo_snapshot >= umbral`
   - Crea órdenes en estado `programado`
   - Verifica stock y envía notificaciones

5. **Servicio de Negocio**:
   - `MantenimientoService`: Lógica centralizada
   - `verificarStockParaAprobacion()`: Valida disponibilidad
   - `completarMantenimiento()`: Transacción completa
   - `obtenerKitPreventivo()`: Carga items del kit

6. **Controlador**:
   - `MantenimientoController@approve`: POST para aprobar órdenes
   - `MantenimientoController@complete`: POST para completar con costos

7. **Notificaciones**:
   - `MantenimientoCreado`: Email cuando se genera orden automática
   - `StockInsuficiente`: Email cuando falta stock para kit

### Frontend

8. **Componente Livewire GestionMantenimientos**:
   - CRUD completo
   - Tabs: Órdenes Activas / Completadas
   - Filtros: Maquinaria, Tipo, Estado, Fechas
   - Modales: Aprobar (con verificación stock), Completar (form insumos), Detalle

9. **Componente Livewire ConfiguracionKits**:
   - CRUD completo para gestión de kits
   - Selector de tipo de maquinaria
   - Agregar/editar/eliminar insumos del kit
   - Marcas de obligatorio/opcional
   - Validación de duplicados
   - Vista de stock disponible vs requerido
   - Resumen del kit con estadísticas

10. **Vistas**:
    - `resources/views/livewire/gestion-mantenimientos.blade.php`: UI gestión
    - `resources/views/livewire/configuracion-kits.blade.php`: UI configuración kits
    - `resources/views/mantenimientos/index.blade.php`: Layout wrapper
    - `resources/views/kits-mantenimiento/index.blade.php`: Layout wrapper kits

11. **Rutas**:
    - GET `/mantenimientos`: Vista principal gestión
    - POST `/mantenimientos/{id}/approve`: Aprobar orden
    - POST `/mantenimientos/{id}/complete`: Completar mantenimiento
    - GET `/kits-mantenimiento`: Vista configuración de kits

12. **Navbar**:
    - Enlace "Mantenimientos" en sección Recursos
    - Enlace "Kits de Mantenimiento" agregado debajo de Mantenimientos

## 🔧 Configuración Inicial

### 1. Configurar Mailtrap

Edita tu archivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<tu_username>
MAIL_PASSWORD=<tu_password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mantenimiento@rennova.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=admin@rennova.com
```

**Obtener credenciales**:
1. Registrarte en https://mailtrap.io
2. Crear un inbox
3. Copiar SMTP credentials

### 2. Configurar Kits Preventivos

Ahora puedes hacerlo desde la interfaz web:

**Opción A: Usando la UI (Recomendado)**

1. Accede a `/kits-mantenimiento` desde el menú lateral
2. Selecciona el tipo de maquinaria en el dropdown
3. Click en "Agregar Insumo"
4. Completa el formulario:
   - **Insumo**: Selecciona de la lista (muestra stock disponible)
   - **Cantidad Requerida**: Ej: 10.00
   - **¿Es obligatorio?**: Activar para insumos críticos
5. Click en "Agregar"
6. Repite para todos los insumos del kit

**Características de la UI**:
- ✅ Validación de duplicados (no permite agregar el mismo insumo dos veces)
- ✅ Vista de stock actual vs requerido (con alertas visuales)
- ✅ Editar cantidades y obligatoriedad
- ✅ Eliminar insumos del kit
- ✅ Resumen con estadísticas del kit
- ✅ Advertencia si el tipo de maquinaria no tiene umbral configurado

**Opción B: Directamente en la base de datos**

```sql
-- Ejemplo: Kit para Excavadoras
INSERT INTO kit_mantenimiento_preventivo 
(id_tipo_maquinaria, id_insumo, cantidad_requerida, es_obligatorio)
VALUES
(1, 5, 10.00, true),   -- Aceite de motor
(1, 12, 4.00, true),   -- Filtro de aire
(1, 15, 2.00, false);  -- Grasa lubricante
```

### 3. Establecer Umbrales

Configura el umbral de toneladas para cada tipo de maquinaria:

```sql
UPDATE tipo_maquinarias 
SET umbral_toneladas = 1000.00 
WHERE id_tipo_maquinaria = 1;
```

### 4. Integrar Evento en ParteDiario

**Pendiente**: Al guardar un parte diario, disparar el evento:

```php
// En ParteDiarioController o donde se registre la carga
use App\Events\CargaRegistrada;

// Después de guardar la carga
event(new CargaRegistrada(
    $carga,
    $carga->id_maquinaria,
    $carga->toneladas
));
```

## 📖 Flujo de Uso

### Workflow Preventivo (Automático)

1. **Registro de Producción**:
   - Usuario registra carga en Parte Diario
   - Evento `CargaRegistrada` se dispara
   - Listener incrementa `maquinarias.toneladas_acumuladas`

2. **Verificación Nocturna** (2:00 AM):
   - Comando `CheckMantenimientoUmbrales` ejecuta
   - Para cada maquinaria activa:
     - Calcula: `toneladas_acumuladas - último_snapshot`
     - Si >= `umbral_toneladas`:
       - Crea orden en estado `programado`
       - Guarda `toneladas_snapshot` actual
       - Verifica stock del kit
       - Envía email `MantenimientoCreado`
       - Si falta stock: envía email `StockInsuficiente`

3. **Aprobación Manual**:
   - Usuario accede a `/mantenimientos`
   - Ve órdenes en estado `programado`
   - Click en botón "Aprobar"
   - Sistema muestra modal con verificación de stock:
     - ✅ Verde: Stock OK
     - ❌ Rojo: Stock insuficiente (bloquea aprobación)
   - Si aprueba: estado cambia a `en curso`

4. **Ejecución**:
   - Técnico realiza el mantenimiento físico

5. **Completar**:
   - Usuario click en "Completar"
   - Modal muestra formulario:
     - Insumos del kit pre-cargados (si es preventivo)
     - Puede agregar/quitar insumos adicionales
     - Ingresa costo de mano de obra
   - Al confirmar:
     - Descuenta stock de insumos
     - Calcula `costo_total = Σ(subtotales) + costo_mano_obra`
     - Actualiza `último_snapshot` de la máquina
     - Cambia estado a `completado`
     - Registra `fecha_completado`

### Workflow Correctivo (Manual)

1. Usuario crea orden manualmente (pendiente implementar form)
2. Tipo = `correctivo`
3. No requiere kit predefinido
4. Sigue mismo flujo de aprobación y completado

## 🎯 Funcionalidades de la UI

### Gestión de Mantenimientos (`/mantenimientos`)

#### Tab "Órdenes Activas"

- Lista órdenes en `programado` y `en curso`
- Columnas: ID, Maquinaria, Tipo, Estado, Toneladas, Fecha
- Acciones:
  - 👁️ Ver Detalle
  - ✅ Aprobar (solo si estado=programado)
  - 🏁 Completar (solo si estado=en curso)

#### Tab "Completadas"

- Lista órdenes en estado `completado`
- Columnas adicionales: Costo Total, Fecha Completado
- Acción:
  - 👁️ Ver Detalle (incluye breakdown de costos)

#### Filtros

- **Maquinaria**: Dropdown con todas las máquinas activas
- **Tipo**: Preventivo / Correctivo
- **Estado**: Programado / En Curso (solo en tab activas)
- **Rango de Fechas**: Desde / Hasta

#### Modal Aprobar

- Muestra información de la orden
- Tabla de verificación de stock:
  - Columnas: Insumo, Requerido, Disponible, Estado
  - Filas verdes: Stock OK
  - Filas rojas: Stock insuficiente (muestra cantidad faltante)
- Botón aprobar deshabilitado si hay stock insuficiente

#### Modal Completar

- Información de la orden
- Tabla dinámica de insumos:
  - Dropdown para seleccionar insumo
  - Input cantidad utilizada
  - Muestra stock disponible
  - Botón eliminar (solo para no obligatorios)
  - Badge "Requerido" en obligatorios del kit
- Botón "Agregar Insumo" para correctivos o extras
- Input "Costo Mano de Obra"
- Validaciones en tiempo real

#### Modal Detalle

- Información completa de la orden
- Si está completada:
  - Tabla de insumos con costos
  - Desglose: Insumos, Mano de Obra, Total
  - Fecha de completado

### Configuración de Kits (`/kits-mantenimiento`)

#### Selector de Tipo de Maquinaria

- Dropdown con todos los tipos disponibles
- Muestra umbral de toneladas si está configurado
- Alerta si el tipo no tiene umbral

#### Tabla de Items del Kit

- Columnas:
  - **ID**: Identificador del item
  - **Insumo**: Nombre con badge "Obligatorio" si aplica
  - **Cantidad Requerida**: Cantidad necesaria para el mantenimiento
  - **Stock Actual**: Badge verde (suficiente) o rojo (insuficiente)
  - **Tipo**: Obligatorio / Opcional
  - **Acciones**: Editar / Eliminar

#### Modal Agregar/Editar

- **Insumo**: Dropdown con stock entre paréntesis
- **Cantidad Requerida**: Input numérico (step 0.01)
- **¿Es obligatorio?**: Switch para marcar como crítico
- Validación de duplicados al agregar
- Ayuda contextual sobre insumos obligatorios

#### Resumen del Kit

- Total de insumos configurados
- Cantidad de obligatorios vs opcionales
- Indicador de stock completo (cuántos tienen stock suficiente)
- Código de colores para identificar rápidamente el estado

#### Características Especiales

- ✅ **Validación de duplicados**: No permite agregar el mismo insumo dos veces
- ✅ **Vista en tiempo real**: El stock se actualiza al seleccionar insumo
- ✅ **Advertencias visuales**: Alerta si falta stock o no hay umbral configurado
- ✅ **Estado vacío friendly**: Mensaje claro cuando no hay items en el kit
- ✅ **Confirmación de eliminación**: Pregunta antes de borrar items

## 🧪 Pruebas Recomendadas

### 1. Probar Evento Manual

```bash
php artisan tinker
```

```php
use App\Events\CargaRegistrada;
use App\Models\Maquinaria;

$maquinaria = Maquinaria::first();
$toneladas_antes = $maquinaria->toneladas_acumuladas;

event(new CargaRegistrada(null, $maquinaria->id_maquinaria, 50.00));

$maquinaria->refresh();
$toneladas_despues = $maquinaria->toneladas_acumuladas;

echo "Antes: {$toneladas_antes}, Después: {$toneladas_despues}";
// Debería incrementar 50.00
```

### 2. Probar Comando Manual

```bash
php artisan mantenimiento:check-umbrales
```

- Revisa logs en `storage/logs/laravel.log`
- Verifica si se crearon órdenes en BD
- Comprueba emails en Mailtrap inbox

### 3. Probar UI

1. Acceder a `/mantenimientos`
2. Ver si carga lista vacía o con datos
3. Crear una orden manualmente en BD:

```sql
INSERT INTO mantenimientos 
(id_maquinaria, id_tipo_mantenimiento, fecha_inicio, estado, toneladas_snapshot)
VALUES
(1, NULL, NOW(), 'programado', 500.00);
```

4. Refrescar UI y probar botón "Aprobar"
5. Aprobar y probar botón "Completar"

## 📊 Estructura de Base de Datos

### Tablas Principales

**maquinarias**
- `toneladas_acumuladas`: Odómetro actual

**tipo_maquinarias**
- `umbral_toneladas`: Cada cuántas toneladas hacer preventivo

**kit_mantenimiento_preventivo**
- Define qué insumos y cantidades por tipo de máquina
- `es_obligatorio`: Si se debe incluir siempre

**mantenimientos**
- `toneladas_snapshot`: Lectura del odómetro al crear orden
- `costo_mano_obra`: Costo de trabajo humano
- `costo_total`: Suma de insumos + mano de obra

**mantenimiento_insumos**
- `costo_unitario`: Precio del insumo al momento del uso
- `subtotal`: cantidad * costo_unitario

## 🔍 Auditoría

Todos los cambios quedan registrados en la tabla `audits`:

```php
$mantenimiento = Mantenimiento::find(1);
$audits = $mantenimiento->audits;

foreach ($audits as $audit) {
    echo "{$audit->event} por {$audit->user->name} el {$audit->created_at}\n";
    echo "Cambios: " . json_encode($audit->new_values) . "\n";
}
```

## ⚙️ Comandos Útiles

```bash
# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ver log en tiempo real
tail -f storage/logs/laravel.log

# Ejecutar comando manualmente
php artisan mantenimiento:check-umbrales

# Ver tareas programadas
php artisan schedule:list

# Ejecutar scheduler (desarrollo)
php artisan schedule:work
```

## 🚨 Troubleshooting

### Las órdenes no se crean automáticamente

**Verificar**:
1. ¿Está configurado el cron job? (en producción)
2. ¿Hay máquinas con `toneladas_acumuladas >= umbral_toneladas`?
3. ¿El tipo de maquinaria tiene `umbral_toneladas` configurado?
4. ¿Hay kits definidos en `kit_mantenimiento_preventivo`?

**Solución**:
```bash
php artisan schedule:work  # Modo desarrollo
# O ejecutar manualmente:
php artisan mantenimiento:check-umbrales
```

### No incrementa el odómetro

**Verificar**:
1. ¿Está registrado el listener en `AppServiceProvider`?
2. ¿Se está disparando el evento `CargaRegistrada`?

**Solución**:
```bash
php artisan config:clear
php artisan event:list  # Ver eventos registrados
```

### No llegan emails

**Verificar**:
1. Credenciales de Mailtrap en `.env`
2. Email del admin configurado
3. Logs de errores

**Solución**:
```bash
php artisan config:cache
# Probar envío manual en tinker
```

### Error al aprobar orden

**Causas comunes**:
- No hay kit definido para ese tipo de maquinaria
- Insumos del kit no existen en BD
- Stock negativo

**Solución**:
1. Verificar kits: `SELECT * FROM kit_mantenimiento_preventivo WHERE id_tipo_maquinaria = X`
2. Verificar insumos: `SELECT * FROM insumos WHERE id_insumo IN (...)`

## 📝 Tareas Pendientes

- [ ] Integrar evento `CargaRegistrada` en `ParteDiarioController`
- [ ] Agregar form para crear órdenes correctivas manualmente
- [ ] Implementar reportes de costos históricos
- [ ] Dashboard con indicadores: próximos mantenimientos, costo promedio, etc.
- [ ] Configurar cron job en producción
- [ ] Exportar historial a PDF/Excel

## 📚 Documentación Adicional

- Ver `SISTEMA_MANTENIMIENTO_DOCS.md` para detalles técnicos completos
- Ver `.env.mailtrap.example` para configuración de email

## 💡 Próximos Pasos Recomendados

1. **Configurar Mailtrap** (5 min)
2. **Definir Kits** para al menos 1 tipo de maquinaria (10 min)
3. **Probar comando manual** para verificar creación de órdenes (5 min)
4. **Probar UI completa** con flujo aprobar → completar (15 min)
5. **Integrar evento en ParteDiario** cuando esté listo (20 min)
6. **Configurar cron job** en producción (5 min)

---

**Desarrollado con** ❤️ **usando Laravel + Livewire + PostgreSQL + OwenIt\Auditing + Mailtrap**
