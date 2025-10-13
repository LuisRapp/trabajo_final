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
        Schema::create('insumos', function (Blueprint $table) {
           $table->id('id_insumo');
            $table->string('nombre', 100);
            $table->string('descripcion', 200)->nullable();
            $table->unsignedBigInteger('id_unidad_medida');
            $table->unsignedBigInteger('id_proveedor')->nullable();
            $table->decimal('costo_unitario', 10, 2);
            $table->timestamps();

            $table->foreign('id_unidad_medida')->references('id_unidad_medida')->on('unidad_medidas')->onDelete('restrict');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
