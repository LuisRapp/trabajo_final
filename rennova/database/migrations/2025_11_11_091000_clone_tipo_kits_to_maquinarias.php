<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        // Clonar kits definidos por tipo hacia cada maquinaria concreta
        $legacyItems = DB::table('kit_mantenimiento_preventivo')
            ->whereNull('id_maquinaria')
            ->get();

        foreach ($legacyItems as $item) {
            $maquinarias = DB::table('maquinarias')
                ->where('id_tipo_maquinaria', $item->id_tipo_maquinaria)
                ->get(['id_maquinaria']);

            foreach ($maquinarias as $maq) {
                $exists = DB::table('kit_mantenimiento_preventivo')
                    ->where('id_maquinaria', $maq->id_maquinaria)
                    ->where('id_insumo', $item->id_insumo)
                    ->exists();

                if (!$exists) {
                    DB::table('kit_mantenimiento_preventivo')->insert([
                        'id_tipo_maquinaria' => $item->id_tipo_maquinaria, // mantener referencia legado
                        'id_maquinaria' => $maq->id_maquinaria,
                        'id_insumo' => $item->id_insumo,
                        'cantidad_requerida' => $item->cantidad_requerida,
                        'es_obligatorio' => $item->es_obligatorio,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Log::info('Migración de kits por tipo -> kits por maquinaria ejecutada.');
    }

    public function down(): void
    {
        // Revertir sólo clones (los que tienen id_maquinaria y coinciden con un legacy base sin id_maquinaria)
        $legacyBases = DB::table('kit_mantenimiento_preventivo')
            ->whereNull('id_maquinaria')
            ->get(['id_tipo_maquinaria','id_insumo']);

        foreach ($legacyBases as $base) {
            DB::table('kit_mantenimiento_preventivo')
                ->whereNotNull('id_maquinaria')
                ->where('id_tipo_maquinaria', $base->id_tipo_maquinaria)
                ->where('id_insumo', $base->id_insumo)
                ->delete();
        }
    }
};
