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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('nombre');
            $table->string('tipo'); // ejemplo: "Inventario", "Mantenimiento", "Ventas", etc.
            $table->json('parametros')->nullable(); // filtros usados para generar el reporte
            $table->string('formato')->default('PDF'); // PDF, XLSX, CSV, etc.
            $table->string('ruta_archivo')->nullable(); // ruta del archivo generado
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
