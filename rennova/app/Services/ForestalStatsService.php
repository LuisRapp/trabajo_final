<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\ParteDiario;
use App\Models\Carga;
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
    public function getPrecioPromedioVenta(Lote $lote): float
    {
        return Cache::remember(
            "stats.precio_promedio_venta.{$lote->id_lote}",
            self::CACHE_TTL,
            function () use ($lote) {
                $row = DB::table('venta_cargas as vc')
                    ->join('cargas as c', 'vc.id_carga', '=', 'c.id_carga')
                    ->where('c.id_lote', $lote->id_lote)
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
     * Costo promedio por tonelada (gastos + mano de obra) / toneladas
     */
    public function getCostoPromedioPorTn(Lote $lote, ?string $desde = null): float
    {
        $fechaDesde = $desde ? Carbon::parse($desde) : Carbon::now()->subMonths(6);

        return Cache::remember(
            "stats.costo_prom_tn.{$lote->id_lote}.{$fechaDesde->format('Ymd')}",
            self::CACHE_TTL,
            function () use ($lote, $fechaDesde) {
                // Costos vienen de parte_diarios (calculados por insumos/consumos + mano de obra + maquinaria)
                $rows = ParteDiario::query()
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha', '>=', $fechaDesde->toDateString())
                    ->selectRaw('SUM(COALESCE(costo_insumos,0) + COALESCE(costo_maquinaria,0)) as gastos_insumos_maquinaria')
                    ->selectRaw('SUM(COALESCE(costo_mano_obra,0)) as mano_obra')
                    ->selectRaw('SUM(COALESCE(costo_total_dia,0)) as costo_total')
                    ->selectRaw('SUM(COALESCE(costo_unitario_calculado,0)) as costo_unitario_sum')
                    ->selectRaw('COUNT(*) as conteo_partes')
                    ->first();

                $totalGastos = (float) ($rows->gastos_insumos_maquinaria ?? 0);
                $costoManoObra = (float) ($rows->mano_obra ?? 0);

                $totalTon = DB::table('cargas')
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha_carga', '>=', $fechaDesde->toDateString())
                    ->sum(DB::raw('peso_neto / 1000.0'));

                if ($totalTon <= 0) {
                    return 0.0;
                }

                $numerador = $totalGastos + $costoManoObra;
                return round($numerador / $totalTon, 2);
            }
        );
    }

    /**
     * Punto de equilibrio diario en toneladas
     */
    public function getPuntoEquilibrioDiario(Lote $lote): float
    {
        return Cache::remember(
            "stats.punto_equilibrio.{$lote->id_lote}",
            self::CACHE_TTL,
            function () use ($lote) {
                $precioUnit = $this->getPrecioPromedioVenta($lote);
                $costoUnit = $this->getCostoPromedioPorTn($lote);

                // Estimar costos fijos diarios desde parte_diarios último mes
                $fijos = ParteDiario::query()
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha', '>=', Carbon::now()->subMonth()->toDateString())
                    ->avg('costo_total_dia');

                $fijos = $fijos ? (float) $fijos : self::FALLBACK_PUNTO_EQUILIBRIO;

                $denominador = $precioUnit - $costoUnit;
                if ($denominador <= 0 || $precioUnit <= 0) {
                    return self::FALLBACK_PUNTO_EQUILIBRIO;
                }

                $punto = $fijos / $denominador;
                if ($punto <= 0 || !is_finite($punto)) {
                    return self::FALLBACK_PUNTO_EQUILIBRIO;
                }

                return round($punto, 2);
            }
        );
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

                if ($insumos > 0) { $labels[] = 'Insumos'; $data[] = round($insumos, 2); }
                if ($maquinaria > 0) { $labels[] = 'Maquinaria'; $data[] = round($maquinaria, 2); }
                if ($manoObra > 0) { $labels[] = 'Mano de Obra'; $data[] = round($manoObra, 2); }

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
                    ->selectRaw(DB::raw($this->dateTrunc('fecha', 'month') . ' as periodo'))
                    ->selectRaw('AVG(costo_unitario_calculado) as costo_prom')
                    ->where('id_lote', $lote->id_lote)
                    ->whereDate('fecha', '>=', $desde->toDateString())
                    ->groupBy('periodo')
                    ->orderBy('periodo')
                    ->get();

                $labels = [];
                $data = [];
                foreach ($rows as $row) {
                    $labels[] = Carbon::parse($row->periodo . '-01')->format('M Y');
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
    /**
     * Helper crítico: calcular costo de mano de obra iterando partes diarios del período
     */
    private function calcularCostoManoObra(Lote $lote, $fechaDesde): float
    {
        $desde = Carbon::parse($fechaDesde);
        $total = 0.0;

        $partes = ParteDiario::with(['empleados', 'cargas'])
            ->where('id_lote', $lote->id_lote)
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->get();

        foreach ($partes as $parte) {
            foreach ($parte->empleados as $empleado) {
                try {
                    $total += $empleado->calcularCostoDia($parte->fecha, (bool) $parte->es_dia_caido, $parte->cargas);
                } catch (\Throwable $e) {
                    // Si no hay lógica o datos, ignoramos ese empleado
                }
            }
        }

        return round($total, 2);
    }

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
