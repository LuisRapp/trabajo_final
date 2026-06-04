<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Servicio de Métricas de Asignación
 * 
 * Responsable de cálculos estadísticos para propuestas de asignación
 */
class AsignacionMetricasService
{
    /**
     * Construye "runs" de tarea por lote, cortando cuando hay un gap grande de días.
     * Devuelve una colección de runs agregados.
     */
    public function buildRuns($records, int $gapDaysForRunSplit): Collection
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

    /**
     * Finaliza un run calculando métricas por hectárea
     */
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

    /**
     * Calcula métricas estadísticas de los runs
     */
    public function computeMetrics(Collection $runs): array
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

    /**
     * Sugiere valor desde runs usando mediana
     */
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
    public function quantile(Collection $values, float $q): ?float
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
