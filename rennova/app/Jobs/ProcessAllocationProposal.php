<?php

namespace App\Jobs;

use App\Enums\TaskType;
use App\Models\AllocationProposal;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Services\AutomaticAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAllocationProposal implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 900;

    public function __construct(
        public readonly int $loteId,
        public readonly int $months = 24,
        public readonly int $minSamples = 5,
        public readonly int $gapDaysForRunSplit = 7,
        public readonly bool $skipIfAlreadyGeneratedToday = true,
    ) {
    }

    public function uniqueId(): string
    {
        return 'allocation-proposals:process:lote:' . $this->loteId;
    }

    public function handle(AutomaticAllocationService $service): void
    {
        $lote = Lote::find($this->loteId);
        if (!$lote) {
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
                    $exists = AllocationProposal::where('id_lote_tarea', $tarea->id_lote_tarea)
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

        $taskType = TaskType::tryFrom((string) $lote->main_task_type);
        if (!$taskType) {
            return;
        }

        if ($this->skipIfAlreadyGeneratedToday) {
            $exists = AllocationProposal::where('id_lote', $lote->id_lote)
                ->whereNull('id_lote_tarea')
                ->where('tipo_tarea', $taskType->value)
                ->where('created_at', '>=', now()->startOfDay())
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'closed');
                })
                ->exists();

            if ($exists) {
                return;
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
