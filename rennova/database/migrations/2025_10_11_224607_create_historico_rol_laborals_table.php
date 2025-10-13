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
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('rol_laboral_id')->constrained('rol_laboral')->onDelete('restrict');
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
