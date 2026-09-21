<?php

namespace App\Console\Commands;

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SincronizarClimaReal extends Command
{
    /**
     * Nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'clima:real {--fecha= : Fecha YYYY-MM-DD (opcional, por defecto ayer)} {--lote= : ID del lote a analizar (opcional)}';

    /**
     * Descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Sincroniza clima real (historico) para el dia objetivo';

    protected ClimaDecisionService $climaService;

    public function __construct(ClimaDecisionService $climaService)
    {
        parent::__construct();
        $this->climaService = $climaService;
    }

    /**
     * Ejecutar el comando de consola.
     */
    public function handle(): int
    {

        $fecha = $this->option('fecha');
        $fechaObjetivo = $fecha
            ? Carbon::parse($fecha)
            : Carbon::now(ClimaDecisionService::TIMEZONE)->subDay();

        $loteId = $this->option('lote');

        $lotes = $loteId
            ? Lote::where('id_lote', $loteId)->get()
            : Lote::whereIn('estado', ['activo', 'en_proceso'])
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get();

        if ($lotes->isEmpty()) {
            $this->warn('No hay lotes disponibles para sincronizar clima real.');

            return Command::SUCCESS;
        }

        $this->line("Sincronizando clima real para {$fechaObjetivo->toDateString()}...");

        $errores = 0;

        foreach ($lotes as $lote) {
            $resultado = $this->climaService->sincronizarReal($lote, $fechaObjetivo);
            if (! $resultado['success']) {
                $errores++;
                $this->error("Lote {$lote->id_lote}: {$resultado['error']}");
            }
        }

        if ($errores > 0) {
            $this->warn("Sincronizacion completada con {$errores} error(es).");
        } else {
            $this->info('Sincronizacion completada correctamente.');
        }

        return Command::SUCCESS;
    }
}
