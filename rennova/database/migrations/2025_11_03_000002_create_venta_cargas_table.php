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
        Schema::create('venta_cargas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_venta');
            $table->unsignedBigInteger('id_carga');
            $table->decimal('precio_unitario', 10, 2); // Precio por tonelada al momento de la venta
            $table->decimal('peso_toneladas', 10, 3); // Peso en toneladas (calculado)
            $table->decimal('subtotal', 10, 2); // Subtotal de esta carga
            $table->timestamps();

            $table->primary(['id_venta', 'id_carga']);

            $table->foreign('id_venta')
                ->references('id_recibo')->on('ventas')
                ->onDelete('cascade');

            $table->foreign('id_carga')
                ->references('id_carga')->on('cargas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_cargas');
    }
};
