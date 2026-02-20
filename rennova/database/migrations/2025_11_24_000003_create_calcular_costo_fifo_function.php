<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea stored procedure para calcular costo FIFO y consumir lotes automáticamente
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('
            CREATE OR REPLACE FUNCTION calcular_costo_fifo(
                p_id_insumo BIGINT,
                p_cantidad_salida NUMERIC(10,2),
                OUT v_costo_total NUMERIC(12,2),
                OUT v_lotes_consumidos JSONB
            ) 
            RETURNS RECORD
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_cantidad_restante NUMERIC(10,2);
                v_lote RECORD;
                v_cantidad_a_consumir NUMERIC(10,2);
                v_lotes_array JSONB := \'[]\'::jsonb;
                v_stock_disponible NUMERIC(10,2);
            BEGIN
                -- Inicializar variables
                v_cantidad_restante := p_cantidad_salida;
                v_costo_total := 0;
                
                -- Validar que la cantidad sea positiva
                IF p_cantidad_salida <= 0 THEN
                    RAISE EXCEPTION \'La cantidad de salida debe ser mayor a cero. Recibido: %\', p_cantidad_salida;
                END IF;
                
                -- Verificar stock disponible total
                SELECT COALESCE(SUM(cantidad_disponible), 0)
                INTO v_stock_disponible
                FROM lotes_inventario
                WHERE id_insumo = p_id_insumo 
                  AND agotado = false
                  AND deleted_at IS NULL;
                
                IF v_stock_disponible < p_cantidad_salida THEN
                    RAISE EXCEPTION \'Stock insuficiente. Disponible: %, Requerido: %\', v_stock_disponible, p_cantidad_salida;
                END IF;
                
                -- Consumir lotes en orden FIFO (primero los más antiguos)
                FOR v_lote IN 
                    SELECT id_lote_inventario, cantidad_disponible, precio_unitario, fecha_compra
                    FROM lotes_inventario
                    WHERE id_insumo = p_id_insumo 
                      AND agotado = false
                      AND deleted_at IS NULL
                    ORDER BY fecha_compra ASC, id_lote_inventario ASC
                LOOP
                    -- Salir si ya consumimos toda la cantidad
                    EXIT WHEN v_cantidad_restante <= 0;
                    
                    -- Calcular cuánto consumir de este lote
                    v_cantidad_a_consumir := LEAST(v_lote.cantidad_disponible, v_cantidad_restante);
                    
                    -- Actualizar cantidad disponible del lote
                    UPDATE lotes_inventario
                    SET cantidad_disponible = cantidad_disponible - v_cantidad_a_consumir,
                        agotado = CASE 
                            WHEN cantidad_disponible - v_cantidad_a_consumir <= 0 THEN true
                            ELSE false
                        END,
                        updated_at = NOW()
                    WHERE id_lote_inventario = v_lote.id_lote_inventario;
                    
                    -- Acumular costo
                    v_costo_total := v_costo_total + (v_cantidad_a_consumir * v_lote.precio_unitario);
                    
                    -- Agregar al array de lotes consumidos
                    v_lotes_array := v_lotes_array || jsonb_build_object(
                        \'id_lote_inventario\', v_lote.id_lote_inventario,
                        \'cantidad_consumida\', v_cantidad_a_consumir,
                        \'precio_unitario\', v_lote.precio_unitario,
                        \'costo_parcial\', v_cantidad_a_consumir * v_lote.precio_unitario,
                        \'fecha_compra\', v_lote.fecha_compra
                    );
                    
                    -- Reducir cantidad restante
                    v_cantidad_restante := v_cantidad_restante - v_cantidad_a_consumir;
                END LOOP;
                
                -- Asignar resultado
                v_lotes_consumidos := v_lotes_array;
                
                -- Verificación final
                IF v_cantidad_restante > 0.001 THEN
                    RAISE EXCEPTION \'Error en cálculo FIFO. Cantidad no consumida: %\', v_cantidad_restante;
                END IF;
                
            END;
            $$;
        ');
        
        // Crear función auxiliar para obtener stock disponible por insumo
        DB::unprepared('
            CREATE OR REPLACE FUNCTION obtener_stock_disponible(p_id_insumo BIGINT)
            RETURNS NUMERIC(10,2)
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_stock NUMERIC(10,2);
            BEGIN
                SELECT COALESCE(SUM(cantidad_disponible), 0)
                INTO v_stock
                FROM lotes_inventario
                WHERE id_insumo = p_id_insumo 
                  AND agotado = false
                  AND deleted_at IS NULL;
                
                RETURN v_stock;
            END;
            $$;
        ');
        
        // Crear función auxiliar para obtener precio promedio ponderado actual
        DB::unprepared('
            CREATE OR REPLACE FUNCTION obtener_precio_promedio(p_id_insumo BIGINT)
            RETURNS NUMERIC(10,2)
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_precio_promedio NUMERIC(10,2);
            BEGIN
                SELECT COALESCE(
                    SUM(cantidad_disponible * precio_unitario) / NULLIF(SUM(cantidad_disponible), 0),
                    0
                )
                INTO v_precio_promedio
                FROM lotes_inventario
                WHERE id_insumo = p_id_insumo 
                  AND agotado = false
                  AND deleted_at IS NULL;
                
                RETURN v_precio_promedio;
            END;
            $$;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP FUNCTION IF EXISTS calcular_costo_fifo(BIGINT, NUMERIC);');
        DB::unprepared('DROP FUNCTION IF EXISTS obtener_stock_disponible(BIGINT);');
        DB::unprepared('DROP FUNCTION IF EXISTS obtener_precio_promedio(BIGINT);');
    }
};
