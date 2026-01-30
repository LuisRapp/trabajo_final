<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminUserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario administrador de demostración
        $this->call([
            RolesAndPermissionsSeeder::class,
            LotesClimaDemoSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => bcrypt('demo1234'),
        ]);

        // Asignar rol de Administrador
        $admin->assignRole('Administrador');

        $this->command->info('✅ Usuario de demostración creado:');
        $this->command->info('   Email: demo@example.com');
        $this->command->info('   Password: demo1234');
    }
}
