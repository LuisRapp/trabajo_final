<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Abort if any cargas still have NULL id_cliente
        $nullCount = DB::table('cargas')
            ->whereNull('id_cliente')
            ->whereNull('deleted_at')
            ->count();

        if ($nullCount > 0) {
            $unmatchedIds = DB::table('cargas')
                ->whereNull('id_cliente')
                ->whereNull('deleted_at')
                ->pluck('id_carga')
                ->implode(', ');

            throw new \RuntimeException(
                "Cannot make id_cliente NOT NULL: {$nullCount} cargas rows still have NULL id_cliente. "
                ."Unmatched id_carga values: {$unmatchedIds}"
            );
        }

        Schema::table('cargas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cliente')->nullable(false)->change();
        });

        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn('destino');
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->string('destino', 100)->nullable();
        });

        // Restore destino from clientes.razon_social via FK join
        DB::statement('
            UPDATE cargas
            SET destino = c.razon_social
            FROM clientes c
            WHERE cargas.id_cliente = c.id_cliente
              AND cargas.deleted_at IS NULL
        ');

        Schema::table('cargas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cliente')->nullable()->change();
        });
    }
};
