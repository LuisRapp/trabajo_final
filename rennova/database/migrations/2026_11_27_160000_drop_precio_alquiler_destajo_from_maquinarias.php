<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El costo por tonelada de una maquina ya vive en
     * `historico_costos_maquinarias` (con vigencia por fecha); este campo
     * duplicado se elimina.
     */
    public function up(): void
    {
        Schema::table('maquinarias', function (Blueprint $table) {
            $table->dropColumn('precio_alquiler_destajo');
        });
    }

    public function down(): void
    {
        Schema::table('maquinarias', function (Blueprint $table) {
            $table->decimal('precio_alquiler_destajo', 10, 2)->nullable()->after('umbral_toneladas');
        });
    }
};
