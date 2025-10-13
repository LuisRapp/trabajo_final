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
        Schema::create('maquinaria_parte_diarios', function (Blueprint $table) {
            $table->id('id_maquinaria_parte');
            $table->bigInteger('id_maquinaria')->constrained('maquinaria');
            $table->foreignId('id_parte_diario')->constrained('parte_diario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinaria_parte_diarios');
    }
};
