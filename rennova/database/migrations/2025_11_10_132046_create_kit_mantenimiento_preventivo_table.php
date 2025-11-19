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
        Schema::create('kit_mantenimiento_preventivo', function (Blueprint $table) {
            $table->id('id_kit');
            $table->unsignedBigInteger('id_tipo_maquinaria');
            $table->foreign('id_tipo_maquinaria')->references('id_tipo_maquinaria')->on('tipo_maquinarias')->onDelete('cascade');
            $table->unsignedBigInteger('id_insumo');
            $table->foreign('id_insumo')->references('id_insumo')->on('insumos')->onDelete('cascade');
            $table->decimal('cantidad_requerida', 10, 2);
            $table->boolean('es_obligatorio')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_mantenimiento_preventivo');
    }
};
