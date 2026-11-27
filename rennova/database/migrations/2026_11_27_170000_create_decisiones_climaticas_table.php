<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Decision climatica diaria por lote. La genera el proceso automatico
     * (clima:decisiones) y es la que se muestra en Estado de procesos.
     */
    public function up(): void
    {
        Schema::create('decisiones_climaticas', function (Blueprint $table) {
            $table->id('id_decision');
            $table->foreignId('id_lote')->constrained('lotes', 'id_lote')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estrategia', 30);
            $table->string('nivel', 20);
            $table->unsignedSmallInteger('dias_no_operativos')->default(0);
            $table->decimal('deficit_tn', 12, 2)->default(0);
            $table->decimal('residual_tn', 12, 2)->default(0);
            $table->decimal('meta_diaria', 12, 2)->default(0);
            $table->text('mensaje')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id_lote', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisiones_climaticas');
    }
};
