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
        Schema::create('clima_dias_lote', function (Blueprint $table) {
            $table->id('id_clima_dia_lote');
            $table->unsignedBigInteger('id_lote');
            $table->date('fecha');
            $table->string('estado_operativo', 24);
            $table->string('razon', 180)->nullable();
            $table->string('fuente', 20)->default('api');
            $table->text('api_error')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->foreign('id_lote')->references('id_lote')->on('lotes')->onDelete('cascade');
            $table->unique(['id_lote', 'fecha'], 'uq_clima_dias_lote_fecha');
            $table->index(['fecha', 'estado_operativo'], 'idx_clima_dias_lote_fecha_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clima_dias_lote');
    }
};
