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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id('id_mantenimiento');
            $table->unsignedBigInteger('id_maquinaria');
            $table->foreign('id_maquinaria')->references('id_maquinaria')->on('maquinarias');
            $table->unsignedBigInteger('id_tipo_mantenimiento')->nullable();
            $table->foreign('id_tipo_mantenimiento')->references('id_tipo_mantenimiento')->on('tipo_mantenimientos');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('costo_total', 10, 2)->nullable();
            $table->enum('estado', ['programado','en curso','completado'])->default('programado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
