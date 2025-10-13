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
        Schema::create('parte_diarios', function (Blueprint $table) {
            $table->id('id_parte_diario');
            $table->unsignedBigInteger('id_lote');
            $table->foreign('id_lote')->references('id_lote')->on('lotes');
            $table->date('fecha');
            $table->boolean('es_dia_caido')->default(false);
            $table->string('observaciones', 120)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parte_diarios');
    }
};
