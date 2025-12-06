<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Clave de configuración');
            $table->text('valor')->comment('Valor de la configuración');
            $table->string('descripcion')->nullable()->comment('Descripción del parámetro');
            $table->string('tipo')->default('string')->comment('Tipo de dato: string, integer, time, boolean');
            $table->timestamps();
        });

        // Insertar valores por defecto
        DB::table('configuracion_sistema')->insert([
            [
                'clave' => 'mantenimiento_hora_verificacion_umbrales',
                'valor' => '*/30 * * * *',
                'descripcion' => 'Expresión cron para verificación de umbrales (cada 30 min por defecto)',
                'tipo' => 'cron',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'mantenimiento_hora_recordatorio',
                'valor' => '08:00',
                'descripcion' => 'Hora para enviar recordatorios de mantenimientos programados (formato HH:MM)',
                'tipo' => 'time',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
