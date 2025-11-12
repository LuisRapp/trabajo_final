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
        Schema::create('insumo_kit_preventivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kit_preventivo_id');
            $table->foreign('kit_preventivo_id')->references('id_kit_preventivo')->on('kit_preventivos')->onDelete('cascade');
            $table->unsignedBigInteger('insumo_id');
            $table->foreign('insumo_id')->references('id_insumo')->on('insumos')->onDelete('cascade');
            $table->decimal('cantidad_necesaria', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumo_kit_preventivo');
    }
};
