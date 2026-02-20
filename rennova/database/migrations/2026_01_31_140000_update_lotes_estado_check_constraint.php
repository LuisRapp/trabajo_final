<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE lotes DROP CONSTRAINT IF EXISTS lotes_estado_check');
        DB::statement("ALTER TABLE lotes ADD CONSTRAINT lotes_estado_check CHECK (estado IN ('activo','en_proceso','inactivo','cerrado','baja'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE lotes DROP CONSTRAINT IF EXISTS lotes_estado_check');
        DB::statement("ALTER TABLE lotes ADD CONSTRAINT lotes_estado_check CHECK (estado IN ('activo','cerrado','baja'))");
    }
};
