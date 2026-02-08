<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_proposal_maquinarias', function (Blueprint $table) {
            $table->id('id_allocation_proposal_maquinaria');
            $table->unsignedBigInteger('id_allocation_proposal');
            $table->unsignedBigInteger('id_maquinaria');

            $table->string('tipo_sugerido', 50)->nullable();
            $table->decimal('score', 12, 4)->nullable();
            $table->boolean('selected')->default(true);

            $table->timestamps();

            $table->foreign('id_allocation_proposal')
                ->references('id_allocation_proposal')
                ->on('allocation_proposals')
                ->onDelete('cascade');

            $table->foreign('id_maquinaria')
                ->references('id_maquinaria')
                ->on('maquinarias')
                ->onDelete('cascade');

            $table->unique(['id_allocation_proposal', 'id_maquinaria'], 'uq_alloc_prop_maquinaria');
            $table->index(['id_allocation_proposal', 'selected'], 'idx_alloc_prop_maquinaria_selected');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_proposal_maquinarias');
    }
};
