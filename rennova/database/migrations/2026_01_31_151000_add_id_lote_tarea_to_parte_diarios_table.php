<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_lote_tarea')->nullable()->after('id_lote');
            $table->foreign('id_lote_tarea')->references('id_lote_tarea')->on('lote_tareas');
            $table->index(['id_lote_tarea', 'fecha'], 'idx_parte_diarios_tarea_fecha');
        });

        // Backfill: crear tareas por (lote, tipo_tarea) y vincular los partes existentes.
        $groups = DB::table('parte_diarios')
            ->select([
                'id_lote',
                'tipo_tarea',
                DB::raw('MIN(fecha) as min_fecha'),
                DB::raw('MAX(fecha) as max_fecha'),
            ])
            ->whereNotNull('tipo_tarea')
            ->groupBy(['id_lote', 'tipo_tarea'])
            ->get();

        foreach ($groups as $g) {
            $idLote = (int) $g->id_lote;
            $tipo = (string) $g->tipo_tarea;

            $idTarea = DB::table('lote_tareas')->insertGetId([
                'id_lote' => $idLote,
                'tipo_tarea' => $tipo,
                // Son tareas históricas ya ejecutadas
                'estado' => 'cerrada',
                'fecha_inicio' => $g->min_fecha,
                'fecha_fin' => $g->max_fecha,
                'superficie_afectada_ha' => null,
                'observaciones' => 'Migrado automáticamente desde parte_diarios.tipo_tarea',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_lote_tarea');

            DB::table('parte_diarios')
                ->where('id_lote', $idLote)
                ->where('tipo_tarea', $tipo)
                ->update([
                    'id_lote_tarea' => $idTarea,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropIndex('idx_parte_diarios_tarea_fecha');
            $table->dropForeign(['id_lote_tarea']);
            $table->dropColumn('id_lote_tarea');
        });
    }
};
