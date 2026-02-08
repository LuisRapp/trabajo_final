<?php

namespace App\Console\Commands;

use App\Enums\TaskType;
use App\Models\Lote;
use App\Services\AutomaticAllocationService;
use Illuminate\Console\Command;

class AllocationPropose extends Command
{
    protected $signature = 'allocation:propose {loteId} {taskType} {--months=24} {--min=5} {--gap=7}';

    protected $description = 'Genera una AllocationProposal para un lote y tipo de tarea usando historial real (persona-día/máquina-día).';

    public function handle(AutomaticAllocationService $service): int
    {
        $loteId = (int) $this->argument('loteId');
        $taskTypeInput = (string) $this->argument('taskType');

        $taskType = TaskType::tryFrom($taskTypeInput);
        if (!$taskType) {
            $this->error('taskType inválido. Valores permitidos: ' . implode(', ', array_map(fn($c) => $c->value, TaskType::cases())));
            return self::INVALID;
        }

        $lote = Lote::find($loteId);
        if (!$lote) {
            $this->error('No se encontró el lote: ' . $loteId);
            return self::FAILURE;
        }

        $months = (int) $this->option('months');
        $min = (int) $this->option('min');
        $gap = (int) $this->option('gap');

        $proposal = $service->proposeForLotAndTask(
            lote: $lote,
            taskType: $taskType,
            since: now()->subMonths($months),
            minSamples: $min,
            gapDaysForRunSplit: $gap
        );

        $this->info('Proposal creada: #' . $proposal->id_allocation_proposal);
        $this->line('Lote: ' . $proposal->id_lote . ' | Especie: ' . ($proposal->especie ?? '-') . ' | Superficie: ' . ($proposal->superficie_ha ?? '-') . ' ha');
        $this->line('Tarea: ' . $proposal->tipo_tarea);
        $this->line('Estimación total:');
        $this->line('  - Persona-día: ' . ($proposal->estimated_person_days ?? 'N/A'));
        $this->line('  - Máquina-día: ' . ($proposal->estimated_machine_days ?? 'N/A'));
        $this->line('  - Duración (días): ' . ($proposal->estimated_duration_days ?? 'N/A'));
        $this->line('Sugerencias:');
        $this->line('  - Tamaño equipo: ' . ($proposal->suggested_team_size ?? 'N/A'));
        $this->line('  - Maquinarias: ' . ($proposal->suggested_machinery_count ?? 'N/A'));

        return self::SUCCESS;
    }
}
