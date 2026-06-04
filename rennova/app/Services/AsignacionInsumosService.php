<?php

namespace App\Services;

use App\Enums\TaskType;
use App\Models\Insumo;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionInsumo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de Estimación de Insumos Semana 1
 * 
 * Responsable de calcular cantidades y costos de insumos para la primera semana
 */
class AsignacionInsumosService
{
    public function __construct(
        private readonly HistoricalTaskPerformanceRepository $repo
    ) {}

    /**
     * Asegura que los insumos de semana 1 tengan estimaciones de cantidad y costo
     */
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

    /**
     * Estima combustible para maquinarias cuando se usan valores por defecto
     */
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

    /**
     * Estima nafta para personas cuando se usan valores por defecto
     */
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

    /**
     * Agrega insumos de motosierra (aceite negro, lima) para tareas de corte
     */
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
     * Verifica si la tarea necesita corte (tala, raleo, poda)
     */
    private function taskNeedsCorte(TaskType $taskType): bool
    {
        return in_array($taskType->value, ['tala_rasa', 'raleo', 'poda'], true);
    }
}
