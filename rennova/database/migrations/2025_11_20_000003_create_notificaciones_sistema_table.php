<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->unsignedBigInteger('mantenimiento_id')->nullable();
            $table->foreign('mantenimiento_id')->references('id_mantenimiento')->on('mantenimientos')->onDelete('cascade');
            $table->enum('tipo', ['umbral_alcanzado', 'stock_insuficiente', 'recordatorio_programado', 'mantenimiento_vencido']);
            $table->string('titulo');
            $table->text('mensaje');
            $table->date('fecha_limite')->nullable()->comment('Fecha límite para accionar (7 días desde creación para umbral_alcanzado)');
            $table->boolean('leida')->default(false);
            $table->boolean('accionada')->default(false)->comment('Si el usuario ya tomó acción (programó el mantenimiento)');
            $table->timestamp('leida_at')->nullable();
            $table->timestamp('accionada_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'leida']);
            $table->index(['user_id', 'accionada']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_sistema');
    }
};
