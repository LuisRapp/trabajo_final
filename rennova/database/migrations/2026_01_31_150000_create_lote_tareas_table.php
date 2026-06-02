<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_tareas', function (Blueprint $table) {
            $table->id('id_lote_tarea');

            $table->unsignedBigInteger('id_lote');
            $table->foreign('id_lote')->references('id_lote')->on('lotes');

            $table->string('tipo_tarea', 30);
            $table->string('estado', 20)->default('planificada');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->decimal('superficie_afectada_ha', 10, 2)->nullable();
            $table->string('observaciones', 255)->nullable();

            $table->timestamps();

            $table->index(['id_lote', 'estado', 'tipo_tarea'], 'idx_lote_tareas_lote_estado_tarea');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_tareas');
    }
};
