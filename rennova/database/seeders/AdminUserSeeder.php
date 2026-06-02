<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que exista el rol "Administrador"
        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        // Crear usuario administrador si no existe
        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@rennova.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Rennova',
                'password' => Hash::make('password'), // Cambiar por una contraseña segura
                'activo' => true,
            ]
        );

        // Asignar rol de Administrador si no lo tiene
        if (!$admin->hasRole($role->name)) {
            $admin->assignRole($role);
        }

        $this->command->info('Usuario administrador creado/actualizado:');
        $this->command->info('Email: admin@rennova.com');
        $this->command->info('Password: password');
        $this->command->info('⚠️  IMPORTANTE: Cambiar la contraseña después del primer login');
    }
}
