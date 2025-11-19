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
        Schema::create('categoria_cliente_precio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('categoria_id');
            $table->decimal('precio', 10, 2);
            $table->date('fecha_vigencia')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('cliente_id')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id_categoria_madera')->on('categoria_maderas')->onDelete('cascade');

            // Unique constraint para evitar duplicados
            $table->unique(['cliente_id', 'categoria_id'], 'unique_cliente_categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_cliente_precio');
    }
};
