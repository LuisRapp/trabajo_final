<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->index('id_lote', 'idx_cargas_id_lote');
            $table->index('id_parte_diario', 'idx_cargas_id_parte_diario');
            $table->index('fecha_carga', 'idx_cargas_fecha_carga');
            $table->index('id_chofer', 'idx_cargas_id_chofer');
            $table->index('id_categoria_madera', 'idx_cargas_id_categoria_madera');
            $table->index('estado', 'idx_cargas_estado');
        });

        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->index('fecha', 'idx_parte_diarios_fecha');
            $table->index('id_lote', 'idx_parte_diarios_id_lote');
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->index('id_maquinaria', 'idx_mantenimientos_id_maquinaria');
            $table->index('estado', 'idx_mantenimientos_estado');
            $table->index('fecha_fin', 'idx_mantenimientos_fecha_fin');
        });

        Schema::table('historico_roles_laborales', function (Blueprint $table) {
            $table->index(['rol_laboral_id', 'fecha_inicio'], 'idx_hist_roles_laboral_fecha');
        });

        Schema::table('historico_costos_maquinarias', function (Blueprint $table) {
            $table->index('id_maquinaria', 'idx_hist_costos_id_maquinaria');
        });

        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->index('id_proveedor', 'idx_lotes_inv_id_proveedor');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->index('id_rol_laboral', 'idx_empleados_id_rol_laboral');
        });

        Schema::table('maquinarias', function (Blueprint $table) {
            $table->index('id_tipo_maquinaria', 'idx_maquinarias_id_tipo_maquinaria');
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->index('id_unidad_medida', 'idx_insumos_id_unidad_medida');
        });

        Schema::table('mantenimiento_insumos', function (Blueprint $table) {
            $table->index('id_mantenimiento', 'idx_mant_insumos_id_mantenimiento');
            $table->index('id_insumo', 'idx_mant_insumos_id_insumo');
        });

        Schema::table('kit_mantenimiento_preventivo', function (Blueprint $table) {
            $table->index('id_tipo_maquinaria', 'idx_kit_mant_id_tipo_maquinaria');
        });

        Schema::table('lote_empleado', function (Blueprint $table) {
            $table->index('id_empleado', 'idx_lote_empleado_id_empleado');
        });

        Schema::table('lote_maquinaria', function (Blueprint $table) {
            $table->index('id_maquinaria', 'idx_lote_maquinaria_id_maquinaria');
        });

        Schema::table('movimiento_stocks', function (Blueprint $table) {
            $table->index('fecha', 'idx_movimiento_stocks_fecha');
            $table->index('tipo', 'idx_movimiento_stocks_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropIndex('idx_cargas_id_lote');
            $table->dropIndex('idx_cargas_id_parte_diario');
            $table->dropIndex('idx_cargas_fecha_carga');
            $table->dropIndex('idx_cargas_id_chofer');
            $table->dropIndex('idx_cargas_id_categoria_madera');
            $table->dropIndex('idx_cargas_estado');
        });

        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropIndex('idx_parte_diarios_fecha');
            $table->dropIndex('idx_parte_diarios_id_lote');
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropIndex('idx_mantenimientos_id_maquinaria');
            $table->dropIndex('idx_mantenimientos_estado');
            $table->dropIndex('idx_mantenimientos_fecha_fin');
        });

        Schema::table('historico_roles_laborales', function (Blueprint $table) {
            $table->dropIndex('idx_hist_roles_laboral_fecha');
        });

        Schema::table('historico_costos_maquinarias', function (Blueprint $table) {
            $table->dropIndex('idx_hist_costos_id_maquinaria');
        });

        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_inv_id_proveedor');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex('idx_empleados_id_rol_laboral');
        });

        Schema::table('maquinarias', function (Blueprint $table) {
            $table->dropIndex('idx_maquinarias_id_tipo_maquinaria');
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropIndex('idx_insumos_id_unidad_medida');
        });

        Schema::table('mantenimiento_insumos', function (Blueprint $table) {
            $table->dropIndex('idx_mant_insumos_id_mantenimiento');
            $table->dropIndex('idx_mant_insumos_id_insumo');
        });

        Schema::table('kit_mantenimiento_preventivo', function (Blueprint $table) {
            $table->dropIndex('idx_kit_mant_id_tipo_maquinaria');
        });

        Schema::table('lote_empleado', function (Blueprint $table) {
            $table->dropIndex('idx_lote_empleado_id_empleado');
        });

        Schema::table('lote_maquinaria', function (Blueprint $table) {
            $table->dropIndex('idx_lote_maquinaria_id_maquinaria');
        });

        Schema::table('movimiento_stocks', function (Blueprint $table) {
            $table->dropIndex('idx_movimiento_stocks_fecha');
            $table->dropIndex('idx_movimiento_stocks_tipo');
        });
    }
};
