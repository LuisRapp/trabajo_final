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
        Schema::create('historico_rol_laborals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->foreign('empleado_id')->references('id_empleado')->on('empleados')->onDelete('cascade');
            $table->unsignedBigInteger('rol_laboral_id');
            $table->foreign('rol_laboral_id')->references('id_rol_laboral')->on('rol_laborals')->onDelete('restrict');
            $table->decimal('costo_diario', 12, 2)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // NULL = rol actual
            $table->string('motivo_cambio')->nullable(); // Ej: ascenso, ajuste salarial, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_rol_laborals');
    }
};
