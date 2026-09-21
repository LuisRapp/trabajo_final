<?php

namespace App\Console\Commands;

use App\Services\MantenimientoService;
use Illuminate\Console\Command;

class CheckMantenimientosProgramados extends Command
{
    protected $signature = 'mantenimiento:check-programados';

    protected $description = 'Verifica mantenimientos programados para hoy y marca como vencidos los no confirmados';

    public function handle(MantenimientoService $servicio): int
    {
        $this->info('Verificando mantenimientos programados...');

        $hoy = now()->toDateString();
        $limiteAviso = now()->addDays(2)->toDateString();

        $mantenimientosHoy = $servicio->obtenerProgramadosDeHoy();

        if ($mantenimientosHoy->count() > 0) {
            $this->info("📅 {$mantenimientosHoy->count()} mantenimiento(s) programado(s) para hoy");

            foreach ($mantenimientosHoy as $mantenimiento) {
                $this->info("  - Orden #{$mantenimiento->id_mantenimiento}: {$mantenimiento->maquinaria->modelo}");
            }
        } else {
            $this->info('No hay mantenimientos programados para hoy');
        }

        $pendientesProgramar = $servicio->obtenerPendientesDeProgramar($limiteAviso);

        if ($pendientesProgramar->count() > 0) {
            $this->info("{$pendientesProgramar->count()} mantenimiento(s) pendientes de programar (limite <= {$limiteAviso})");
        }

        if ($mantenimientosHoy->count() > 0 || $pendientesProgramar->count() > 0) {
            $servicio->enviarRecordatorioProgramados($mantenimientosHoy, $pendientesProgramar);
        }

        $vencidos = $servicio->marcarMantenimientosVencidos($hoy);

        if ($vencidos->count() > 0) {
            $this->warn("⚠️  {$vencidos->count()} mantenimiento(s) vencido(s)");

            foreach ($vencidos as $mantenimiento) {
                $this->warn("  - Orden #{$mantenimiento->id_mantenimiento} marcada como vencida");
            }
        }

        $this->info("\nVerificación completada.");

        return 0;
    }
}
