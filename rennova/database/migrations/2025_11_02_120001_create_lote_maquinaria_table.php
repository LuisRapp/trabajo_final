<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lote_maquinaria')) {
            Schema::create('lote_maquinaria', function (Blueprint $table) {
                $table->unsignedBigInteger('id_lote');
                $table->unsignedBigInteger('id_maquinaria');
                $table->timestamps();

                $table->primary(['id_lote', 'id_maquinaria']);
                $table->foreign('id_lote')->references('id_lote')->on('lotes')->onDelete('cascade');
                $table->foreign('id_maquinaria')->references('id_maquinaria')->on('maquinarias')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_maquinaria');
    }
};
