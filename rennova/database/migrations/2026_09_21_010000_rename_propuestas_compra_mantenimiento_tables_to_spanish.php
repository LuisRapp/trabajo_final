<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renombra las tablas de propuestas de compra de mantenimiento y sus
     * columnas identificadores al español, siguiendo la convención del
     * resto del esquema (mantenimientos, mantenimiento_insumos, etc.).
     */
    public function up(): void
    {
        Schema::rename('mantenimiento_purchase_proposal_insumos', 'propuestas_compra_mantenimiento_insumos');
        Schema::rename('mantenimiento_purchase_proposals', 'propuestas_compra_mantenimiento');

        Schema::table('propuestas_compra_mantenimiento', function (Blueprint $table) {
            $table->renameColumn('id_mantenimiento_purchase_proposal', 'id_propuesta_compra_mantenimiento');
        });

        Schema::table('propuestas_compra_mantenimiento_insumos', function (Blueprint $table) {
            $table->renameColumn('id_mantenimiento_purchase_proposal_insumo', 'id_propuesta_compra_mantenimiento_insumo');
            $table->renameColumn('id_mantenimiento_purchase_proposal', 'id_propuesta_compra_mantenimiento');
        });

        Schema::table('propuestas_compra_mantenimiento', function (Blueprint $table) {
            $table->renameIndex('uniq_mantenimiento_purchase_proposal', 'uniq_propuesta_compra_mantenimiento');
        });

        Schema::table('propuestas_compra_mantenimiento_insumos', function (Blueprint $table) {
            $table->renameIndex('idx_mpp_insumos', 'idx_propuesta_compra_insumos');
        });
    }

    public function down(): void
    {
        Schema::table('propuestas_compra_mantenimiento_insumos', function (Blueprint $table) {
            $table->renameIndex('idx_propuesta_compra_insumos', 'idx_mpp_insumos');
        });

        Schema::table('propuestas_compra_mantenimiento', function (Blueprint $table) {
            $table->renameIndex('uniq_propuesta_compra_mantenimiento', 'uniq_mantenimiento_purchase_proposal');
        });

        Schema::table('propuestas_compra_mantenimiento_insumos', function (Blueprint $table) {
            $table->renameColumn('id_propuesta_compra_mantenimiento', 'id_mantenimiento_purchase_proposal');
            $table->renameColumn('id_propuesta_compra_mantenimiento_insumo', 'id_mantenimiento_purchase_proposal_insumo');
        });

        Schema::table('propuestas_compra_mantenimiento', function (Blueprint $table) {
            $table->renameColumn('id_propuesta_compra_mantenimiento', 'id_mantenimiento_purchase_proposal');
        });

        Schema::rename('propuestas_compra_mantenimiento', 'mantenimiento_purchase_proposals');
        Schema::rename('propuestas_compra_mantenimiento_insumos', 'mantenimiento_purchase_proposal_insumos');
    }
};
