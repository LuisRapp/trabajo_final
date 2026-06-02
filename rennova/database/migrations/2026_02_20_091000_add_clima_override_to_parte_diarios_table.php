<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->boolean('clima_override')->default(false)->after('es_dia_caido');
            $table->string('clima_override_motivo', 255)->nullable()->after('clima_override');
            $table->unsignedBigInteger('clima_override_confirmado_por')->nullable()->after('clima_override_motivo');
            $table->timestamp('clima_override_confirmado_at')->nullable()->after('clima_override_confirmado_por');

            $table->foreign('clima_override_confirmado_por')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parte_diarios', function (Blueprint $table) {
            $table->dropForeign(['clima_override_confirmado_por']);
            $table->dropColumn([
                'clima_override',
                'clima_override_motivo',
                'clima_override_confirmado_por',
                'clima_override_confirmado_at',
            ]);
        });
    }
};
