<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionInsumo;
use App\Models\PropuestaAsignacionMaquinaria;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AutomaticAllocationService
{
    public function __construct(
        private readonly HistoricalTaskPerformanceRepository $repo
    ) {}

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

        $selected = null;
        $runs = collect();

        foreach ($fallbacks as $fb) {
            $records = $this->repo->fetchDailyProductionRecords(
                taskType: $fb['task'],
                species: $fb['species'],
                since: $since
            );

            $runs = $this->buildRuns($records, $gapDaysForRunSplit);
            // Filtrar runs con superficie válida
            $runs = $runs->filter(fn ($r) => ($r['superficie'] ?? 0) > 0);

            if ($runs->count() >= $minSamples) {
                $selected = $fb;
                break;
            }
        }

        if ($selected === null) {
            // Aun si no llega al mínimo, usamos el último fallback (global)
            $selected = end($fallbacks);
        }

        $metrics = $this->computeMetrics($runs);

        $superficie = (float) ($lote->superficie ?? 0);
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
            'meta' => [
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
            ],
        ]);

        $this->populateProposalCandidates(
            proposal: $proposal,
            taskType: $taskType,
            species: $lote->especie,
            since: $since
        );

        return $proposal;
    }

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

        $selected = null;
        $runs = collect();

        foreach ($fallbacks as $fb) {
            $records = $this->repo->fetchDailyProductionRecords(
                taskType: $fb['task'],
                species: $fb['species'],
                since: $since
            );

            $runs = $this->buildRuns($records, $gapDaysForRunSplit);
            $runs = $runs->filter(fn ($r) => ($r['superficie'] ?? 0) > 0);

            if ($runs->count() >= $minSamples) {
                $selected = $fb;
                break;
            }
        }

        if ($selected === null) {
            $selected = end($fallbacks);
        }

        $metrics = $this->computeMetrics($runs);

        $superficie = (float) ($tarea->superficie_afectada_ha ?? $lote->superficie ?? 0);
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
            'meta' => [
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
            ],
        ]);

        $this->populateProposalCandidates(
            proposal: $proposal,
            taskType: $taskType,
            species: $lote->especie,
            since: $since
        );

        return $proposal;
    }

    private function populateProposalCandidates(
        PropuestaAsignacion $proposal,
        TaskType $taskType,
        ?string $species,
        Carbon $since
    ): void {
        // Ranking simple: frecuencia histórica de participación en cargas.
        $employees = collect($this->repo->topEmployeesForTaskAndSpecies(
            taskType: $taskType->value,
            species: $species,
            since: $since,
            limit: 10
        ));

        $busyEmployees = $this->busyEmployeeIds();

        $i = 0;
        $employees = $employees
            ->filter(fn ($row) => ! in_array((int) $row->id_empleado, $busyEmployees, true))
            ->values();

        $teamSize = $this->resolveTeamSize($proposal);
        $needsCorteWorkers = $this->taskNeedsCorte($taskType);
        $chainsawCount = $needsCorteWorkers ? max(1, (int) ceil($teamSize * 0.4)) : 0;
        // Asegurar al menos 1 operador si hay maquinarias sugeridas (gruista para grúas, etc)
        $machineryCount = (int) ($proposal->suggested_machinery_count ?? 0);
        $operatorCount = $machineryCount > 0 ? max(1, $machineryCount) : 0;

        $chainsawKeywords = ['motosierra', 'motosierr', 'moto sierra', 'motosierrista'];
        $operatorKeywords = ['operario', 'maquinista', 'tractorista', 'operador', 'gruista'];

        $selectedEmployeeIds = [];

        $chainsawCandidates = $employees->filter(fn ($row) => $this->roleMatches($row->rol_nombre ?? null, $chainsawKeywords))->values();
        $operatorCandidates = $employees->filter(fn ($row) => $this->roleMatches($row->rol_nombre ?? null, $operatorKeywords))->values();
        $generalCandidates = $employees->filter(fn ($row) => ! $this->roleMatches($row->rol_nombre ?? null, $chainsawKeywords)
            && ! $this->roleMatches($row->rol_nombre ?? null, $operatorKeywords)
        )->values();

        foreach ($chainsawCandidates->take($chainsawCount) as $row) {
            $selectedEmployeeIds[] = (int) $row->id_empleado;
        }

        foreach ($operatorCandidates as $row) {
            if (count($selectedEmployeeIds) >= ($chainsawCount + $operatorCount)) {
                break;
            }
            $selectedEmployeeIds[] = (int) $row->id_empleado;
        }

        foreach ($generalCandidates as $row) {
            if (count($selectedEmployeeIds) >= max(1, $teamSize)) {
                break;
            }
            $selectedEmployeeIds[] = (int) $row->id_empleado;
        }

        // Fallback: si no hay candidatos en histórico, buscar empleados por rol
        if ($employees->isEmpty()) {
            $fallback = collect();
            if ($chainsawCount > 0) {
                $fallback = $fallback->merge($this->fallbackEmployeesByRole($chainsawKeywords, $busyEmployees, $chainsawCount));
            }
            if ($operatorCount > 0) {
                $fallback = $fallback->merge($this->fallbackEmployeesByRole($operatorKeywords, $busyEmployees, $operatorCount));
            }
            $fallback = $fallback->merge($this->fallbackEmployeesByRole([], $busyEmployees, max(1, $teamSize)));
            $employees = $fallback->unique('id_empleado')->values();
            $selectedEmployeeIds = $employees->take(max(1, $teamSize))->pluck('id_empleado')->map(fn ($v) => (int) $v)->all();
        }

        // Fallback adicional: si faltan operadores y hay maquinarias, buscar operadores específicamente
        if ($operatorCount > 0 && $operatorCandidates->count() < $operatorCount) {
            $missingOperators = $operatorCount - $operatorCandidates->count();
            $additionalOperators = $this->fallbackEmployeesByRole($operatorKeywords, $busyEmployees, $missingOperators);
            foreach ($additionalOperators as $row) {
                if (! $employees->contains('id_empleado', $row->id_empleado)) {
                    $employees->push($row);
                    if (count($selectedEmployeeIds) < ($chainsawCount + $operatorCount)) {
                        $selectedEmployeeIds[] = (int) $row->id_empleado;
                    }
                }
            }
        }

        foreach ($employees as $row) {
            PropuestaAsignacionEmpleado::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_empleado' => (int) $row->id_empleado,
                'rol_sugerido' => $row->rol_nombre ? (string) $row->rol_nombre : null,
                'score' => isset($row->days_count) ? (float) $row->days_count : null,
                'selected' => in_array((int) $row->id_empleado, $selectedEmployeeIds, true),
            ]);
        }

        $maquinarias = collect($this->repo->topMaquinariasForTaskAndSpecies(
            taskType: $taskType->value,
            species: $species,
            since: $since,
            limit: 10
        ));

        $busyMaquinarias = $this->busyMaquinariaIds();

        // Fallback: si no hay histórico, buscar maquinarias disponibles
        if ($maquinarias->isEmpty()) {
            $maquinarias = collect(DB::table('maquinarias as m')
                ->leftJoin('tipo_maquinarias as tm', 'tm.id_tipo_maquinaria', '=', 'm.id_tipo_maquinaria')
                ->whereNotIn('m.id_maquinaria', $busyMaquinarias)
                ->select([
                    'm.id_maquinaria',
                    'm.modelo',
                    DB::raw('tm.nombre as tipo_nombre'),
                    DB::raw('0 as days_count'),
                ])
                ->get());
        }

        // Filtrar maquinarias ocupadas
        $maquinarias = $maquinarias->filter(fn ($row) => ! in_array((int) $row->id_maquinaria, $busyMaquinarias, true));

        // Priorización blanda por tipo (para acercarnos a grúa/skidder sin hardcode rígido)
        $sortedMaquinarias = $maquinarias->sortByDesc(function ($row) {
            $tipo = mb_strtolower((string) ($row->tipo_nombre ?? ''));
            $modelo = mb_strtolower((string) ($row->modelo ?? ''));
            $bonus = 0;
            // Detectar grúa tanto en tipo como en modelo
            if (str_contains($tipo, 'grua') || str_contains($tipo, 'grúa') ||
                str_contains($modelo, 'grua') || str_contains($modelo, 'grúa') ||
                str_contains($tipo, 'carga')) {
                $bonus += 1000;
            }
            if (str_contains($tipo, 'skid') || str_contains($modelo, 'skid')) {
                $bonus += 900;
            }

            $base = isset($row->days_count) ? (float) $row->days_count : 0.0;

            return $bonus + $base;
        })->values();

        $sortedMaquinarias = $sortedMaquinarias
            ->filter(fn ($row) => ! in_array((int) $row->id_maquinaria, $busyMaquinarias, true))
            ->values();

        $needsCorte = $this->taskNeedsCorte($taskType);
        $selectedMachineryIds = [];

        // Categorizar todas las maquinarias disponibles
        $categories = $sortedMaquinarias->mapWithKeys(function ($row) {
            $label = $row->tipo_nombre ?? $row->modelo ?? '';
            $cat = $this->detectMachineCategory($label);

            return [(int) $row->id_maquinaria => $cat];
        });

        // Separar por categorías para armar parejas/equipos lógicos
        $cargaPool = $sortedMaquinarias->filter(fn ($r) => ($categories[(int) $r->id_maquinaria] ?? null) === 'carga');
        $arrastrePool = $sortedMaquinarias->filter(fn ($r) => ($categories[(int) $r->id_maquinaria] ?? null) === 'arrastre');
        $cortePool = $sortedMaquinarias->filter(fn ($r) => ($categories[(int) $r->id_maquinaria] ?? null) === 'corte');
        $otrosPool = $sortedMaquinarias->filter(fn ($r) => ($categories[(int) $r->id_maquinaria] ?? null) === null);

        // Estrategia: Armar parejas del mundo real
        // 1. Trío completo: arrastre + corte + carga (ideal)
        // 2. Pareja arrastre + carga
        // 3. Pareja corte + carga
        // 4. Si no hay suficientes, usar lo que haya disponible

        $targetMachines = max(1, (int) ($proposal->suggested_machinery_count ?? 1));

        // Intentar formar trío completo si hay disponibilidad y la tarea lo amerita
        if ($needsCorte && $targetMachines >= 3 &&
            $arrastrePool->isNotEmpty() && $cortePool->isNotEmpty() && $cargaPool->isNotEmpty()) {
            $selectedMachineryIds[] = (int) $arrastrePool->first()->id_maquinaria;
            $selectedMachineryIds[] = (int) $cortePool->first()->id_maquinaria;
            $selectedMachineryIds[] = (int) $cargaPool->first()->id_maquinaria;
        }
        // Intentar pareja arrastre + carga
        elseif ($arrastrePool->isNotEmpty() && $cargaPool->isNotEmpty()) {
            $selectedMachineryIds[] = (int) $arrastrePool->first()->id_maquinaria;
            $selectedMachineryIds[] = (int) $cargaPool->first()->id_maquinaria;

            // Si necesita corte y hay disponible, agregar
            if ($needsCorte && $cortePool->isNotEmpty() && count($selectedMachineryIds) < $targetMachines) {
                $selectedMachineryIds[] = (int) $cortePool->first()->id_maquinaria;
            }
        }
        // Intentar pareja corte + carga
        elseif ($needsCorte && $cortePool->isNotEmpty() && $cargaPool->isNotEmpty()) {
            $selectedMachineryIds[] = (int) $cortePool->first()->id_maquinaria;
            $selectedMachineryIds[] = (int) $cargaPool->first()->id_maquinaria;
        }
        // Si solo hay carga disponible, usarla
        elseif ($cargaPool->isNotEmpty()) {
            $selectedMachineryIds[] = (int) $cargaPool->first()->id_maquinaria;
        }

        // Completar hasta el target con maquinarias priorizadas que falten
        foreach (['carga', 'arrastre', 'corte'] as $cat) {
            if (count($selectedMachineryIds) >= $targetMachines) {
                break;
            }
            foreach ($sortedMaquinarias as $row) {
                if (count($selectedMachineryIds) >= $targetMachines) {
                    break;
                }
                if (in_array((int) $row->id_maquinaria, $selectedMachineryIds, true)) {
                    continue;
                }
                if (($categories[(int) $row->id_maquinaria] ?? null) === $cat) {
                    $selectedMachineryIds[] = (int) $row->id_maquinaria;
                }
            }
        }

        // Fallback: completar con cualquier maquinaria disponible (sin categoría o de otros tipos)
        foreach ($sortedMaquinarias as $row) {
            if (count($selectedMachineryIds) >= $targetMachines) {
                break;
            }
            if (! in_array((int) $row->id_maquinaria, $selectedMachineryIds, true)) {
                $selectedMachineryIds[] = (int) $row->id_maquinaria;
            }
        }

        foreach ($sortedMaquinarias as $row) {
            PropuestaAsignacionMaquinaria::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_maquinaria' => (int) $row->id_maquinaria,
                'tipo_sugerido' => $row->tipo_nombre ? (string) $row->tipo_nombre : null,
                'score' => isset($row->days_count) ? (float) $row->days_count : null,
                'selected' => in_array((int) $row->id_maquinaria, $selectedMachineryIds, true),
            ]);
        }

        $insumos = $this->repo->topInsumosBySalidas(
            since: $since,
            limit: 10
        );

        foreach ($insumos as $row) {
            PropuestaAsignacionInsumo::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_insumo' => (int) $row->id_insumo,
                'cantidad_semana_1' => null,
                'costo_estimado_semana_1' => null,
                'selected' => true,
            ]);
        }

        // Completa cantidades/costos para semana 1 usando histórico y precios.
        $this->ensureWeek1SupplyEstimates($proposal, $since);
    }

    public function ensureWeek1SupplyEstimates(PropuestaAsignacion $proposal, ?Carbon $since = null): void
    {
        $since ??= Carbon::today()->subMonths(24);

        $proposal->loadMissing(['proposedInsumos', 'proposedMaquinarias']);

        $days = 7;
        if (is_numeric($proposal->estimated_duration_days)) {
            $duration = (float) $proposal->estimated_duration_days;
            $days = (int) max(1, min(7, (int) ceil($duration)));
        }

        DB::transaction(function () use ($proposal, $since, $days) {
            foreach ($proposal->proposedInsumos as $row) {
                // Si ya tiene cálculo, no pisamos.
                if ($row->cantidad_semana_1 !== null && $row->costo_estimado_semana_1 !== null) {
                    continue;
                }

                $medianDaily = null;
                if (! empty($proposal->tipo_tarea)) {
                    $medianDaily = $this->repo->medianDailySalidaQuantityForInsumoAndTask(
                        (int) $row->id_insumo,
                        (string) $proposal->tipo_tarea,
                        $since
                    );
                }

                if ($medianDaily === null) {
                    $medianDaily = $this->repo->medianDailySalidaQuantityForInsumo((int) $row->id_insumo, $since);
                }
                if ($medianDaily === null) {
                    continue;
                }

                $cantidad = round($medianDaily * $days, 2);
                $precioUnitario = $this->repo->unitPriceForInsumo((int) $row->id_insumo);
                $costo = $precioUnitario !== null ? round($cantidad * $precioUnitario, 2) : null;

                $row->cantidad_semana_1 = $cantidad;
                $row->costo_estimado_semana_1 = $costo;
                $row->save();
            }

            $meta = $proposal->meta ?? [];
            $meta['insumos_semana_1'] = array_merge($meta['insumos_semana_1'] ?? [], [
                'calculated_at' => now()->toISOString(),
                'days' => $days,
                'method' => 'median_daily_salidas_x_days (task-aware fallback)',
            ]);
            $proposal->meta = $meta;
            $proposal->save();
        });

        $useDefault = ! empty(($proposal->meta ?? [])['default_rates']['reason'] ?? null);
        $this->ensureWeek1FuelEstimate($proposal, $days, $useDefault);
        $this->ensureWeek1PersonFuelEstimate($proposal, $days, $useDefault);
        $this->ensureChainsawSupplies($proposal);
    }

    private function ensureWeek1FuelEstimate(PropuestaAsignacion $proposal, int $days, bool $useDefault): void
    {
        if (! $useDefault) {
            return;
        }
        $proposal->loadMissing(['proposedInsumos', 'proposedMaquinarias']);

        $machines = $proposal->suggested_machinery_count;
        if (! is_numeric($machines)) {
            $machines = $proposal->proposedMaquinarias
                ->where('selected', true)
                ->count();
        }
        $machines = (int) max(0, $machines);

        if ($machines <= 0) {
            return;
        }

        $fuel = Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%combustible%'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%diesel%'])
            ->first();

        if (! $fuel) {
            return;
        }

        $litros = round(50 * $machines * $days, 2);

        $row = PropuestaAsignacionInsumo::query()
            ->where('id_allocation_proposal', $proposal->id_allocation_proposal)
            ->where('id_insumo', $fuel->id_insumo)
            ->first();

        if ($row) {
            $row->cantidad_semana_1 = $litros;
            $row->save();
        } else {
            PropuestaAsignacionInsumo::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_insumo' => (int) $fuel->id_insumo,
                'cantidad_semana_1' => $litros,
                'costo_estimado_semana_1' => null,
                'selected' => true,
            ]);
        }
    }

    private function ensureWeek1PersonFuelEstimate(PropuestaAsignacion $proposal, int $days, bool $useDefault): void
    {
        if (! $useDefault) {
            return;
        }

        $teamSize = $this->resolveTeamSize($proposal);
        if ($teamSize <= 0) {
            return;
        }

        $nafta = Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%nafta%'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%gasolina%'])
            ->first();

        if (! $nafta) {
            return;
        }

        $litros = round(5 * $teamSize * $days, 2);

        $row = PropuestaAsignacionInsumo::query()
            ->where('id_allocation_proposal', $proposal->id_allocation_proposal)
            ->where('id_insumo', $nafta->id_insumo)
            ->first();

        if ($row) {
            $row->cantidad_semana_1 = $litros;
            $row->save();
        } else {
            PropuestaAsignacionInsumo::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_insumo' => (int) $nafta->id_insumo,
                'cantidad_semana_1' => $litros,
                'costo_estimado_semana_1' => null,
                'selected' => true,
            ]);
        }
    }

    private function ensureChainsawSupplies(PropuestaAsignacion $proposal): void
    {
        if (! $this->taskNeedsCorte(TaskType::tryFrom((string) $proposal->tipo_tarea) ?? TaskType::TALA_RASA)) {
            return;
        }

        $keywords = [
            'aceite negro',
            'aceite de cadena',
            'aceite cadena',
            'lima',
        ];

        foreach ($keywords as $kw) {
            $insumo = Insumo::query()
                ->whereRaw('LOWER(nombre) LIKE ?', ['%'.$kw.'%'])
                ->first();

            if (! $insumo) {
                continue;
            }

            $exists = PropuestaAsignacionInsumo::query()
                ->where('id_allocation_proposal', $proposal->id_allocation_proposal)
                ->where('id_insumo', $insumo->id_insumo)
                ->exists();

            if ($exists) {
                continue;
            }

            PropuestaAsignacionInsumo::create([
                'id_allocation_proposal' => $proposal->id_allocation_proposal,
                'id_insumo' => (int) $insumo->id_insumo,
                'cantidad_semana_1' => null,
                'costo_estimado_semana_1' => null,
                'selected' => true,
            ]);
        }
    }

    private function resolveTeamSize(PropuestaAsignacion $proposal): int
    {
        if (is_numeric($proposal->suggested_team_size)) {
            return max(1, (int) round((float) $proposal->suggested_team_size));
        }

        if (is_numeric($proposal->estimated_person_days) && is_numeric($proposal->estimated_duration_days)) {
            $duration = (float) $proposal->estimated_duration_days;
            if ($duration > 0) {
                return max(1, (int) ceil((float) $proposal->estimated_person_days / $duration));
            }
        }

        return 3;
    }

    private function taskNeedsCorte(TaskType $taskType): bool
    {
        return in_array($taskType->value, ['tala_rasa', 'raleo', 'poda'], true);
    }

    private function roleMatches(?string $roleName, array $keywords): bool
    {
        if (empty($keywords)) {
            return true;
        }
        $role = mb_strtolower((string) $roleName);
        foreach ($keywords as $kw) {
            if ($kw !== '' && str_contains($role, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function detectMachineCategory(?string $name): ?string
    {
        $value = mb_strtolower((string) $name);
        if ($value === '') {
            return null;
        }

        // Priorizar detección de grúa primero (más específico)
        if (str_contains($value, 'grua') || str_contains($value, 'grúa') || str_contains($value, 'carga')) {
            return 'carga';
        }
        if (str_contains($value, 'corte') || str_contains($value, 'procesador')) {
            return 'corte';
        }
        if (str_contains($value, 'arrastre') || str_contains($value, 'skid') || str_contains($value, 'skidder')) {
            return 'arrastre';
        }

        return null;
    }

    private function fallbackEmployeesByRole(array $keywords, array $busyEmployees, int $limit): Collection
    {
        if ($limit <= 0) {
            return collect();
        }

        return collect(DB::table('empleados as e')
            ->leftJoin('roles_laborales as rl', 'rl.id_rol_laboral', '=', 'e.id_rol_laboral')
            ->when(! empty($keywords), function ($q) use ($keywords) {
                $q->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhereRaw('LOWER(rl.nombre) LIKE ?', ['%'.$kw.'%']);
                    }
                });
            })
            ->when(! empty($busyEmployees), fn ($q) => $q->whereNotIn('e.id_empleado', $busyEmployees))
            ->select([
                'e.id_empleado',
                'e.apellido',
                'e.nombre',
                DB::raw('rl.nombre as rol_nombre'),
            ])
            ->limit($limit)
            ->get());
    }

    private function busyEmployeeIds(): array
    {
        return DB::table('lote_empleado')
            ->join('lotes', 'lotes.id_lote', '=', 'lote_empleado.id_lote')
            ->whereIn('lotes.estado', ['en_proceso'])
            ->pluck('lote_empleado.id_empleado')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    private function busyMaquinariaIds(): array
    {
        return DB::table('lote_maquinaria')
            ->join('lotes', 'lotes.id_lote', '=', 'lote_maquinaria.id_lote')
            ->whereIn('lotes.estado', ['en_proceso'])
            ->pluck('lote_maquinaria.id_maquinaria')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Construye "runs" de tarea por lote, cortando cuando hay un gap grande de días.
     * Devuelve una colección de runs agregados.
     */
    private function buildRuns($records, int $gapDaysForRunSplit): Collection
    {
        $runs = collect();

        /** @var array<string, array> $current */
        $current = [];

        foreach ($records as $row) {
            $key = $row->id_lote_tarea
                ? ('tarea|'.$row->id_lote_tarea)
                : ($row->id_lote.'|'.$row->tipo_tarea);
            $date = Carbon::parse($row->fecha);

            if (! isset($current[$key])) {
                $current[$key] = [
                    'id_lote' => (int) $row->id_lote,
                    'id_lote_tarea' => $row->id_lote_tarea ? (int) $row->id_lote_tarea : null,
                    'tipo_tarea' => (string) $row->tipo_tarea,
                    'especie' => (string) $row->especie,
                    'superficie' => (float) $row->superficie,
                    'start' => $date,
                    'end' => $date,
                    'duration_days' => 0,
                    'person_days' => 0.0,
                    'machine_days' => 0.0,
                ];
            }

            $cur = &$current[$key];

            $gap = $cur['end']->diffInDays($date);
            if ($gap > $gapDaysForRunSplit) {
                $runs->push($this->finalizeRun($cur));
                $cur = [
                    'id_lote' => (int) $row->id_lote,
                    'id_lote_tarea' => $row->id_lote_tarea ? (int) $row->id_lote_tarea : null,
                    'tipo_tarea' => (string) $row->tipo_tarea,
                    'especie' => (string) $row->especie,
                    'superficie' => (float) $row->superficie,
                    'start' => $date,
                    'end' => $date,
                    'duration_days' => 0,
                    'person_days' => 0.0,
                    'machine_days' => 0.0,
                ];
            }

            $cur['end'] = $date;
            $cur['duration_days'] += 1;
            $cur['person_days'] += (float) ($row->empleados_count ?? 0);
            $cur['machine_days'] += (float) ($row->maquinarias_count ?? 0);
            unset($cur);
        }

        foreach ($current as $run) {
            $runs->push($this->finalizeRun($run));
        }

        return $runs;
    }

    private function finalizeRun(array $run): array
    {
        $superficie = (float) ($run['superficie'] ?? 0);
        $duration = max(1, (int) ($run['duration_days'] ?? 0));

        $run['persons_per_day'] = $duration > 0 ? ((float) $run['person_days'] / $duration) : null;
        $run['machines_per_day'] = $duration > 0 ? ((float) $run['machine_days'] / $duration) : null;

        if ($superficie > 0) {
            $run['person_days_per_ha'] = (float) $run['person_days'] / $superficie;
            $run['machine_days_per_ha'] = (float) $run['machine_days'] / $superficie;
            $run['days_per_ha'] = (float) $duration / $superficie;
        } else {
            $run['person_days_per_ha'] = null;
            $run['machine_days_per_ha'] = null;
            $run['days_per_ha'] = null;
        }

        // Compactar fechas para meta/debug
        $run['start'] = $run['start']->toDateString();
        $run['end'] = $run['end']->toDateString();

        return $run;
    }

    private function computeMetrics(Collection $runs): array
    {
        $pdph = $runs->pluck('person_days_per_ha')->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v)->values();
        $mdph = $runs->pluck('machine_days_per_ha')->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v)->values();
        $dph = $runs->pluck('days_per_ha')->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v)->values();

        $pdph = $this->filterOutliersIqr($pdph);
        $mdph = $this->filterOutliersIqr($mdph);
        $dph = $this->filterOutliersIqr($dph);

        $medPdph = $this->quantile($pdph, 0.5);
        $medMdph = $this->quantile($mdph, 0.5);
        $medDph = $this->quantile($dph, 0.5);

        $p25Pdph = $this->quantile($pdph, 0.25);
        $p75Pdph = $this->quantile($pdph, 0.75);

        $p25Mdph = $this->quantile($mdph, 0.25);
        $p75Mdph = $this->quantile($mdph, 0.75);

        $p25Dph = $this->quantile($dph, 0.25);
        $p75Dph = $this->quantile($dph, 0.75);

        return [
            'suggested_team_size' => $this->suggestFromRuns($runs, 'persons_per_day'),
            'suggested_machinery_count' => $this->suggestFromRuns($runs, 'machines_per_day'),
            'kpi' => [
                'person_days_per_ha' => ['median' => $medPdph, 'p25' => $p25Pdph, 'p75' => $p75Pdph, 'n' => $pdph->count()],
                'machine_days_per_ha' => ['median' => $medMdph, 'p25' => $p25Mdph, 'p75' => $p75Mdph, 'n' => $mdph->count()],
                'days_per_ha' => ['median' => $medDph, 'p25' => $p25Dph, 'p75' => $p75Dph, 'n' => $dph->count()],
            ],
        ];
    }

    private function suggestFromRuns(Collection $runs, string $field): ?int
    {
        $values = $runs->pluck($field)
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => (float) $v)
            ->values();

        $values = $this->filterOutliersIqr($values);
        $median = $this->quantile($values, 0.5);

        return $median === null ? null : max(1, (int) round($median));
    }

    /**
     * Filtrado robusto por IQR. Devuelve colección filtrada.
     */
    private function filterOutliersIqr(Collection $values): Collection
    {
        if ($values->count() < 4) {
            return $values;
        }

        $q1 = $this->quantile($values, 0.25);
        $q3 = $this->quantile($values, 0.75);

        if ($q1 === null || $q3 === null) {
            return $values;
        }

        $iqr = $q3 - $q1;
        if ($iqr <= 0) {
            return $values;
        }

        $low = $q1 - 1.5 * $iqr;
        $high = $q3 + 1.5 * $iqr;

        return $values->filter(fn ($v) => $v >= $low && $v <= $high)->values();
    }

    /**
     * Quantil con interpolación lineal (valores ordenados).
     */
    private function quantile(Collection $values, float $q): ?float
    {
        $n = $values->count();
        if ($n === 0) {
            return null;
        }

        $sorted = $values->sort()->values();
        if ($n === 1) {
            return (float) $sorted[0];
        }

        $pos = ($n - 1) * $q;
        $lower = (int) floor($pos);
        $upper = (int) ceil($pos);

        $lowerVal = (float) $sorted[$lower];
        $upperVal = (float) $sorted[$upper];

        if ($lower === $upper) {
            return $lowerVal;
        }

        $weight = $pos - $lower;

        return $lowerVal + ($upperVal - $lowerVal) * $weight;
    }
}
