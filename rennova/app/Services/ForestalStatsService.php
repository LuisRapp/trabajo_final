<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\ParteDiario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ForestalStatsService
{
    private const CACHE_TTL = 21600; // 6 horas

    private const FALLBACK_PUNTO_EQUILIBRIO = 75; // tn si faltan datos

    /**
     * Promedio ponderado de ventas (precio unitario real)
     */
    public function getPrecioPromedioVenta(Lote $lote, ?string $desde = null, ?string $hasta = null): float
    {
        $fechaDesde = $desde ? Carbon::parse($desde) : null;
        $fechaHasta = $hasta ? Carbon::parse($hasta) : null;
        $cacheKey = "stats.precio_promedio_venta.{$lote->id_lote}.".($fechaDesde ? $fechaDesde->format('Ymd') : 'na').'.'.($fechaHasta ? $fechaHasta->format('Ymd') : 'na');

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($lote, $fechaDesde, $fechaHasta) {
                $query = DB::table('venta_cargas as vc')
                    ->join('cargas as c', 'vc.id_carga', '=', 'c.id_carga')
                    ->where('c.id_lote', $lote->id_lote);

                if ($fechaDesde && $fechaHasta) {
                    $query->whereBetween('c.fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()]);
                } elseif ($fechaDesde) {
                    $query->whereDate('c.fecha_carga', '>=', $fechaDesde->toDateString());
                }

                $row = $query
                    ->selectRaw('SUM(COALESCE(vc.subtotal, vc.precio_unitario * vc.peso_toneladas)) as monto_total')
                    ->selectRaw('SUM(vc.peso_toneladas) as ton_total')
                    ->first();

                $montoTotal = (float) ($row->monto_total ?? 0);
                $tonTotal = (float) ($row->ton_total ?? 0);

                if ($tonTotal <= 0) {
                    return 0.0;
                }

                return round($montoTotal / $tonTotal, 2);
            }
        );
    }

    /**
     * Costo promedio por tonelada (fuente única: costo_total_dia).
     *
     * `costo_total_dia` ya incluye insumos + maquinaria + mano de obra,
     * calculado por ParteDiarioCostoService.
     */
    public function getCostoPromedioPorTn(Lote $lote, ?string $desde = null, ?string $hasta = null): float
    {
        $fechaDesde = $desde ? Carbon::parse($desde) : Carbon::now()->subMonths(6);
        $fechaHasta = $hasta ? Carbon::parse($hasta) : null;
        $cacheKey = "stats.costo_prom_tn.{$lote->id_lote}.{$fechaDesde->format('Ymd')}.".($fechaHasta ? $fechaHasta->format('Ymd') : 'na');

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($lote, $fechaDesde, $fechaHasta) {
                $partesQuery = ParteDiario::query()->where('id_lote', $lote->id_lote);

                if ($fechaHasta) {
                    $partesQuery->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()]);
                } else {
                    $partesQuery->whereDate('fecha', '>=', $fechaDesde->toDateString());
                }

                $costoTotal = (float) $partesQuery->sum('costo_total_dia');

                $cargasQuery = DB::table('cargas')
                    ->where('id_lote', $lote->id_lote)
                    ->whereNull('cargas.deleted_at');

                if ($fechaHasta) {
                    $cargasQuery->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()]);
                } else {
                    $cargasQuery->whereDate('fecha_carga', '>=', $fechaDesde->toDateString());
                }

                $totalTon = (float) $cargasQuery->sum(DB::raw('peso_neto / 1000.0'));

                if ($totalTon <= 0) {
                    return 0.0;
                }

                return round($costoTotal / $totalTon, 2);
            }
        );
    }

    /**
     * Punto de equilibrio diario en toneladas por día.
     *
     * Regla: costo fijo = mano de obra de días caídos (jornales); costo
     * variable = el resto (insumos + maquinaria + mano de obra de producción).
     *
     * Fórmula: (fijo ÷ días del período) ÷ (precio − variable ÷ toneladas).
     *
     * @return float Toneladas por día necesarias para cubrir costos
     */
    public function getPuntoEquilibrioDiario(Lote $lote, ?string $desde = null, ?string $hasta = null): float
    {
        $fechaDesde = $desde ? Carbon::parse($desde) : Carbon::now()->subMonth();
        $fechaHasta = $hasta ? Carbon::parse($hasta) : Carbon::now();
        $cacheKey = "stats.punto_equilibrio.{$lote->id_lote}.{$fechaDesde->format('Ymd')}.".($fechaHasta ? $fechaHasta->format('Ymd') : 'na');

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($lote, $fechaDesde, $fechaHasta) {
                $desde = $fechaDesde->toDateString();
                $hasta = $fechaHasta->toDateString();

                $precio = $this->getPrecioPromedioVenta($lote, $desde, $hasta);

                $costoTotal = (float) ParteDiario::query()
                    ->where('id_lote', $lote->id_lote)
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->sum('costo_total_dia');

                $fijoTotal = (float) ParteDiario::query()
                    ->where('id_lote', $lote->id_lote)
                    ->where('es_dia_caido', true)
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->sum('costo_mano_obra');

                $variableTotal = max(0.0, $costoTotal - $fijoTotal);

                $toneladas = (float) DB::table('cargas')
                    ->where('id_lote', $lote->id_lote)
                    ->whereNull('cargas.deleted_at')
                    ->whereBetween('fecha_carga', [$desde, $hasta])
                    ->sum(DB::raw('peso_neto / 1000.0'));

                if ($toneladas <= 0 || $precio <= 0) {
                    return self::FALLBACK_PUNTO_EQUILIBRIO;
                }

                $variablePorTonelada = $variableTotal / $toneladas;
                $margen = $precio - $variablePorTonelada;

                if ($margen <= 0) {
                    return self::FALLBACK_PUNTO_EQUILIBRIO;
                }

                $dias = max(1, $fechaDesde->diffInDays($fechaHasta));
                $fijoPorDia = $fijoTotal / $dias;

                if ($fijoPorDia <= 0) {
                    return 0.0;
                }

                $punto = $fijoPorDia / $margen;

                if ($punto <= 0 || ! is_finite($punto)) {
                    return self::FALLBACK_PUNTO_EQUILIBRIO;
                }

                return round($punto, 2);
            }
        );
    }

    /**
     * Proyección mensual global (solo días hábiles lun-vie) comparada contra el
     * punto de equilibrio agregado de todos los lotes en producción.
     *
     * @return array{objetivo_mensual_tn: float, proyeccion_mensual_tn: float, dias_habiles_mes: int, deficit_tn: float, cumple_objetivo: bool}
     */
    public function getProyeccionVsEquilibrio(): array
    {
        $objetivoDiario = $this->objetivoDiarioGlobal();
        $metaDiaria = app(ClimaAnalisisService::class)->calcularMetaDiaria();
        $diasHabiles = $this->diasHabilesDelMes(Carbon::now());

        return $this->calcularProyeccionMensual($objetivoDiario, $metaDiaria, $diasHabiles);
    }

    /**
     * Suma el punto de equilibrio diario (tn/día) de todos los lotes en producción.
     */
    private function objetivoDiarioGlobal(): float
    {
        $total = 0.0;

        foreach (Lote::whereIn('estado', ['activo', 'en_proceso'])->get() as $lote) {
            $total += $this->getPuntoEquilibrioDiario($lote);
        }

        return round($total, 2);
    }

    /**
     * Cantidad de días hábiles (lunes a viernes) del mes de una fecha.
     */
    public function diasHabilesDelMes(Carbon $fecha): int
    {
        $dias = 0;
        $cursor = $fecha->copy()->startOfMonth();
        $fin = $fecha->copy()->endOfMonth();

        while ($cursor->lessThanOrEqualTo($fin)) {
            if ($cursor->isWeekday()) {
                $dias++;
            }
            $cursor->addDay();
        }

        return $dias;
    }

    /**
     * Proyección mensual pura: objetivo y proyección a partir de tasas diarias y
     * días hábiles, con el déficit resultante (positivo = quedar corto).
     *
     * @return array{objetivo_mensual_tn: float, proyeccion_mensual_tn: float, dias_habiles_mes: int, deficit_tn: float, cumple_objetivo: bool}
     */
    public function calcularProyeccionMensual(float $objetivoDiario, float $metaDiaria, int $diasHabiles): array
    {
        $objetivoMensual = round($objetivoDiario * $diasHabiles, 2);
        $proyeccionMensual = round($metaDiaria * $diasHabiles, 2);
        $deficit = round($objetivoMensual - $proyeccionMensual, 2);

        return [
            'objetivo_mensual_tn' => $objetivoMensual,
            'proyeccion_mensual_tn' => $proyeccionMensual,
            'dias_habiles_mes' => $diasHabiles,
            'deficit_tn' => $deficit,
            'cumple_objetivo' => $deficit <= 0,
        ];
    }

    /**
     * Composición de gastos (incluye Mano de Obra calculada)
     */
    public function getComposicionGastos(Lote $lote, ?string $desde = null): array
    {
        $fechaDesde = $desde ? Carbon::parse($desde) : Carbon::now()->subMonths(6);

        return Cache::remember(
            "stats.composicion_gastos.{$lote->id_lote}.{$fechaDesde->format('Ymd')}",
            self::CACHE_TTL,
            function () use ($lote, $fechaDesde) {
                // Gastos se basan en costos de parte_diarios (insumos + maquinaria)
                $rows = ParteDiario::query()
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha', '>=', $fechaDesde->toDateString())
                    ->selectRaw('SUM(COALESCE(costo_insumos,0)) as insumos')
                    ->selectRaw('SUM(COALESCE(costo_maquinaria,0)) as maquinaria')
                    ->selectRaw('SUM(COALESCE(costo_mano_obra,0)) as mano_obra')
                    ->first();

                $labels = [];
                $data = [];

                $insumos = (float) ($rows->insumos ?? 0);
                $maquinaria = (float) ($rows->maquinaria ?? 0);
                $manoObra = (float) ($rows->mano_obra ?? 0);

                if ($insumos > 0) {
                    $labels[] = 'Insumos';
                    $data[] = round($insumos, 2);
                }
                if ($maquinaria > 0) {
                    $labels[] = 'Maquinaria';
                    $data[] = round($maquinaria, 2);
                }
                if ($manoObra > 0) {
                    $labels[] = 'Mano de Obra';
                    $data[] = round($manoObra, 2);
                }

                return [
                    'labels' => $labels,
                    'data' => $data,
                ];
            }
        );
    }

    /**
     * Producción diaria (últimos 30 días) en toneladas
     */
    public function getProduccionDiaria(Lote $lote): array
    {
        return Cache::remember(
            "stats.produccion_diaria.{$lote->id_lote}",
            self::CACHE_TTL,
            function () use ($lote) {
                $desde = Carbon::now()->subDays(29)->toDateString();
                $rows = DB::table('cargas')
                    ->selectRaw('DATE(fecha_carga) as fecha')
                    ->selectRaw('SUM(peso_neto/1000.0) as toneladas')
                    ->where('id_lote', $lote->id_lote)
                    ->whereNull('cargas.deleted_at')
                    ->whereDate('fecha_carga', '>=', $desde)
                    ->groupBy(DB::raw('DATE(fecha_carga)'))
                    ->orderBy('fecha')
                    ->get();

                $fechas = [];
                $data = [];
                foreach ($rows as $row) {
                    $fechas[] = Carbon::parse($row->fecha)->format('d/m');
                    $data[] = round((float) $row->toneladas, 2);
                }

                return [
                    'fechas' => $fechas,
                    'data' => $data,
                ];
            }
        );
    }

    /**
     * Evolución de costo unitario (últimos 6 meses) promedio por mes
     */
    public function getEvolucionCostoUnitario(Lote $lote): array
    {
        return Cache::remember(
            "stats.costo_unitario_serie.{$lote->id_lote}",
            self::CACHE_TTL,
            function () use ($lote) {
                $desde = Carbon::now()->subMonths(5)->startOfMonth();
                $rows = ParteDiario::query()
                    ->selectRaw(DB::raw($this->dateTrunc('fecha', 'month').' as periodo'))
                    ->selectRaw('AVG(costo_unitario_calculado) as costo_prom')
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha', '>=', $desde->toDateString())
                    ->groupBy('periodo')
                    ->orderBy('periodo')
                    ->get();

                $labels = [];
                $data = [];
                foreach ($rows as $row) {
                    $labels[] = Carbon::parse($row->periodo.'-01')->isoFormat('MMM YYYY');
                    $data[] = round((float) $row->costo_prom, 2);
                }

                return [
                    'labels' => $labels,
                    'data' => $data,
                ];
            }
        );
    }

    /**
     * Suma de gastos, opcionalmente solo fijos (es_fijo = true si existe)
     */
    private function dateTrunc(string $column, string $granularity): string
    {
        // Soportar SQLite y Postgres; MySQL usa DATE_FORMAT
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return "DATE_TRUNC('{$granularity}', {$column})";
        }
        if ($driver === 'mysql') {
            return "DATE_FORMAT({$column}, '%Y-%m-01')";
        }

        // SQLite
        return "strftime('%Y-%m-01', {$column})";
    }
}
