<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_proposals', function (Blueprint $table) {
            $table->id('id_allocation_proposal');
            $table->unsignedBigInteger('id_lote');

            // TaskType::value (string) para portabilidad entre motores.
            $table->string('tipo_tarea', 30);

            $table->string('especie', 50)->nullable();
            $table->decimal('superficie_ha', 10, 2)->nullable();

            // Estimaciones (en días)
            $table->decimal('estimated_person_days', 10, 2)->nullable();
            $table->decimal('estimated_machine_days', 10, 2)->nullable();
            $table->decimal('estimated_duration_days', 10, 2)->nullable();

            // Sugerencias
            $table->unsignedInteger('suggested_team_size')->nullable();
            $table->unsignedInteger('suggested_machinery_count')->nullable();

            // Explicabilidad
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->foreign('id_lote')->references('id_lote')->on('lotes')->onDelete('cascade');

            $table->index(['id_lote', 'tipo_tarea', 'created_at'], 'idx_alloc_prop_lote_tarea_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_proposals');
    }
};
