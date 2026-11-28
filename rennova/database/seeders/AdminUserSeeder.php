<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el administrador inicial.
     *
     * La password se toma de ADMIN_PASSWORD (nunca se hardcodea ni se imprime).
     * Si el usuario ya existe no se toca su password. Si la variable no esta
     * definida, no hace nada: la base puede quedar sin admin y se crea despues
     * con este mismo comando.
     */
    public function run(): void
    {
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($password === '') {
            $this->command->warn('ADMIN_PASSWORD no esta definida: no se crea el administrador.');

            return;
        }

        $email = (string) env('ADMIN_EMAIL', 'admin@rennova.com');

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        // El cast "hashed" del modelo hashea la password al asignarla.
        $admin = Usuario::firstOrCreate(
            ['email' => $email],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Rennova',
                'password' => $password,
            ]
        );

        if (! $admin->hasRole($role->name)) {
            $admin->assignRole($role);
        }

        $this->command->info('Administrador asegurado: '.$email);
    }
}
