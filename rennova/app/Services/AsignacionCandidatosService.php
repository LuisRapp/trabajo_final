<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionInsumo;
use App\Models\PropuestaAsignacionMaquinaria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de Selección de Candidatos
 * 
 * Responsable de seleccionar empleados, maquinarias e insumos para propuestas
 */
class AsignacionCandidatosService
{
    public function __construct(
        private readonly HistoricalTaskPerformanceRepository $repo
    ) {}

    /**
     * Popula candidatos (empleados, maquinarias, insumos) para una propuesta
     */
    public function populateProposalCandidates(
        PropuestaAsignacion $proposal,
        TaskType $taskType,
        ?string $species,
        \Carbon\Carbon $since
    ): void {
        $this->populateEmployees($proposal, $taskType, $species, $since);
        $this->populateMachinery($proposal, $taskType, $species, $since);
        $this->populateInsumos($proposal, $since);
    }

    /**
     * Selecciona empleados candidatos basado en histórico y roles
     */
    private function populateEmployees(
        PropuestaAsignacion $proposal,
        TaskType $taskType,
        ?string $species,
        \Carbon\Carbon $since
    ): void {
        // Ranking simple: frecuencia histórica de participación en cargas.
        $employees = collect($this->repo->topEmployeesForTaskAndSpecies(
            taskType: $taskType->value,
            species: $species,
            since: $since,
            limit: 10
        ));

        $busyEmployees = $this->busyEmployeeIds();

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
    }

    /**
     * Selecciona maquinarias candidatas basado en histórico y categorías
     */
    private function populateMachinery(
        PropuestaAsignacion $proposal,
        TaskType $taskType,
        ?string $species,
        \Carbon\Carbon $since
    ): void {
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
    }

    /**
     * Selecciona insumos candidatos basado en salidas históricas
     */
    private function populateInsumos(PropuestaAsignacion $proposal, \Carbon\Carbon $since): void
    {
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
    }

    /**
     * Obtiene IDs de empleados ocupados en lotes en proceso
     */
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

    /**
     * Obtiene IDs de maquinarias ocupadas en lotes en proceso
     */
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
     * Resuelve tamaño del equipo desde la propuesta
     */
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

    /**
     * Verifica si la tarea necesita trabajadores de corte
     */
    private function taskNeedsCorte(TaskType $taskType): bool
    {
        return in_array($taskType->value, ['tala_rasa', 'raleo', 'poda'], true);
    }

    /**
     * Verifica si un rol coincide con keywords
     */
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

    /**
     * Detecta categoría de maquinaria (carga, arrastre, corte)
     */
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

    /**
     * Busca empleados por rol cuando no hay histórico
     */
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
}
