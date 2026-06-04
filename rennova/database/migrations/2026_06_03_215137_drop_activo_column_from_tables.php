<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Elimina columna activo booleana de tablas que ya tienen SoftDeletes
     */
    public function up(): void
    {
        $tablas = [
            'adelantos',
            'parte_diarios',
            'recibos',
            'roles_laborales',
            'tipo_mantenimientos',
            'usuarios',
            'ventas',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('activo');
            });
        }
    }

    /**
     * Re-agrega columna activo booleana
     */
    public function down(): void
    {
        $tablas = [
            'adelantos',
            'parte_diarios',
            'recibos',
            'roles_laborales',
            'tipo_mantenimientos',
            'usuarios',
            'ventas',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('id');
            });
        }
    }
};
