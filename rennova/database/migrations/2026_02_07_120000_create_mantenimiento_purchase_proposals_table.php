<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento_purchase_proposals', function (Blueprint $table) {
            $table->id('id_mantenimiento_purchase_proposal');
            $table->unsignedBigInteger('id_mantenimiento');
            $table->unsignedBigInteger('id_maquinaria');
            $table->string('status', 20)->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('id_mantenimiento')
                ->references('id_mantenimiento')
                ->on('mantenimientos')
                ->onDelete('cascade');

            $table->foreign('id_maquinaria')
                ->references('id_maquinaria')
                ->on('maquinarias')
                ->onDelete('cascade');

            $table->unique('id_mantenimiento', 'uniq_mantenimiento_purchase_proposal');
        });

        Schema::create('mantenimiento_purchase_proposal_insumos', function (Blueprint $table) {
            $table->id('id_mantenimiento_purchase_proposal_insumo');
            $table->unsignedBigInteger('id_mantenimiento_purchase_proposal');
            $table->unsignedBigInteger('id_insumo');
            $table->decimal('cantidad_requerida', 10, 2);
            $table->decimal('stock_disponible', 10, 2)->default(0);
            $table->decimal('faltante', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_mantenimiento_purchase_proposal')
                ->references('id_mantenimiento_purchase_proposal')
                ->on('mantenimiento_purchase_proposals')
                ->onDelete('cascade');

            $table->foreign('id_insumo')
                ->references('id_insumo')
                ->on('insumos')
                ->onDelete('cascade');

            $table->index(['id_mantenimiento_purchase_proposal', 'id_insumo'], 'idx_mpp_insumos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_purchase_proposal_insumos');
        Schema::dropIfExists('mantenimiento_purchase_proposals');
    }
};
