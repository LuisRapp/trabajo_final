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
        Schema::create('mantenimiento_insumos', function (Blueprint $table) {
            $table->id('id_mantenimiento_insumo');
            $table->unsignedBigInteger('id_mantenimiento');
            $table->foreign('id_mantenimiento')->references('id_mantenimiento')->on('mantenimientos');
            $table->unsignedBigInteger('id_insumo');
            $table->foreign('id_insumo')->references('id_insumo')->on('insumos');
            $table->unsignedBigInteger('id_movimiento')->nullable();
            $table->foreign('id_movimiento')->references('id_movimiento_stock')->on('movimiento_stocks');
            $table->decimal('cantidad_utilizada', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_insumos');
    }
};