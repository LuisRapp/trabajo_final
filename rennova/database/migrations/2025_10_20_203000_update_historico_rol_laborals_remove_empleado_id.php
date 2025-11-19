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
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            // Eliminar la FK y la columna empleado_id para que el histórico dependa solo del rol
            if (Schema::hasColumn('historico_rol_laborals', 'empleado_id')) {
                $table->dropForeign(['empleado_id']);
                $table->dropColumn('empleado_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            // Restaurar columna y FK en caso de rollback
            if (!Schema::hasColumn('historico_rol_laborals', 'empleado_id')) {
                $table->unsignedBigInteger('empleado_id')->nullable();
                $table->foreign('empleado_id')->references('id_empleado')->on('empleados')->onDelete('cascade');
            }
        });
    }
};
