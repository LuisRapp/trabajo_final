<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            // Nullable por compatibilidad con datos existentes (hoy son de prueba).
            // La app lo exigirá para nuevos registros/ediciones.
            $table->string('tipo_tarea', 30)->nullable()->after('fecha');

            $table->index(['tipo_tarea', 'fecha'], 'idx_parte_diarios_tipo_tarea_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropIndex('idx_parte_diarios_tipo_tarea_fecha');
            $table->dropColumn('tipo_tarea');
        });
    }
};
