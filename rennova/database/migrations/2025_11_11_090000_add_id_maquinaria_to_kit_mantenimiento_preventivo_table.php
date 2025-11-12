<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kit_mantenimiento_preventivo', function (Blueprint $table) {
            if (!Schema::hasColumn('kit_mantenimiento_preventivo', 'id_maquinaria')) {
                $table->unsignedBigInteger('id_maquinaria')->nullable()->after('id_tipo_maquinaria');
                $table->foreign('id_maquinaria')->references('id_maquinaria')->on('maquinarias')->onDelete('cascade');
                $table->index(['id_maquinaria']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('kit_mantenimiento_preventivo', function (Blueprint $table) {
            if (Schema::hasColumn('kit_mantenimiento_preventivo', 'id_maquinaria')) {
                $table->dropForeign(['id_maquinaria']);
                $table->dropIndex(['id_maquinaria']);
                $table->dropColumn('id_maquinaria');
            }
        });
    }
};
