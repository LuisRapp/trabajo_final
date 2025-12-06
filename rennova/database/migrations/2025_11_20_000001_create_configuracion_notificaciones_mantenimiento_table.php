<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_notificaciones_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_notificacion', ['umbral', 'recordatorio', 'stock'])
                ->comment('Tipo de notificación: umbral=se alcanzó umbral, recordatorio=mantenimiento programado hoy, stock=falta stock');
            $table->timestamps();
            
            $table->unique(['user_id', 'tipo_notificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_notificaciones_mantenimiento');
    }
};
