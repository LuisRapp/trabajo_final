<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\PropuestaAsignacion;
use Carbon\Carbon;

/**
 * Servicio de Asignación Automática (Orquestador)
 * 
 * Coordina los servicios especializados para generar propuestas de asignación
 * de recursos (empleados, maquinarias, insumos) a lotes y tareas.
 */
class AutomaticAllocationService
{
    public function __construct(
        private readonly HistoricalTaskPerformanceRepository $repo,
        private readonly AsignacionMetricasService $metricasService,
        private readonly AsignacionCandidatosService $candidatosService,
        private readonly AsignacionInsumosService $insumosService
    ) {}

    /**
     * Genera propuesta de asignación para un lote y tipo de tarea
     */
    public function proposeForLotAndTask(
        Lote $lote,
        TaskType $taskType,
        ?Carbon $since = null,
        int $minSamples = 5,
        int $gapDaysForRunSplit = 7
    ): PropuestaAsignacion {
        $since ??= Carbon::today()->subMonths(24);

        $fallbacks = [
            ['task' => $taskType->value, 'species' => $lote->especie, 'label' => 'task+species'],
            ['task' => $taskType->value, 'species' => null, 'label' => 'task'],
            ['task' => null, 'species' => $lote->especie, 'label' => 'species'],
            ['task' => null, 'species' => null, 'label' => 'global'],
        ];

        [$selected, $runs] = $this->findBestFallback($fallbacks, $since, $minSamples, $gapDaysForRunSplit);

        $metrics = $this->metricasService->computeMetrics($runs);

        $superficie = (float) ($lote->superficie ?? 0);
        [$estimatedPersonDays, $estimatedMachineDays, $estimatedDurationDays, $useDefault] =
            $this->calculateEstimates($superficie, $metrics);

        $proposal = PropuestaAsignacion::create([
            'id_lote' => $lote->id_lote,
            'id_lote_tarea' => null,
            'tipo_tarea' => $taskType->value,
            'especie' => $lote->especie,
            'superficie_ha' => $lote->superficie,
            'estimated_person_days' => $estimatedPersonDays,
            'estimated_machine_days' => $estimatedMachineDays,
            'estimated_duration_days' => $estimatedDurationDays,
            'suggested_team_size' => $metrics['suggested_team_size'],
            'suggested_machinery_count' => $metrics['suggested_machinery_count'],
            'meta' => $this->buildMeta($since, $minSamples, $gapDaysForRunSplit, $selected, $runs, $superficie, $metrics, $useDefault),
        ]);

        $this->candidatosService->populateProposalCandidates(
            proposal: $proposal,
            taskType: $taskType,
            species: $lote->especie,
            since: $since
        );

        return $proposal;
    }

    /**
     * Genera propuesta de asignación para una LoteTarea específica
     */
    public function proposeForLoteTarea(
        LoteTarea $tarea,
        ?Carbon $since = null,
        int $minSamples = 5,
        int $gapDaysForRunSplit = 7
    ): PropuestaAsignacion {
        $since ??= Carbon::today()->subMonths(24);

        $lote = $tarea->lote()->first();
        if (! $lote) {
            throw new \InvalidArgumentException('La tarea no tiene lote asociado.');
        }

        $taskType = TaskType::tryFrom((string) $tarea->tipo_tarea);
        if (! $taskType) {
            throw new \InvalidArgumentException('tipo_tarea inválido en lote_tareas: '.(string) $tarea->tipo_tarea);
        }

        $fallbacks = [
            ['task' => $taskType->value, 'species' => $lote->especie, 'label' => 'task+species'],
            ['task' => $taskType->value, 'species' => null, 'label' => 'task'],
            ['task' => null, 'species' => $lote->especie, 'label' => 'species'],
            ['task' => null, 'species' => null, 'label' => 'global'],
        ];

        [$selected, $runs] = $this->findBestFallback($fallbacks, $since, $minSamples, $gapDaysForRunSplit);

        $metrics = $this->metricasService->computeMetrics($runs);

        $superficie = (float) ($tarea->superficie_afectada_ha ?? $lote->superficie ?? 0);
        [$estimatedPersonDays, $estimatedMachineDays, $estimatedDurationDays, $useDefault] =
            $this->calculateEstimates($superficie, $metrics);

        $proposal = PropuestaAsignacion::create([
            'id_lote' => $lote->id_lote,
            'id_lote_tarea' => $tarea->id_lote_tarea,
            'tipo_tarea' => $taskType->value,
            'especie' => $lote->especie,
            'superficie_ha' => $superficie,
            'estimated_person_days' => $estimatedPersonDays,
            'estimated_machine_days' => $estimatedMachineDays,
            'estimated_duration_days' => $estimatedDurationDays,
            'suggested_team_size' => $metrics['suggested_team_size'],
            'suggested_machinery_count' => $metrics['suggested_machinery_count'],
            'meta' => $this->buildMeta($since, $minSamples, $gapDaysForRunSplit, $selected, $runs, $superficie, $metrics, $useDefault),
        ]);

        $this->candidatosService->populateProposalCandidates(
            proposal: $proposal,
            taskType: $taskType,
            species: $lote->especie,
            since: $since
        );

        return $proposal;
    }

