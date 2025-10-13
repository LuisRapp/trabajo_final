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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id('id_lote');
            $table->string('propietario', 60);
            $table->string('condicion_compra', 20);
            $table->enum('estado', ['activo','cerrado','baja'])->default('activo');
            $table->string('ubicacion', 100);
            $table->string('especie', 50);
            $table->decimal('superficie', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
