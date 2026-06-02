<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('movimiento_stocks', 'id_parte_diario')) {
                $table->unsignedBigInteger('id_parte_diario')->nullable()->after('id_lote_inventario');
                $table->index('id_parte_diario', 'idx_movimiento_parte_diario');
                $table->foreign('id_parte_diario')
                    ->references('id_parte_diario')
                    ->on('parte_diarios')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_stocks', function (Blueprint $table) {
            if (Schema::hasColumn('movimiento_stocks', 'id_parte_diario')) {
                $table->dropForeign(['id_parte_diario']);
                $table->dropIndex('idx_movimiento_parte_diario');
                $table->dropColumn('id_parte_diario');
            }
        });
    }
};
