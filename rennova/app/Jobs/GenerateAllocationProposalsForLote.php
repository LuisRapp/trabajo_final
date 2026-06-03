<?php

namespace App\Jobs;

use App\Enums\TaskType;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\PropuestaAsignacion;
use App\Services\AutomaticAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAllocationProposalsForLote implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $loteId,
        public readonly int $months = 24,
        public readonly int $minSamples = 5,
        public readonly int $gapDaysForRunSplit = 7,
        public readonly bool $skipIfAlreadyGeneratedToday = true,
    ) {}

    public function uniqueId(): string
    {
        return 'allocation-proposals:lote:'.$this->loteId;
    }

    public function handle(AutomaticAllocationService $service): void
    {
        $lote = Lote::find($this->loteId);
        if (! $lote) {
            return;
        }

        // No generar si el lote está inactivo
        if ($lote->estado === 'inactivo') {
            return;
        }

        $tareasActivas = LoteTarea::query()
            ->where('id_lote', $lote->id_lote)
            ->whereIn('estado', ['planificada', 'en_ejecucion'])
            ->orderByDesc('id_lote_tarea')
            ->get();

        if ($tareasActivas->isNotEmpty()) {
            foreach ($tareasActivas as $tarea) {
                if ($this->skipIfAlreadyGeneratedToday) {
                    $exists = PropuestaAsignacion::where('id_lote_tarea', $tarea->id_lote_tarea)
                        ->where('created_at', '>=', now()->startOfDay())
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'closed');
                        })
                        ->exists();

                    if ($exists) {
                        continue;
                    }
                }

                $service->proposeForLoteTarea(
                    tarea: $tarea,
                    since: now()->subMonths($this->months),
                    minSamples: $this->minSamples,
                    gapDaysForRunSplit: $this->gapDaysForRunSplit,
                );
            }

            return;
        }

        // Fallback: si el usuario todavía no definió tareas, generamos propuestas solo para la tarea principal del lote.
        $mainTaskType = $lote->main_task_type ?? null;
        $taskTypes = $mainTaskType ? [TaskType::from($mainTaskType)] : TaskType::cases();

        foreach ($taskTypes as $taskType) {
            if ($this->skipIfAlreadyGeneratedToday) {
                $exists = PropuestaAsignacion::where('id_lote', $lote->id_lote)
                    ->whereNull('id_lote_tarea')
                    ->where('tipo_tarea', $taskType->value)
                    ->where('created_at', '>=', now()->startOfDay())
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'closed');
                    })
                    ->exists();

                if ($exists) {
                    continue;
                }
            }

            $service->proposeForLotAndTask(
                lote: $lote,
                taskType: $taskType,
                since: now()->subMonths($this->months),
                minSamples: $this->minSamples,
                gapDaysForRunSplit: $this->gapDaysForRunSplit,
            );
        }
    }
}
