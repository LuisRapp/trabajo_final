<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejecuciones_automatizacion', function (Blueprint $table) {
            $table->id();
            $table->string('comando')->unique();
            $table->timestamp('ultima_ejecucion_at')->nullable();
            $table->string('estado', 20)->default('ok');
            $table->text('motivo_error')->nullable();
            $table->unsignedInteger('duracion_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_automatizacion');
    }
};
