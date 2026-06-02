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
     * Refactorización para agregar tracking de precios y FIFO a movimientos de stock
     */
    public function up(): void
    {
        Schema::table('movimiento_stocks', function (Blueprint $table) {
            // Agregar precio unitario del movimiento (nullable temporalmente)
            // Para entradas: precio de compra
            // Para salidas: precio FIFO calculado automáticamente
            $table->decimal('precio_unitario', 10, 2)->nullable()->after('cantidad');
            
            // Referencia al lote consumido (solo para salidas procesadas por FIFO)
            // Nullable porque las entradas crean lotes nuevos
            $table->unsignedBigInteger('id_lote_inventario')->nullable()->after('precio_unitario');
            
            // Costo total del movimiento = cantidad × precio_unitario
            // Facilita reportes sin recalcular
            $table->decimal('costo_total_movimiento', 12, 2)->nullable()->after('id_lote_inventario');
            
            // Foreign key al lote consumido
            $table->foreign('id_lote_inventario')
                  ->references('id_lote_inventario')
                  ->on('lotes_inventario')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Índice para consultas de movimientos por lote
            $table->index('id_lote_inventario', 'idx_movimiento_lote');
            
            // Índice compuesto para reportes por insumo y fecha
            $table->index(['id_insumo', 'fecha', 'tipo'], 'idx_movimiento_reportes');
        });
        
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("\n                UPDATE movimiento_stocks\n                SET precio_unitario = COALESCE(\n                        (SELECT i.costo_unitario FROM insumos i WHERE i.id_insumo = movimiento_stocks.id_insumo),
                        1
                    ),
                    costo_total_movimiento = cantidad * COALESCE(\n                        (SELECT i.costo_unitario FROM insumos i WHERE i.id_insumo = movimiento_stocks.id_insumo),
                        1
                    )
                WHERE precio_unitario IS NULL
            ");

            return;
        }

        // Backfill precio_unitario con el costo_unitario del insumo para registros históricos (PostgreSQL)
        DB::statement("\n            UPDATE movimiento_stocks ms
            SET precio_unitario = COALESCE(i.costo_unitario, 1),
                costo_total_movimiento = ms.cantidad * COALESCE(i.costo_unitario, 1)
            FROM insumos i
            WHERE ms.id_insumo = i.id_insumo
              AND ms.precio_unitario IS NULL
        ");

        // Hacer NOT NULL después del backfill (PostgreSQL)
        DB::statement('ALTER TABLE movimiento_stocks ALTER COLUMN precio_unitario SET NOT NULL');
        DB::statement('ALTER TABLE movimiento_stocks ALTER COLUMN costo_total_movimiento SET NOT NULL');

        // Agregar CHECK constraint para validar que precio_unitario > 0 (PostgreSQL)
        DB::statement('ALTER TABLE movimiento_stocks ADD CONSTRAINT chk_precio_positivo CHECK (precio_unitario > 0)');

        // Agregar CHECK constraint para validar que costo_total = cantidad × precio_unitario (PostgreSQL)
        DB::statement('ALTER TABLE movimiento_stocks ADD CONSTRAINT chk_costo_calculado CHECK (ABS(costo_total_movimiento - (cantidad * precio_unitario)) < 0.01)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::table('movimiento_stocks', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                // Eliminar constraints primero
                $table->dropConstraint('chk_precio_positivo');
                $table->dropConstraint('chk_costo_calculado');
            }

            // Eliminar índices
            $table->dropIndex('idx_movimiento_lote');
            $table->dropIndex('idx_movimiento_reportes');

            // Eliminar foreign key
            $table->dropForeign(['id_lote_inventario']);

            // Eliminar columnas
            $table->dropColumn([
                'precio_unitario',
                'id_lote_inventario',
                'costo_total_movimiento'
            ]);
        });
    }
};
