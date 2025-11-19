<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@rennova.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Crear rol Super Admin si no existe
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // Asignar TODOS los permisos al rol
        $allPermissions = Permission::all();
        $role->syncPermissions($allPermissions);

        // Asignar rol al usuario
        if (!$superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole($role);
        }

        $this->command->info('✓ Super Admin creado exitosamente');
        $this->command->info('  Email: superadmin@rennova.com');
        $this->command->info('  Password: admin123');
        $this->command->info('  Permisos: ' . $allPermissions->count() . ' permisos asignados');
    }
}
