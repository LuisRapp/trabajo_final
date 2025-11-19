<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_maquinaria', function (Blueprint $table) {
            $table->unsignedBigInteger('id_carga');
            $table->unsignedBigInteger('id_maquinaria');
            $table->timestamps();

            $table->primary(['id_carga', 'id_maquinaria']);

            $table->foreign('id_carga')
                ->references('id_carga')->on('cargas')
                ->onDelete('cascade');

            $table->foreign('id_maquinaria')
                ->references('id_maquinaria')->on('maquinarias')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_maquinaria');
    }
};
