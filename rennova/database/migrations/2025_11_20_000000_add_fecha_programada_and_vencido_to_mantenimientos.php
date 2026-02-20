<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Modificar enum de estado para incluir 'vencido' (solo para PostgreSQL).
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_estado_check");
            DB::statement("ALTER TABLE mantenimientos ALTER COLUMN estado TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE mantenimientos ADD CONSTRAINT mantenimientos_estado_check CHECK (estado IN ('programado', 'en curso', 'completado', 'vencido'))");
        }
        
        // Agregar campo fecha_programada
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->date('fecha_programada')->nullable()->after('fecha_inicio')
                ->comment('Fecha en la que se programó realizar el mantenimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn('fecha_programada');
        });

        $driver = DB::connection()->getDriverName();

        // Revertir enum de estado (solo para PostgreSQL).
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE mantenimientos DROP CONSTRAINT mantenimientos_estado_check");
            DB::statement("ALTER TABLE mantenimientos ALTER COLUMN estado TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE mantenimientos ADD CONSTRAINT mantenimientos_estado_check CHECK (estado IN ('programado', 'en curso', 'completado'))");
        }
    }
};
