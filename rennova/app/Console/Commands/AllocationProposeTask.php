<?php

namespace App\Console\Commands;

use App\Models\LoteTarea;
use App\Services\AutomaticAllocationService;
use Illuminate\Console\Command;

class AllocationProposeTask extends Command
{
    protected $signature = 'allocation:propose-task {loteTareaId} {--months=24} {--min=5} {--gap=7}';

    protected $description = 'Genera una PropuestaAsignacion para una tarea de lote (lote_tareas) usando historial real.';

    public function handle(AutomaticAllocationService $service): int
    {
        $loteTareaId = (int) $this->argument('loteTareaId');

        $tarea = LoteTarea::find($loteTareaId);
        if (! $tarea) {
            $this->error('No se encontró la tarea: '.$loteTareaId);

            return self::FAILURE;
        }

        $months = (int) $this->option('months');
        $min = (int) $this->option('min');
        $gap = (int) $this->option('gap');

        $proposal = $service->proposeForLoteTarea(
            tarea: $tarea,
            since: now()->subMonths($months),
            minSamples: $min,
            gapDaysForRunSplit: $gap
        );

        $this->info('Proposal creada: #'.$proposal->id_allocation_proposal);
        $this->line('Lote: '.$proposal->id_lote.' | Tarea: '.($proposal->id_lote_tarea ?? '-').' | Tipo: '.$proposal->tipo_tarea);
        $this->line('Superficie usada (ha): '.($proposal->superficie_ha ?? 'N/A'));
        $this->line('Estimación total:');
        $this->line('  - Persona-día: '.($proposal->estimated_person_days ?? 'N/A'));
        $this->line('  - Máquina-día: '.($proposal->estimated_machine_days ?? 'N/A'));
        $this->line('  - Duración (días): '.($proposal->estimated_duration_days ?? 'N/A'));

        return self::SUCCESS;
    }
}
