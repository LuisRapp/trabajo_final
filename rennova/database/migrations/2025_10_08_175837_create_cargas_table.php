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
        Schema::create('cargas', function (Blueprint $table) {
            $table->id('id_carga');
            $table->unsignedBigInteger('id_lote');
            $table->foreign('id_lote')->references('id_lote')->on('lotes');
            $table->unsignedBigInteger('id_categoria_madera')->nullable();
            $table->foreign('id_categoria_madera')->references('id_categoria_madera')->on('categoria_maderas');
            $table->bigInteger('id_chofer')->nullable();
            $table->unsignedBigInteger('id_parte_diario')->nullable();
            $table->foreign('id_parte_diario')->references('id_parte_diario')->on('parte_diarios');
            $table->string('ticket', 20)->nullable();
            $table->decimal('peso_bruto', 10, 2)->nullable();
            $table->decimal('tara', 10, 2)->nullable();
            $table->decimal('peso_neto', 10, 2)->nullable();
            $table->string('destino', 100)->nullable();
            $table->date('fecha_carga')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargas');
    }
};
