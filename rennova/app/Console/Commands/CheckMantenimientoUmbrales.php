<?php

namespace App\Console\Commands;

use App\Services\MantenimientoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMantenimientoUmbrales extends Command
{
    protected $signature = 'mantenimiento:check-umbrales {--maquinaria=}';

    protected $description = 'Verifica umbrales, programa por clima y genera orden de mantenimiento con personal asignado';

    public function handle(MantenimientoService $servicio): int
    {
        $maquinariaIdOpt = $this->option('maquinaria');

        $this->info('Iniciando verificacion automatica de umbrales de mantenimiento...');

        $maquinarias = $servicio->obtenerMaquinariasElegibles(
            $maquinariaIdOpt ? (int) $maquinariaIdOpt : null
        );

        if ($maquinarias->isEmpty()) {
            $this->warn('No hay maquinarias elegibles para verificar.');

            return self::SUCCESS;
        }

        $tipoPreventivo = $servicio->obtenerTipoPreventivo();
        if (! $tipoPreventivo) {
            $this->error('No existe tipo de mantenimiento preventivo configurado.');

            return self::SUCCESS;
        }

        $ordenesGeneradas = 0;
        $advertenciasStock = [];

        foreach ($maquinarias as $maquinaria) {
            try {
                $resultado = $servicio->procesarUmbralMaquinaria($maquinaria, $tipoPreventivo);
            } catch (Throwable $e) {
                $this->error("Error creando orden para maquinaria {$maquinaria->id_maquinaria}: {$e->getMessage()}");
                Log::error('Error en mantenimiento:check-umbrales', [
                    'maquinaria_id' => $maquinaria->id_maquinaria,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (! $resultado['generada']) {
                if ($resultado['motivo'] === 'orden_abierta') {
                    $this->warn("Maquinaria {$maquinaria->id_maquinaria}: ya existe orden abierta.");
                }

                continue;
            }

            $ordenesGeneradas++;
            $this->reportarOrdenGenerada($maquinaria, $resultado);

            if ($resultado['falta_stock']) {
                $advertenciasStock[] = [
                    'maquinaria' => $maquinaria->id_maquinaria,
                    'orden' => $resultado['mantenimiento']->id_mantenimiento,
                    'insumos' => $resultado['insumos'],
                ];
            }
        }

        $this->mostrarResumen($ordenesGeneradas, $advertenciasStock);

        return self::SUCCESS;
    }

    /**
     * @param  array{generada: bool, mantenimiento: \App\Models\Mantenimiento, programacion: array, falta_stock: bool, insumos: array}  $resultado
     */
    private function reportarOrdenGenerada($maquinaria, array $resultado): void
    {
        $mantenimiento = $resultado['mantenimiento'];
        $programacion = $resultado['programacion'];

        $this->info(
            "Orden #{$mantenimiento->id_mantenimiento} creada para maquinaria {$maquinaria->id_maquinaria}. ".
            "Fecha: {$programacion['fecha_programada']->toDateString()} ({$programacion['fuente']})."
        );

        if ($resultado['falta_stock']) {
            $this->warn("Orden #{$mantenimiento->id_mantenimiento}: faltan insumos para kit preventivo.");
        }
    }

    private function mostrarResumen(int $ordenesGeneradas, array $advertenciasStock): void
    {
        $this->info('');
        $this->info('=== RESUMEN ===');
        $this->info("Ordenes generadas: {$ordenesGeneradas}");

        if (! empty($advertenciasStock)) {
            $this->warn('Advertencias de stock:');
            foreach ($advertenciasStock as $adv) {
                $this->warn("Orden #{$adv['orden']} - Maquinaria {$adv['maquinaria']}");
                foreach ($adv['insumos'] as $ins) {
                    $this->warn("  - {$ins['insumo']}: faltan {$ins['faltante']} unidades");
                }
            }
            Log::warning('Ordenes creadas con faltantes de stock', ['advertencias' => $advertenciasStock]);
        }

        $this->info('Verificacion completada.');
    }
}
