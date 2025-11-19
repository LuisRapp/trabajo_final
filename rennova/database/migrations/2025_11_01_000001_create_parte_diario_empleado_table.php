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
        Schema::create('parte_diario_empleado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_parte_diario');
            $table->unsignedBigInteger('id_empleado');
            $table->timestamps();

            $table->foreign('id_parte_diario')->references('id_parte_diario')->on('parte_diarios')->onDelete('cascade');
            $table->foreign('id_empleado')->references('id_empleado')->on('empleados')->onDelete('cascade');
            
            // Evitar duplicados
            $table->unique(['id_parte_diario', 'id_empleado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parte_diario_empleado');
    }
};
