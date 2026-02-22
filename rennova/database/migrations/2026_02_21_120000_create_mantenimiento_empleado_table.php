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
        if (!Schema::hasTable('mantenimiento_empleado')) {
            Schema::create('mantenimiento_empleado', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_mantenimiento');
                $table->unsignedBigInteger('id_empleado');
                $table->string('rol_origen', 32)->nullable()
                    ->comment('mantenimiento|administrativo|fallback');
                $table->timestamps();

                $table->foreign('id_mantenimiento')
                    ->references('id_mantenimiento')
                    ->on('mantenimientos')
                    ->onDelete('cascade');

                $table->foreign('id_empleado')
                    ->references('id_empleado')
                    ->on('empleados')
                    ->onDelete('cascade');

                // Regla funcional actual: 1 persona por mantenimiento.
                $table->unique('id_mantenimiento', 'uq_mantenimiento_empleado_orden');
                $table->index('id_empleado', 'idx_mantenimiento_empleado_empleado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_empleado');
    }
};

