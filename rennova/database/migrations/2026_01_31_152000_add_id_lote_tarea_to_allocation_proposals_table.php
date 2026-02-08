<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->unsignedBigInteger('id_lote_tarea')->nullable()->after('id_lote');
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas');
            $table->index(['id_lote_tarea', 'created_at'], 'idx_alloc_prop_tarea_created');
        });
    }

    public function down(): void
    {
        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->dropIndex('idx_alloc_prop_tarea_created');
            $table->dropForeign(['id_lote_tarea']);
            $table->dropColumn('id_lote_tarea');
        });
    }
};
