<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saldo descontado del adelanto. Permite descontar parcialmente una
     * liquidacion y arrastrar el resto a la siguiente.
     */
    public function up(): void
    {
        Schema::table('adelantos', function (Blueprint $table) {
            $table->decimal('monto_descontado', 10, 2)->default(0)->after('monto');
        });

        // Los adelantos ya pagados se consideran descontados en su totalidad.
        DB::table('adelantos')
            ->where('estado', 'pagado')
            ->orderBy('id_adelanto')
            ->get(['id_adelanto', 'monto'])
            ->each(function ($adelanto): void {
                DB::table('adelantos')
                    ->where('id_adelanto', $adelanto->id_adelanto)
                    ->update(['monto_descontado' => $adelanto->monto]);
            });
    }

    public function down(): void
    {
        Schema::table('adelantos', function (Blueprint $table) {
            $table->dropColumn('monto_descontado');
        });
    }
};