    /**
     * Delega al servicio de insumos para asegurar estimaciones de semana 1
     */
    public function ensureWeek1SupplyEstimates(PropuestaAsignacion $proposal, ?Carbon $since = null): void
    {
        $this->insumosService->ensureWeek1SupplyEstimates($proposal, $since);
    }

    /**
     * Busca el mejor fallback con datos suficientes
     */
    private function findBestFallback(array $fallbacks, Carbon $since, int $minSamples, int $gapDaysForRunSplit): array
    {
        $selected = null;
        $runs = collect();

        foreach ($fallbacks as $fb) {
            $records = $this->repo->fetchDailyProductionRecords(
                taskType: $fb['task'],
                species: $fb['species'],
                since: $since
            );

            $runs = $this->metricasService->buildRuns($records, $gapDaysForRunSplit);
            $runs = $runs->filter(fn ($r) => ($r['superficie'] ?? 0) > 0);

            if ($runs->count() >= $minSamples) {
                $selected = $fb;
                break;
            }
        }

        if ($selected === null) {
            $selected = end($fallbacks);
        }

        return [$selected, $runs];
    }

    /**
     * Calcula estimaciones de días y duración
     */
    private function calculateEstimates(float $superficie, array $metrics): array
    {
        $pdphMedian = $metrics['kpi']['person_days_per_ha']['median'] ?? null;
        $mdphMedian = $metrics['kpi']['machine_days_per_ha']['median'] ?? null;
        $dphMedian = $metrics['kpi']['days_per_ha']['median'] ?? null;

        $useDefault = ($metrics['kpi']['person_days_per_ha']['n'] ?? 0) === 0
            || ($metrics['kpi']['machine_days_per_ha']['n'] ?? 0) === 0
            || ($metrics['kpi']['days_per_ha']['n'] ?? 0) === 0;

        if ($useDefault) {
            $pdphMedian = 5;
            $mdphMedian = 5;
            $dphMedian = 5;
        }

        $estimatedPersonDays = ($superficie > 0 && is_numeric($pdphMedian)) ? round(((float) $pdphMedian) * $superficie, 2) : null;
        $estimatedMachineDays = ($superficie > 0 && is_numeric($mdphMedian)) ? round(((float) $mdphMedian) * $superficie, 2) : null;
        $estimatedDurationDays = ($superficie > 0 && is_numeric($dphMedian)) ? round(((float) $dphMedian) * $superficie, 2) : null;

        return [$estimatedPersonDays, $estimatedMachineDays, $estimatedDurationDays, $useDefault];
    }

    /**
     * Construye el array de metadatos para la propuesta
     */
    private function buildMeta(Carbon $since, int $minSamples, int $gapDaysForRunSplit, array $selected, $runs, float $superficie, array $metrics, bool $useDefault): array
    {
        return [
            'since' => $since->toDateString(),
            'min_samples' => $minSamples,
            'gap_days_for_run_split' => $gapDaysForRunSplit,
            'fallback_used' => $selected['label'],
            'samples' => [
                'runs' => $runs->count(),
            ],
            'scaled_by_superficie_ha' => $superficie,
            'kpi' => $metrics['kpi'],
            'confidence' => $useDefault ? 'low' : 'normal',
            'review_required' => $useDefault,
            'default_rates' => $useDefault ? [
                'person_days_per_ha' => 5,
                'machine_days_per_ha' => 5,
                'days_per_ha' => 5,
                'reason' => 'sin_historico',
            ] : null,
        ];
    }
}
