<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_proposal_insumos', function (Blueprint $table) {
            $table->id('id_allocation_proposal_insumo');
            $table->unsignedBigInteger('id_allocation_proposal');
            $table->unsignedBigInteger('id_insumo');

            $table->decimal('cantidad_semana_1', 12, 2)->nullable();
            $table->decimal('costo_estimado_semana_1', 12, 2)->nullable();
            $table->boolean('selected')->default(true);

            $table->timestamps();

            $table->foreign('id_allocation_proposal')
                ->references('id_allocation_proposal')
                ->on('allocation_proposals')
                ->onDelete('cascade');

            $table->foreign('id_insumo')
                ->references('id_insumo')
                ->on('insumos')
                ->onDelete('cascade');

            $table->unique(['id_allocation_proposal', 'id_insumo'], 'uq_alloc_prop_insumo');
            $table->index(['id_allocation_proposal', 'selected'], 'idx_alloc_prop_insumo_selected');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_proposal_insumos');
    }
};
