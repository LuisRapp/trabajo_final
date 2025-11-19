<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
        $admin = User::firstOrCreate(
            ['email' => 'admin@rennova.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'), // Cambiar por una contraseña segura
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
