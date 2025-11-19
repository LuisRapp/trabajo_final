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
        Schema::table('tipo_maquinarias', function (Blueprint $table) {
            $table->decimal('umbral_toneladas', 10, 2)->nullable()->after('nombre')
                ->comment('Toneladas para generar mantenimiento preventivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_maquinarias', function (Blueprint $table) {
            $table->dropColumn('umbral_toneladas');
        });
    }
};
