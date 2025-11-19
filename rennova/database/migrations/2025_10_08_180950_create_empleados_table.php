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
        Schema::create('empleados', function (Blueprint $table) {
           $table->id('id_empleado');
            $table->unsignedBigInteger('id_rol_laboral')->nullable();
            $table->string('dni', 10)->unique();
            $table->string('apellido', 60);
            $table->string('nombre', 60);
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_inicio_actividades');
            $table->date('fecha_fin_actividades')->nullable();
            $table->timestamps();

        $table->foreign('id_rol_laboral')
            ->references('id_rol_laboral')
            ->on('rol_laborals')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
