<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir los módulos del sistema
        $modules = [
            'partes-diarios' => 'Partes Diarios',
            'lotes' => 'Lotes',
            'cargas' => 'Cargas',
            'maquinarias' => 'Maquinarias',
            'mantenimientos' => 'Mantenimientos',
            'insumos' => 'Insumos',
            'empleados' => 'Empleados',
            'adelantos' => 'Adelantos',
            'clientes' => 'Clientes',
            'ventas' => 'Ventas',
            'recibos' => 'Recibos',
            'proveedores' => 'Proveedores',
            'choferes' => 'Choferes',
            'usuarios' => 'Usuarios',
            'roles-laborales' => 'Roles Laborales',
            'categorias-madera' => 'Categorías de Madera',
            'tipos-maquinaria' => 'Tipos de Maquinaria',
            'tipos-mantenimiento' => 'Tipos de Mantenimiento',
            'unidades-medida' => 'Unidades de Medida',
            'lista-precios' => 'Lista de Precios',
            'reportes' => 'Reportes',
            'auditoria' => 'Auditoría',
        ];

        // Crear permisos para cada módulo
        $actions = ['ver', 'crear', 'editar', 'eliminar'];
        
        foreach ($modules as $moduleKey => $moduleName) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}-{$moduleKey}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Permisos especiales adicionales
        Permission::firstOrCreate(['name' => 'exportar-reportes', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'ver-dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'ver-auditoria', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gestionar-permisos', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'gestionar-usuarios', 'guard_name' => 'web']);

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $operadorRole = Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);
        $contadorRole = Role::firstOrCreate(['name' => 'Contador', 'guard_name' => 'web']);
        $vendedorRole = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);

        // Limpiar permisos anteriores de los roles
        $adminRole->syncPermissions([]);
        $supervisorRole->syncPermissions([]);
        $operadorRole->syncPermissions([]);
        $contadorRole->syncPermissions([]);
        $vendedorRole->syncPermissions([]);

        // ADMINISTRADOR - Todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // SUPERVISOR - Puede ver todo, editar operaciones, no puede eliminar ni gestionar usuarios
        $supervisorRole->givePermissionTo([
            // Ver todo
            'ver-partes-diarios', 'ver-lotes', 'ver-cargas', 'ver-maquinarias', 
            'ver-mantenimientos', 'ver-insumos', 'ver-empleados', 'ver-adelantos',
            'ver-clientes', 'ver-ventas', 'ver-recibos', 'ver-proveedores', 
            'ver-choferes', 'ver-roles-laborales', 'ver-categorias-madera',
            'ver-tipos-maquinaria', 'ver-tipos-mantenimiento', 'ver-unidades-medida',
            'ver-lista-precios', 'ver-reportes', 'ver-dashboard',
            
            // Crear y editar operaciones
            'crear-partes-diarios', 'editar-partes-diarios',
            'crear-cargas', 'editar-cargas',
            'crear-lotes', 'editar-lotes',
            'crear-mantenimientos', 'editar-mantenimientos',
            'crear-insumos', 'editar-insumos',
            'crear-empleados', 'editar-empleados',
            'crear-adelantos', 'editar-adelantos',
            'crear-maquinarias', 'editar-maquinarias',
            
            // Reportes
            'exportar-reportes',
        ]);

        // OPERADOR - Solo operaciones diarias básicas
        $operadorRole->givePermissionTo([
            'ver-dashboard',
            'ver-partes-diarios', 'crear-partes-diarios', 'editar-partes-diarios',
            'ver-cargas', 'crear-cargas', 'editar-cargas',
            'ver-lotes',
            'ver-maquinarias',
            'ver-mantenimientos', 'crear-mantenimientos',
            'ver-insumos',
            'ver-empleados',
            'ver-choferes',
            'ver-categorias-madera',
            'ver-clientes',
        ]);

        // CONTADOR - Solo gestión financiera y reportes
        $contadorRole->givePermissionTo([
            'ver-dashboard',
            'ver-ventas', 'crear-ventas', 'editar-ventas',
            'ver-recibos', 'crear-recibos', 'editar-recibos',
            'ver-adelantos', 'crear-adelantos', 'editar-adelantos',
            'ver-clientes', 'crear-clientes', 'editar-clientes',
            'ver-proveedores', 'crear-proveedores', 'editar-proveedores',
            'ver-empleados',
            'ver-cargas',
            'ver-lotes',
            'ver-lista-precios', 'crear-lista-precios', 'editar-lista-precios',
            'ver-reportes',
            'exportar-reportes',
        ]);

        // VENDEDOR - Solo gestión de clientes y ventas
        $vendedorRole->givePermissionTo([
            'ver-dashboard',
            'ver-clientes', 'crear-clientes', 'editar-clientes',
            'ver-ventas', 'crear-ventas', 'editar-ventas',
            'ver-recibos', 'crear-recibos',
            'ver-cargas',
            'ver-lotes',
            'ver-lista-precios',
            'ver-categorias-madera',
        ]);

        $this->command->info('Roles y permisos creados exitosamente.');
        $this->command->info('');
        $this->command->info('Roles creados:');
        $this->command->info('- Administrador (todos los permisos)');
        $this->command->info('- Supervisor (operaciones y reportes)');
        $this->command->info('- Operador (operaciones diarias básicas)');
        $this->command->info('- Contador (finanzas y reportes)');
        $this->command->info('- Vendedor (clientes y ventas)');
        $this->command->info('');
        $this->command->info('Total de permisos creados: ' . Permission::count());
    }
}
