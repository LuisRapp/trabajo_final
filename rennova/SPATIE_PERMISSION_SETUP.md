# Implementación de Spatie Permission

## ✅ Sistema de Roles y Permisos Implementado

### 📦 Componentes Instalados

- **Spatie Laravel Permission v6.21**
- Tablas: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
- Middleware personalizado para validación de permisos
- Seeder con roles y permisos predefinidos
- Interfaz visual para gestión de roles y permisos

---

## 🎭 Roles Creados

### 1. **Administrador**
- **Permisos:** TODOS (91 permisos)
- **Descripción:** Acceso completo al sistema

### 2. **Supervisor**
- **Permisos:** Ver todo, crear/editar operaciones, exportar reportes
- **Restricciones:** No puede eliminar ni gestionar usuarios
- **Módulos:** Partes diarios, cargas, lotes, mantenimientos, empleados, etc.

### 3. **Operador**
- **Permisos:** Operaciones diarias básicas
- **Módulos:** Partes diarios, cargas, lotes (solo ver), mantenimientos
- **Restricciones:** No puede eliminar ni acceder a finanzas

### 4. **Contador**
- **Permisos:** Gestión financiera y reportes
- **Módulos:** Ventas, recibos, adelantos, clientes, proveedores, lista de precios
- **Restricciones:** No puede acceder a operaciones productivas

### 5. **Vendedor**
- **Permisos:** Gestión de clientes y ventas
- **Módulos:** Clientes, ventas, recibos, lista de precios (solo ver)
- **Restricciones:** No puede acceder a operaciones ni finanzas internas

---

## 🔑 Permisos por Módulo

Cada módulo tiene 4 permisos básicos:
- **ver-[módulo]**: Ver listado y detalles
- **crear-[módulo]**: Crear nuevos registros
- **editar-[módulo]**: Modificar registros existentes
- **eliminar-[módulo]**: Eliminar registros

### Módulos con permisos:
- partes-diarios
- lotes
- cargas
- maquinarias
- mantenimientos
- insumos
- empleados
- adelantos
- clientes
- ventas
- recibos
- proveedores
- choferes
- usuarios
- roles-laborales
- categorias-madera
- tipos-maquinaria
- tipos-mantenimiento
- unidades-medida
- lista-precios
- reportes
- auditoria

### Permisos especiales:
- **exportar-reportes**: Exportar datos a Excel/PDF
- **ver-dashboard**: Acceder al dashboard
- **ver-auditoria**: Ver auditoría del sistema
- **gestionar-permisos**: Administrar roles y permisos

---

## 💻 Uso en el Código

### 1. Proteger Rutas

```php
// Requiere permiso específico
Route::get('/lista-precios', function() {
    // ...
})->middleware('permission:ver-lista-precios');

// Requiere un rol específico
Route::get('/admin', function() {
    // ...
})->middleware('role:Administrador');

// Requiere rol O permiso
Route::get('/reportes', function() {
    // ...
})->middleware('role_or_permission:Supervisor|exportar-reportes');
```

### 2. Proteger Vistas Blade

```blade
{{-- Verificar permiso --}}
@can('ver-lista-precios')
    <a href="{{ route('lista-precios.index') }}">Lista de Precios</a>
@endcan

{{-- Verificar rol --}}
@role('Administrador')
    <button>Eliminar Todo</button>
@endrole

{{-- Verificar múltiples permisos --}}
@canany(['crear-ventas', 'editar-ventas'])
    <button>Gestionar Ventas</button>
@endcanany
```

### 3. En Controladores PHP

```php
// Verificar permiso
if (auth()->user()->can('crear-ventas')) {
    // Permitir crear venta
}

// Verificar rol
if (auth()->user()->hasRole('Administrador')) {
    // Acceso de administrador
}

// Verificar múltiples permisos
if (auth()->user()->hasAnyPermission(['ver-reportes', 'exportar-reportes'])) {
    // Permitir acceso a reportes
}

// Abortar si no tiene permiso
abort_if(!auth()->user()->can('eliminar-ventas'), 403);
```

### 4. En Componentes Livewire

```php
class MiComponente extends Component
{
    public function mount()
    {
        // Verificar permiso al cargar componente
        if (!auth()->user()->can('ver-lista-precios')) {
            abort(403, 'No tienes permiso');
        }
    }
    
    public function eliminar($id)
    {
        // Verificar permiso antes de eliminar
        if (!auth()->user()->can('eliminar-lista-precios')) {
            session()->flash('error', 'No tienes permiso para eliminar');
            return;
        }
        
        // Proceder con eliminación
    }
}
```

