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
        Schema::create('maquinarias', function (Blueprint $table) {
            $table->id('id_maquinaria');
            $table->foreignId('id_tipo_maquinaria')->constrained('tipo_maquinaria');
            $table->string('modelo', 60)->nullable();
            $table->string('estado', 20)->default('activo');
            $table->boolean('es_alquilada')->default(false);
            $table->date('fecha_inicio_actividades')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinarias');
    }
};
