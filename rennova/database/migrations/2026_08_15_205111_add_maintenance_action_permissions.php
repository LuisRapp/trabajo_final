<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $confirmar = Permission::firstOrCreate([
            'name' => 'confirmar-mantenimiento',
            'guard_name' => 'web',
        ]);

        $completar = Permission::firstOrCreate([
            'name' => 'completar-mantenimiento',
            'guard_name' => 'web',
        ]);

        $supervisor = Role::firstOrCreate([
            'name' => 'Supervisor',
            'guard_name' => 'web',
        ]);

        $operador = Role::firstOrCreate([
            'name' => 'Operador',
            'guard_name' => 'web',
        ]);

        $supervisor->givePermissionTo([$confirmar, $completar]);
        $operador->givePermissionTo([$confirmar, $completar]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $completar = Permission::findByName('completar-mantenimiento', 'web');

        if ($completar !== null) {
            $completar->delete();
        }

        $confirmar = Permission::findByName('confirmar-mantenimiento', 'web');

        if ($confirmar !== null) {
            $roles = Role::whereIn('name', ['Supervisor', 'Operador'])->get();

            foreach ($roles as $rol) {
                $rol->revokePermissionTo($confirmar);
            }
        }
    }
};
