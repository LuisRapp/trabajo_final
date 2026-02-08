<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_tareas', function (Blueprint $table) {
            $table->dropForeign(['id_lote']);
            $table->foreign('id_lote')->references('id_lote')->on('lotes')->onDelete('cascade');
        });

        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->dropForeign(['id_lote_tarea']);
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas')->nullOnDelete();
        });

        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropForeign(['id_lote_tarea']);
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas')->nullOnDelete();
        });

        DB::statement('ALTER TABLE lote_tareas DROP CONSTRAINT IF EXISTS lote_tareas_estado_check');
        DB::statement("ALTER TABLE lote_tareas ADD CONSTRAINT lote_tareas_estado_check CHECK (estado IN ('planificada','en_ejecucion','cerrada'))");

        DB::statement('ALTER TABLE lote_tareas DROP CONSTRAINT IF EXISTS lote_tareas_tipo_tarea_check');
        DB::statement("ALTER TABLE lote_tareas ADD CONSTRAINT lote_tareas_tipo_tarea_check CHECK (tipo_tarea IN ('tala_rasa','raleo','poda','limpieza'))");

        DB::statement('ALTER TABLE allocation_proposals DROP CONSTRAINT IF EXISTS allocation_proposals_status_check');
        DB::statement("ALTER TABLE allocation_proposals ADD CONSTRAINT allocation_proposals_status_check CHECK (status IN ('draft','confirmed','applied','closed'))");

        DB::statement('ALTER TABLE allocation_proposals DROP CONSTRAINT IF EXISTS allocation_proposals_tipo_tarea_check');
        DB::statement("ALTER TABLE allocation_proposals ADD CONSTRAINT allocation_proposals_tipo_tarea_check CHECK (tipo_tarea IN ('tala_rasa','raleo','poda','limpieza'))");

        DB::statement('ALTER TABLE parte_diarios DROP CONSTRAINT IF EXISTS parte_diarios_tipo_tarea_check');
        DB::statement("ALTER TABLE parte_diarios ADD CONSTRAINT parte_diarios_tipo_tarea_check CHECK (tipo_tarea IS NULL OR tipo_tarea IN ('tala_rasa','raleo','poda','limpieza'))");

        DB::statement('ALTER TABLE lotes DROP CONSTRAINT IF EXISTS lotes_main_task_type_check');
        DB::statement("ALTER TABLE lotes ADD CONSTRAINT lotes_main_task_type_check CHECK (main_task_type IS NULL OR main_task_type IN ('tala_rasa','raleo','poda','limpieza'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE lote_tareas DROP CONSTRAINT IF EXISTS lote_tareas_estado_check');
        DB::statement('ALTER TABLE lote_tareas DROP CONSTRAINT IF EXISTS lote_tareas_tipo_tarea_check');
        DB::statement('ALTER TABLE allocation_proposals DROP CONSTRAINT IF EXISTS allocation_proposals_status_check');
        DB::statement('ALTER TABLE allocation_proposals DROP CONSTRAINT IF EXISTS allocation_proposals_tipo_tarea_check');
        DB::statement('ALTER TABLE parte_diarios DROP CONSTRAINT IF EXISTS parte_diarios_tipo_tarea_check');
        DB::statement('ALTER TABLE lotes DROP CONSTRAINT IF EXISTS lotes_main_task_type_check');

        Schema::table('lote_tareas', function (Blueprint $table) {
            $table->dropForeign(['id_lote']);
            $table->foreign('id_lote')->references('id_lote')->on('lotes');
        });

        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->dropForeign(['id_lote_tarea']);
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas');
        });

        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropForeign(['id_lote_tarea']);
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas');
        });
    }
};
