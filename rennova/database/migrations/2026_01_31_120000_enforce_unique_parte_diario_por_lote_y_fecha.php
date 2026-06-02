<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Normalizar datos existentes: si hay más de 1 Parte Diario por (lote, fecha),
        // consolidar relaciones en el registro más reciente y eliminar los duplicados.
        $duplicateGroups = DB::table('parte_diarios')
            ->select('id_lote', 'fecha', DB::raw('COUNT(*) as cnt'))
            ->groupBy('id_lote', 'fecha')
            // PostgreSQL no permite referenciar el alias en HAVING.
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $ids = DB::table('parte_diarios')
                ->where('id_lote', $group->id_lote)
                ->where('fecha', $group->fecha)
                ->orderByDesc('id_parte_diario')
                ->pluck('id_parte_diario')
                ->all();

            if (count($ids) < 2) {
                continue;
            }

            $keepId = array_shift($ids);

            foreach ($ids as $oldId) {
                // Consolidar Cargas
                if (Schema::hasTable('cargas')) {
                    DB::table('cargas')
                        ->where('id_parte_diario', $oldId)
                        ->update(['id_parte_diario' => $keepId]);
                }

                // Consolidar empleados del Parte Diario (tabla tiene UNIQUE por (parte, empleado))
                if (Schema::hasTable('parte_diario_empleado')) {
                    $empleados = DB::table('parte_diario_empleado')
                        ->where('id_parte_diario', $oldId)
                        ->pluck('id_empleado')
                        ->all();

                    foreach ($empleados as $idEmpleado) {
                        DB::table('parte_diario_empleado')->updateOrInsert(
                            ['id_parte_diario' => $keepId, 'id_empleado' => $idEmpleado],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }

                    DB::table('parte_diario_empleado')
                        ->where('id_parte_diario', $oldId)
                        ->delete();
                }

                // Consolidar maquinarias del Parte Diario
                if (Schema::hasTable('maquinaria_parte_diarios')) {
                    DB::table('maquinaria_parte_diarios')
                        ->where('id_parte_diario', $oldId)
                        ->update(['id_parte_diario' => $keepId]);
                }

                // Movimientos de stock: se referencian por texto (motivo). No es seguro re-mapear.
                // Para evitar ruido, los eliminamos para los partes duplicados.
                if (Schema::hasTable('movimiento_stocks')) {
                    DB::table('movimiento_stocks')
                        ->where('motivo', 'like', 'Parte Diario #' . $oldId . ' - %')
                        ->delete();
                }

                // Eliminar Parte Diario duplicado
                DB::table('parte_diarios')
                    ->where('id_parte_diario', $oldId)
                    ->delete();
            }
        }

        // 1.b) Deduplicar maquinarias por (id_parte_diario, id_maquinaria) si existieran.
        // Evitamos SQL específico (MySQL DELETE JOIN) para compatibilidad.
        if (Schema::hasTable('maquinaria_parte_diarios')) {
            $dupMaqGroups = DB::table('maquinaria_parte_diarios')
                ->select('id_parte_diario', 'id_maquinaria', DB::raw('COUNT(*) as cnt'))
                ->groupBy('id_parte_diario', 'id_maquinaria')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($dupMaqGroups as $g) {
                $maqParteIds = DB::table('maquinaria_parte_diarios')
                    ->where('id_parte_diario', $g->id_parte_diario)
                    ->where('id_maquinaria', $g->id_maquinaria)
                    ->orderBy('id_maquinaria_parte')
                    ->pluck('id_maquinaria_parte')
                    ->all();

                // Mantener el primero, borrar el resto
                array_shift($maqParteIds);
                if (!empty($maqParteIds)) {
                    DB::table('maquinaria_parte_diarios')
                        ->whereIn('id_maquinaria_parte', $maqParteIds)
                        ->delete();
                }
            }
        }

        // 2) Enforce de integridad: 1 Parte Diario por (lote, fecha)
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->unique(['id_lote', 'fecha'], 'uq_parte_diarios_lote_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropUnique('uq_parte_diarios_lote_fecha');
        });
    }
};
