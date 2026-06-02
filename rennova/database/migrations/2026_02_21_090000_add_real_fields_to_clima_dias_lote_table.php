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
        Schema::table('clima_dias_lote', function (Blueprint $table) {
            $table->string('estado_pronostico', 24)->nullable()->after('estado_operativo');
            $table->string('razon_pronostico', 180)->nullable()->after('razon');
            $table->string('fuente_pronostico', 20)->nullable()->after('fuente');
            $table->text('api_error_pronostico')->nullable()->after('api_error');
            $table->timestamp('pronostico_actualizado_at')->nullable()->after('api_error_pronostico');

            $table->string('estado_real', 24)->nullable()->after('pronostico_actualizado_at');
            $table->string('razon_real', 180)->nullable()->after('estado_real');
            $table->string('fuente_real', 20)->nullable()->after('razon_real');
            $table->text('api_error_real')->nullable()->after('fuente_real');
            $table->timestamp('real_actualizado_at')->nullable()->after('api_error_real');
        });

        DB::table('clima_dias_lote')
            ->whereNull('estado_pronostico')
            ->update([
                'estado_pronostico' => DB::raw('estado_operativo'),
                'razon_pronostico' => DB::raw('razon'),
                'fuente_pronostico' => DB::raw('fuente'),
                'api_error_pronostico' => DB::raw('api_error'),
                'pronostico_actualizado_at' => DB::raw('updated_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clima_dias_lote', function (Blueprint $table) {
            $table->dropColumn([
                'estado_pronostico',
                'razon_pronostico',
                'fuente_pronostico',
                'api_error_pronostico',
                'pronostico_actualizado_at',
                'estado_real',
                'razon_real',
                'fuente_real',
                'api_error_real',
                'real_actualizado_at',
            ]);
        });
    }
};
