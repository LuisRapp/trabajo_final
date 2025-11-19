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
        Schema::create('carga_empleado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_carga');
            $table->unsignedBigInteger('id_empleado');
            $table->timestamps();

            $table->foreign('id_carga')->references('id_carga')->on('cargas')->onDelete('cascade');
            $table->foreign('id_empleado')->references('id_empleado')->on('empleados')->onDelete('cascade');
            
            // Evitar duplicados
            $table->unique(['id_carga', 'id_empleado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_empleado');
    }
};
