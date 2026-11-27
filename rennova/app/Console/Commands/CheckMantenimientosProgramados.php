<?php

namespace App\Console\Commands;

use App\Services\ConfiguracionService;
use App\Services\ProcesoMantenimientoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckMantenimientosProgramados extends Command
{
    protected $signature = 'mantenimiento:check-programados
                            {--forzar : Envia el recordatorio sin respetar la hora configurada}';

    protected $description = 'Verifica mantenimientos programados para hoy y marca como vencidos los no confirmados';

    public function handle(ProcesoMantenimientoService $servicio): int
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

        // El recordatorio es diario: se envia una sola vez, a partir de la
        // hora configurada. La revision de vencidos corre siempre.
        $horaRecordatorio = (string) ConfiguracionService::obtener('mantenimiento_hora_recordatorio', '08:00');
        $claveRecordatorio = 'mantenimiento:recordatorio:'.now()->toDateString();
        $correspondeRecordatorio = now()->format('H:i') >= $horaRecordatorio
            && ! Cache::has($claveRecordatorio);

        if (($mantenimientosHoy->count() > 0 || $pendientesProgramar->count() > 0)
            && ($this->option('forzar') || $correspondeRecordatorio)) {
            $servicio->enviarRecordatorioProgramados($mantenimientosHoy, $pendientesProgramar);
            Cache::put($claveRecordatorio, true, now()->endOfDay());
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
