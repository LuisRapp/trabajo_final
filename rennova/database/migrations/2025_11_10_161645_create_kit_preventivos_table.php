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
        Schema::create('kit_preventivos', function (Blueprint $table) {
            $table->id('id_kit_preventivo');
            $table->string('nombre_kit');
            $table->unsignedBigInteger('id_tipo_maquinaria');
            $table->foreign('id_tipo_maquinaria')->references('id_tipo_maquinaria')->on('tipo_maquinarias')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_preventivos');
    }
};
