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
            $table->foreignId('id_mantenimiento')->constrained('mantenimiento');
            $table->foreignId('id_insumo')->constrained('insumo');
            $table->foreignId('id_movimiento')->nullable()->constrained('movimiento_stock');
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
