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
        Schema::table('insumos', function (Blueprint $table) {
            // Hacer nullable la columna costo_unitario ya que ahora usamos FIFO
            $table->decimal('costo_unitario', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            // Revertir a NOT NULL
            $table->decimal('costo_unitario', 10, 2)->nullable(false)->change();
        });
    }
};
