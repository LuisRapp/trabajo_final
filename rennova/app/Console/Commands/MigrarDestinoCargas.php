<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Carga;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class MigrarDestinoCargas extends Command
{
    protected $signature = 'cargas:migrar-destino';
    protected $description = 'Migra el campo destino de las cargas de ID numérico a nombre del cliente';

    public function handle()
    {
        $this->info('Iniciando migración de campo destino en cargas...');
        
        // Obtener todas las cargas y filtrar las que tienen IDs numéricos
        $todasLasCargas = Carga::whereNotNull('destino')->get();
        
        $cargasConIdNumerico = $todasLasCargas->filter(function($carga) {
            return is_numeric($carga->destino) && (int)$carga->destino > 0;
        });
        
        $total = $cargasConIdNumerico->count();
        
        if ($total === 0) {
            $this->info('No se encontraron cargas con IDs numéricos en el campo destino.');
            return 0;
        }
        
        $this->info("Se encontraron {$total} cargas para migrar.");
        
        $migradas = 0;
        $errores = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($cargasConIdNumerico as $carga) {
                $cliente = Cliente::find((int)$carga->destino);
                
                if ($cliente) {
                    $carga->destino = $cliente->razon_social;
                    $carga->save();
                    $migradas++;
                    $this->line("✓ Carga ID {$carga->id_carga}: {$cliente->id_cliente} → {$cliente->razon_social}");
                } else {
                    $errores++;
                    $this->error("✗ Carga ID {$carga->id_carga}: Cliente ID {$carga->destino} no encontrado");
                }
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("Migración completada:");
            $this->info("  - Cargas migradas: {$migradas}");
            if ($errores > 0) {
                $this->warn("  - Errores: {$errores}");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error durante la migración: ' . $e->getMessage());
            return 1;
        }
    }
}
