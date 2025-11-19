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
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->decimal('toneladas_snapshot', 12, 2)->nullable()->after('costo_total')
                ->comment('Toneladas acumuladas al momento del mantenimiento');
            $table->decimal('costo_mano_obra', 10, 2)->nullable()->after('toneladas_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn(['toneladas_snapshot', 'costo_mano_obra']);
        });
    }
};
