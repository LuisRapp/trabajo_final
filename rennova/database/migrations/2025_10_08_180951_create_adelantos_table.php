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
        Schema::create('adelantos', function (Blueprint $table) {
            $table->id('id_adelanto');
            $table->unsignedBigInteger('id_empleado');
            $table->date('fecha_emision')->default(DB::raw('CURRENT_DATE'));
            $table->decimal('monto', 10, 2);
            $table->string('estado', 15)->default('pendiente');
            $table->timestamps();

            $table->foreign('id_empleado')
                  ->references('id_empleado')
                  ->on('empleado')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adelantos');
    }
};
