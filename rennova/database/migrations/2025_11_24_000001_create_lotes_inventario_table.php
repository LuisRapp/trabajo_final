<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para tracking de lotes de inventario con método FIFO.
     * Cada compra/entrada de insumos crea un nuevo lote con su precio unitario.
     * Las salidas consumen primero los lotes más antiguos (FIFO - First In First Out).
     */
    public function up(): void
    {
        Schema::create('lotes_inventario', function (Blueprint $table) {
            $table->id('id_lote_inventario');
            $table->unsignedBigInteger('id_insumo');
            $table->unsignedBigInteger('id_proveedor')->nullable()->comment('Proveedor de este lote (si es compra)');
            
            // Cantidades
            $table->decimal('cantidad_inicial', 10, 2)->comment('Cantidad original del lote');
            $table->decimal('cantidad_disponible', 10, 2)->comment('Cantidad aún disponible para consumir');
            
            // Costos
            $table->decimal('precio_unitario', 10, 2)->comment('Precio de compra por unidad en este lote');
            $table->decimal('costo_total', 12, 2)->comment('Costo total del lote (cantidad_inicial * precio_unitario)');
            
            // Metadatos
            $table->date('fecha_compra')->comment('Fecha de adquisición del lote');
            $table->string('numero_factura', 50)->nullable()->comment('Número de factura/remito de compra');
            $table->enum('tipo_movimiento', ['compra', 'ajuste_entrada', 'devolucion'])->default('compra')
                ->comment('Origen del lote: compra normal, ajuste de inventario o devolución');
            $table->text('observaciones')->nullable();
            
            // Estado
            $table->boolean('agotado')->default(false)->comment('True cuando cantidad_disponible = 0');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('id_insumo')->references('id_insumo')->on('insumos')->onDelete('cascade');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors')->onDelete('set null');
            
            // Indexes para performance en consultas FIFO
            $table->index(['id_insumo', 'agotado', 'fecha_compra'], 'idx_fifo_lookup');
            $table->index('fecha_compra');
        });
        
        $driver = DB::connection()->getDriverName();

        // Check constraint: cantidad_disponible no puede ser negativa ni mayor a cantidad_inicial (solo PostgreSQL).
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE lotes_inventario ADD CONSTRAINT chk_cantidad_disponible CHECK (cantidad_disponible >= 0 AND cantidad_disponible <= cantidad_inicial)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes_inventario');
    }
};
