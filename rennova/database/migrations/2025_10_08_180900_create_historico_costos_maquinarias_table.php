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
        Schema::create('historico_costos_maquinarias', function (Blueprint $table) {
           $table->id('id_costo');
            $table->foreignId('id_maquinaria')->constrained('maquinaria');
            $table->decimal('costo_por_tonelada', 10, 2)->nullable();
            $table->date('fecha_inicio_vigencia');
            $table->date('fecha_fin_vigencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_costos_maquinarias');
    }
};