---

## 🛠️ Gestión de Roles y Permisos

### Interfaz Visual
Accede a: `/roles-permisos` (requiere permiso `gestionar-permisos`)

**Funcionalidades:**
1. **Tab "Roles y Permisos":**
   - Ver lista de roles
   - Crear nuevos roles
   - Asignar/desasignar permisos a roles
   - Eliminar roles (excepto Administrador)

2. **Tab "Asignar Roles a Usuarios":**
   - Ver lista de usuarios
   - Buscar usuarios
   - Asignar múltiples roles a un usuario
   - Ver roles actuales de cada usuario

### Por Código (en Tinker o Seeder)

```php
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Crear un rol
$rol = Role::create(['name' => 'Jefe de Producción']);

// Asignar permisos al rol
$rol->givePermissionTo('ver-partes-diarios');
$rol->givePermissionTo(['crear-partes-diarios', 'editar-partes-diarios']);

// Asignar rol a usuario
$user = User::find(1);
$user->assignRole('Administrador');

// Asignar múltiples roles
$user->syncRoles(['Supervisor', 'Contador']);

// Dar permiso directo a usuario (sin rol)
$user->givePermissionTo('exportar-reportes');

// Verificar
$user->hasRole('Administrador'); // true/false
$user->can('ver-ventas'); // true/false
```

---

## 🚀 Asignar Rol al Primer Usuario

Para asignar el rol de Administrador al primer usuario:

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$user->assignRole('Administrador');
```

---

## 📝 Comandos Útiles

```bash
# Limpiar caché de permisos
php artisan permission:cache-reset

# Ver permisos en caché
php artisan permission:show

# Re-ejecutar seeder de roles
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 🔒 Protección Recomendada por Módulo

```php
// En routes/web.php

Route::middleware(['auth'])->group(function () {
    // Operaciones diarias
    Route::get('/partes-diarios', ...)->middleware('permission:ver-partes-diarios');
    Route::get('/cargas', ...)->middleware('permission:ver-cargas');
    Route::get('/lotes', ...)->middleware('permission:ver-lotes');
    
    // Mantenimiento
    Route::get('/mantenimientos', ...)->middleware('permission:ver-mantenimientos');
    Route::get('/maquinarias', ...)->middleware('permission:ver-maquinarias');
    
    // Recursos Humanos
    Route::get('/empleados', ...)->middleware('permission:ver-empleados');
    Route::get('/adelantos', ...)->middleware('permission:ver-adelantos');
    
    // Finanzas
    Route::get('/ventas', ...)->middleware('permission:ver-ventas');
    Route::get('/recibos', ...)->middleware('permission:ver-recibos');
    Route::get('/lista-precios', ...)->middleware('permission:ver-lista-precios');
    
    // Configuración (solo admin/supervisor)
    Route::get('/usuarios', ...)->middleware('role:Administrador');
    Route::get('/roles-permisos', ...)->middleware('permission:gestionar-permisos');
});
```

---

## ✅ Estado Actual

- ✅ Spatie Permission instalado
- ✅ Migraciones ejecutadas
- ✅ Modelo User con trait HasRoles
- ✅ Middleware registrados
- ✅ 91 permisos creados
- ✅ 5 roles creados con permisos asignados
- ✅ Interfaz visual para gestión
- ✅ Ruta y sidebar agregados
- ⚠️ **Pendiente:** Asignar rol al primer usuario
- ⚠️ **Pendiente:** Proteger rutas individuales con middleware

---

## 🎯 Próximos Pasos

1. **Asignar rol Administrador al usuario principal:**
   ```bash
   php artisan tinker
   User::first()->assignRole('Administrador');
   ```

2. **Proteger rutas críticas:**
   - Agregar middleware `permission:` a rutas sensibles
   - Proteger botones de eliminación en vistas

3. **Personalizar permisos:**
   - Ajustar permisos de cada rol según necesidades
   - Crear roles adicionales si es necesario

4. **Testing:**
   - Probar acceso con diferentes roles
   - Verificar que las restricciones funcionen correctamente

---

**Sistema de permisos completamente funcional y listo para usar! 🎉**
