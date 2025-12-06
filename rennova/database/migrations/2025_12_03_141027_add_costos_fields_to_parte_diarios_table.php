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
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->decimal('costo_mano_obra', 12, 2)->nullable()->after('observaciones');
            $table->decimal('costo_insumos', 12, 2)->nullable()->after('costo_mano_obra');
            $table->decimal('costo_maquinaria', 12, 2)->nullable()->after('costo_insumos');
            $table->decimal('costo_total_dia', 12, 2)->nullable()->after('costo_maquinaria');
            $table->decimal('costo_unitario_calculado', 12, 2)->nullable()->after('costo_total_dia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropColumn([
                'costo_mano_obra',
                'costo_insumos',
                'costo_maquinaria',
                'costo_total_dia',
                'costo_unitario_calculado'
            ]);
        });
    }
};
