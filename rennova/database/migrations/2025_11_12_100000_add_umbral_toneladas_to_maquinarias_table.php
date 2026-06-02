<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campo umbral_toneladas a tabla maquinarias
        Schema::table('maquinarias', function (Blueprint $table) {
            $table->decimal('umbral_toneladas', 10, 2)->nullable()->after('toneladas_acumuladas')
                ->comment('Umbral de toneladas para generar mantenimiento preventivo (específico por máquina)');
        });

        // Migrar datos: copiar umbral_toneladas del tipo_maquinaria a cada maquinaria
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE maquinarias
                SET umbral_toneladas = (
                    SELECT tm.umbral_toneladas
                    FROM tipo_maquinarias tm
                    WHERE tm.id_tipo_maquinaria = maquinarias.id_tipo_maquinaria
                )
                WHERE EXISTS (
                    SELECT 1
                    FROM tipo_maquinarias tm
                    WHERE tm.id_tipo_maquinaria = maquinarias.id_tipo_maquinaria
                      AND tm.umbral_toneladas IS NOT NULL
                )
            SQL);
        } else {
            DB::statement(<<<'SQL'
                UPDATE maquinarias m
                SET umbral_toneladas = tm.umbral_toneladas
                FROM tipo_maquinarias tm
                WHERE m.id_tipo_maquinaria = tm.id_tipo_maquinaria
                AND tm.umbral_toneladas IS NOT NULL
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maquinarias', function (Blueprint $table) {
            $table->dropColumn('umbral_toneladas');
        });
    }
};
