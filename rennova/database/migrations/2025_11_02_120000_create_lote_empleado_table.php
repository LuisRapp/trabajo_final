<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lote_empleado')) {
            Schema::create('lote_empleado', function (Blueprint $table) {
                $table->unsignedBigInteger('id_lote');
                $table->unsignedBigInteger('id_empleado');
                $table->timestamps();

                $table->primary(['id_lote', 'id_empleado']);
                $table->foreign('id_lote')->references('id_lote')->on('lotes')->onDelete('cascade');
                $table->foreign('id_empleado')->references('id_empleado')->on('empleados')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_empleado');
    }
};
