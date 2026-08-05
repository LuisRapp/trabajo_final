<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        // Abort if duplicate razon_social exists in clientes
        $duplicates = DB::select('
            SELECT razon_social, COUNT(*) as cnt
            FROM clientes
            WHERE deleted_at IS NULL
            GROUP BY razon_social
            HAVING COUNT(*) > 1
        ');

        if (! empty($duplicates)) {
            $names = collect($duplicates)->pluck('razon_social')->implode(', ');
            throw new \RuntimeException(
                "Backfill aborted: duplicate razon_social found in clientes table: {$names}. "
                .'Clean up duplicates before running this migration.'
            );
        }

        // Backfill: match cargas.destino to clientes.razon_social
        DB::statement('
            UPDATE cargas
            SET id_cliente = c.id_cliente
            FROM clientes c
            WHERE cargas.destino = c.razon_social
              AND c.deleted_at IS NULL
              AND cargas.deleted_at IS NULL
        ');

        // Log unmatched rows
        $unmatched = DB::select('
            SELECT id_carga, destino
            FROM cargas
            WHERE id_cliente IS NULL
              AND destino IS NOT NULL
              AND deleted_at IS NULL
        ');

        if (! empty($unmatched)) {
            Log::warning('Backfill migration: unmatched cargas rows', [
                'count' => count($unmatched),
                'rows' => collect($unmatched)->map(fn ($r) => [
                    'id_carga' => $r->id_carga,
                    'destino' => $r->destino,
                ])->toArray(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('cargas')->update(['id_cliente' => null]);
    }
};
