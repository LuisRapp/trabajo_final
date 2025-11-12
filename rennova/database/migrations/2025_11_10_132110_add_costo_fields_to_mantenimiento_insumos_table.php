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
        Schema::table('mantenimiento_insumos', function (Blueprint $table) {
            $table->decimal('costo_unitario', 10, 2)->nullable()->after('cantidad_utilizada')
                ->comment('Costo del insumo al momento del uso');
            $table->decimal('subtotal', 10, 2)->nullable()->after('costo_unitario')
                ->comment('cantidad_utilizada * costo_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimiento_insumos', function (Blueprint $table) {
            $table->dropColumn(['costo_unitario', 'subtotal']);
        });
    }
};
