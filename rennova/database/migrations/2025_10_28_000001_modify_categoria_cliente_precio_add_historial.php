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
        // Primero eliminamos la restricción unique existente
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            $table->dropUnique('unique_cliente_categoria');
        });

        // Modificamos la tabla para agregar el sistema de historial
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            // Renombramos fecha_vigencia a fecha_desde
            $table->renameColumn('fecha_vigencia', 'fecha_desde');
        });

        // Hacemos fecha_desde NOT NULL y agregamos fecha_hasta
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            $table->date('fecha_desde')->nullable(false)->change();
            $table->date('fecha_hasta')->nullable()->after('fecha_desde');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            // Eliminamos fecha_hasta
            $table->dropColumn('fecha_hasta');
            
            // Renombramos fecha_desde de vuelta a fecha_vigencia
            $table->renameColumn('fecha_desde', 'fecha_vigencia');
        });

        // Hacemos fecha_vigencia nullable nuevamente
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            $table->date('fecha_vigencia')->nullable()->change();
        });

        // Restauramos la restricción unique
        Schema::table('categoria_cliente_precio', function (Blueprint $table) {
            $table->unique(['cliente_id', 'categoria_id'], 'unique_cliente_categoria');
        });
    }
};
