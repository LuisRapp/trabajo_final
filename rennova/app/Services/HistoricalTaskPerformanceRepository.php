<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistoricalTaskPerformanceRepository
{
    /**
     * Devuelve registros diarios de producción para análisis histórico.
     * Cada fila representa 1 Parte Diario (por la constraint de unicidad id_lote+fecha).
     */
    public function fetchDailyProductionRecords(
        ?string $taskType = null,
        ?string $species = null,
        ?Carbon $since = null
    ) {
        $since ??= Carbon::today()->subMonths(24);

        $query = DB::table('parte_diarios as pd')
            ->join('lotes as l', 'l.id_lote', '=', 'pd.id_lote')
            ->leftJoin('lote_tareas as lt', 'lt.id_lote_tarea', '=', 'pd.id_lote_tarea')
            ->leftJoin('cargas as c', 'c.id_parte_diario', '=', 'pd.id_parte_diario')
            ->leftJoin('carga_empleado as ce', 'ce.id_carga', '=', 'c.id_carga')
            ->leftJoin('carga_maquinaria as cm', 'cm.id_carga', '=', 'c.id_carga')
            ->leftJoin('parte_diario_empleado as pde', 'pde.id_parte_diario', '=', 'pd.id_parte_diario')
            ->leftJoin('maquinaria_parte_diarios as mpd', 'mpd.id_parte_diario', '=', 'pd.id_parte_diario')
            ->whereDate('pd.fecha', '>=', $since->toDateString())
            ->where('pd.es_dia_caido', false)
            ->where(function ($q) {
                $q->whereNotNull('pd.tipo_tarea')
                    ->orWhereNotNull('lt.tipo_tarea');
            })
            ->select([
                'pd.id_parte_diario',
                'pd.id_lote',
                'pd.id_lote_tarea',
                'pd.fecha',
                DB::raw('COALESCE(lt.tipo_tarea, pd.tipo_tarea) as tipo_tarea'),
                'l.especie',
                DB::raw('COALESCE(lt.superficie_afectada_ha, l.superficie) as superficie'),
                DB::raw('COALESCE(NULLIF(COUNT(DISTINCT ce.id_empleado),0), COUNT(DISTINCT pde.id_empleado)) as empleados_count'),
                DB::raw('COALESCE(NULLIF(COUNT(DISTINCT cm.id_maquinaria),0), COUNT(DISTINCT mpd.id_maquinaria)) as maquinarias_count'),
            ])
            ->groupBy([
                'pd.id_parte_diario',
                'pd.id_lote',
                'pd.id_lote_tarea',
                'pd.fecha',
                'l.especie',
                'l.superficie',
                'lt.tipo_tarea',
                'lt.superficie_afectada_ha',
            ])
            ->havingRaw('COALESCE(NULLIF(COUNT(DISTINCT ce.id_empleado),0), COUNT(DISTINCT pde.id_empleado)) > 0 OR COALESCE(NULLIF(COUNT(DISTINCT cm.id_maquinaria),0), COUNT(DISTINCT mpd.id_maquinaria)) > 0')
            ->orderBy('pd.id_lote')
            ->orderBy('pd.fecha');

        if ($taskType !== null) {
            $query->whereRaw('COALESCE(lt.tipo_tarea, pd.tipo_tarea) = ?', [$taskType]);
        }

        if ($species !== null) {
            $query->where('l.especie', $species);
        }

        return $query->get();
    }

    public function topEmployeesForTaskAndSpecies(
        ?string $taskType = null,
        ?string $species = null,
        ?Carbon $since = null,
        int $limit = 5
    ) {
        $since ??= Carbon::today()->subMonths(24);

        $query = DB::table('carga_empleado as ce')
            ->join('cargas as c', 'c.id_carga', '=', 'ce.id_carga')
            ->join('parte_diarios as pd', 'pd.id_parte_diario', '=', 'c.id_parte_diario')
            ->join('lotes as l', 'l.id_lote', '=', 'pd.id_lote')
            ->leftJoin('lote_tareas as lt', 'lt.id_lote_tarea', '=', 'pd.id_lote_tarea')
            ->join('empleados as e', 'e.id_empleado', '=', 'ce.id_empleado')
            ->leftJoin('rol_laborals as rl', 'rl.id_rol_laboral', '=', 'e.id_rol_laboral')
            ->whereNotNull('c.id_parte_diario')
            ->whereDate('pd.fecha', '>=', $since->toDateString())
            ->where('pd.es_dia_caido', false)
            ->where(function ($q) {
                $q->whereNotNull('pd.tipo_tarea')
                    ->orWhereNotNull('lt.tipo_tarea');
            })
            ->select([
                'e.id_empleado',
                'e.apellido',
                'e.nombre',
                DB::raw('rl.nombre as rol_nombre'),
                DB::raw('COUNT(DISTINCT pd.id_parte_diario) as days_count'),
            ])
            ->groupBy([
                'e.id_empleado',
                'e.apellido',
                'e.nombre',
                'rl.nombre',
            ])
            ->orderByDesc('days_count')
            ->limit($limit);

        if ($taskType !== null) {
            $query->whereRaw('COALESCE(lt.tipo_tarea, pd.tipo_tarea) = ?', [$taskType]);
        }

        if ($species !== null) {
            $query->where('l.especie', $species);
        }

        return $query->get();
    }

    public function topMaquinariasForTaskAndSpecies(
        ?string $taskType = null,
        ?string $species = null,
        ?Carbon $since = null,
        int $limit = 5
    ) {
        $since ??= Carbon::today()->subMonths(24);

        $query = DB::table('carga_maquinaria as cm')
            ->join('cargas as c', 'c.id_carga', '=', 'cm.id_carga')
            ->join('parte_diarios as pd', 'pd.id_parte_diario', '=', 'c.id_parte_diario')
            ->join('lotes as l', 'l.id_lote', '=', 'pd.id_lote')
            ->leftJoin('lote_tareas as lt', 'lt.id_lote_tarea', '=', 'pd.id_lote_tarea')
            ->join('maquinarias as m', 'm.id_maquinaria', '=', 'cm.id_maquinaria')
            ->leftJoin('tipo_maquinarias as tm', 'tm.id_tipo_maquinaria', '=', 'm.id_tipo_maquinaria')
            ->whereNotNull('c.id_parte_diario')
            ->whereDate('pd.fecha', '>=', $since->toDateString())
            ->where('pd.es_dia_caido', false)
            ->where(function ($q) {
                $q->whereNotNull('pd.tipo_tarea')
                    ->orWhereNotNull('lt.tipo_tarea');
            })
            ->select([
                'm.id_maquinaria',
                'm.modelo',
                DB::raw('tm.nombre as tipo_nombre'),
                DB::raw('COUNT(DISTINCT pd.id_parte_diario) as days_count'),
            ])
            ->groupBy([
                'm.id_maquinaria',
                'm.modelo',
                'tm.nombre',
            ])
            ->orderByDesc('days_count')
            ->limit($limit);

        if ($taskType !== null) {
            $query->whereRaw('COALESCE(lt.tipo_tarea, pd.tipo_tarea) = ?', [$taskType]);
        }

        if ($species !== null) {
            $query->where('l.especie', $species);
        }

        return $query->get();
    }

    public function topInsumosBySalidas(
        ?Carbon $since = null,
        int $limit = 10
    ) {
        $since ??= Carbon::today()->subMonths(24);

        return DB::table('movimiento_stocks as ms')
            ->join('insumos as i', 'i.id_insumo', '=', 'ms.id_insumo')
            ->whereDate('ms.fecha', '>=', $since->toDateString())
            ->where('ms.tipo', 'salida')
            ->select([
                'i.id_insumo',
                'i.nombre',
                DB::raw('SUM(ms.cantidad) as cantidad_total'),
            ])
            ->groupBy([
                'i.id_insumo',
                'i.nombre',
            ])
            ->orderByDesc('cantidad_total')
            ->limit($limit)
            ->get();
    }

    public function medianDailySalidaQuantityForInsumo(
        int $insumoId,
        ?Carbon $since = null
    ): ?float {
        $since ??= Carbon::today()->subMonths(24);

        // Mediana del total diario de salidas del insumo.
        // Postgres: percentile_cont(0.5) devuelve null si no hay filas.
        $row = DB::table(DB::raw("(\n            select
                percentile_cont(0.5) within group (order by daily_qty) as median_daily_qty
            from (
                select ms.fecha, sum(ms.cantidad) as daily_qty
                from movimiento_stocks ms
                where ms.id_insumo = ?
                  and ms.tipo = 'salida'
                  and ms.fecha >= ?
                group by ms.fecha
            ) t
        ) as med"))
            ->setBindings([$insumoId, $since->toDateString()])
            ->first();

        if (!$row || $row->median_daily_qty === null) {
            return null;
        }

        return (float) $row->median_daily_qty;
    }

    public function unitPriceForInsumo(int $insumoId): ?float
    {
        $insumo = DB::table('insumos')->where('id_insumo', $insumoId)->first();
        if ($insumo && $insumo->costo_unitario !== null) {
            return (float) $insumo->costo_unitario;
        }

        // Fallback: último precio registrado (prioriza entradas, si existen).
        $latestEntrada = DB::table('movimiento_stocks')
            ->where('id_insumo', $insumoId)
            ->where('tipo', 'entrada')
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento_stock')
            ->value('precio_unitario');

        if ($latestEntrada !== null) {
            return (float) $latestEntrada;
        }

        $latestAny = DB::table('movimiento_stocks')
            ->where('id_insumo', $insumoId)
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento_stock')
            ->value('precio_unitario');

        return $latestAny !== null ? (float) $latestAny : null;
    }

    public function medianDailySalidaQuantityForInsumoAndTask(
        int $insumoId,
        string $taskType,
        ?Carbon $since = null
    ): ?float {
        $since ??= Carbon::today()->subMonths(24);

        // Estrategia:
        // 1) total diario de salidas del insumo (movimiento_stocks)
        // 2) "peso" diario por tarea según actividad en ParteDiario (maquinarias si existen, si no empleados)
        // 3) asignar consumo a tarea: total_diario * (peso_tarea / peso_total_dia)
        // 4) mediana de asignaciones diarias para esa tarea
        //
        // Nota: La FK a parte_diarios puede estar presente, pero este cálculo
        // usa asignación por fecha y ponderación para cubrir historiales legacy.
        $sql = <<<'SQL'
            with daily_insumo as (
                select ms.fecha, sum(ms.cantidad) as qty
                from movimiento_stocks ms
                where ms.id_insumo = :insumo
                  and ms.tipo = 'salida'
                  and ms.fecha >= :since
                group by ms.fecha
            ),
            daily_activity as (
                select
                    pd.fecha,
                    coalesce(lt.tipo_tarea, pd.tipo_tarea) as tipo_tarea,
                    count(distinct cm.id_maquinaria) as mach_cnt_carga,
                    count(distinct mpd.id_maquinaria) as mach_cnt_parte,
                    count(distinct ce.id_empleado) as emp_cnt_carga,
                    count(distinct pde.id_empleado) as emp_cnt_parte,
                    case
                        when count(distinct cm.id_maquinaria) > 0 then count(distinct cm.id_maquinaria)
                        else count(distinct mpd.id_maquinaria)
                    end as mach_cnt,
                    case
                        when count(distinct ce.id_empleado) > 0 then count(distinct ce.id_empleado)
                        else count(distinct pde.id_empleado)
                    end as emp_cnt
                from parte_diarios pd
                left join lote_tareas lt on lt.id_lote_tarea = pd.id_lote_tarea
                left join cargas c on c.id_parte_diario = pd.id_parte_diario
                left join carga_maquinaria cm on cm.id_carga = c.id_carga
                left join carga_empleado ce on ce.id_carga = c.id_carga
                left join maquinaria_parte_diarios mpd on mpd.id_parte_diario = pd.id_parte_diario
                left join parte_diario_empleado pde on pde.id_parte_diario = pd.id_parte_diario
                where pd.fecha >= :since
                  and pd.es_dia_caido = false
                  and (pd.tipo_tarea is not null or lt.tipo_tarea is not null)
                group by pd.fecha, coalesce(lt.tipo_tarea, pd.tipo_tarea)
                having
                    (
                        case
                            when count(distinct cm.id_maquinaria) > 0 then count(distinct cm.id_maquinaria)
                            else count(distinct mpd.id_maquinaria)
                        end
                    ) > 0
                    or (
                        case
                            when count(distinct ce.id_empleado) > 0 then count(distinct ce.id_empleado)
                            else count(distinct pde.id_empleado)
                        end
                    ) > 0
            ),
            daily_totals as (
                select
                    fecha,
                    sum(mach_cnt) as mach_total,
                    sum(emp_cnt) as emp_total
                from daily_activity
                group by fecha
            ),
            allocated as (
                select
                    di.fecha,
                    da.tipo_tarea,
                    di.qty,
                    case
                        when dt.mach_total > 0 then (da.mach_cnt::numeric / nullif(dt.mach_total::numeric, 0))
                        else (da.emp_cnt::numeric / nullif(dt.emp_total::numeric, 0))
                    end as share,
                    di.qty * case
                        when dt.mach_total > 0 then (da.mach_cnt::numeric / nullif(dt.mach_total::numeric, 0))
                        else (da.emp_cnt::numeric / nullif(dt.emp_total::numeric, 0))
                    end as allocated_qty
                from daily_insumo di
                join daily_activity da on da.fecha = di.fecha
                join daily_totals dt on dt.fecha = di.fecha
                where da.tipo_tarea = :task
            )
            select percentile_cont(0.5) within group (order by allocated_qty) as median_daily_qty
            from allocated
            where allocated_qty is not null
        SQL;

        $row = DB::selectOne($sql, [
            'insumo' => $insumoId,
            'task' => $taskType,
            'since' => $since->toDateString(),
        ]);

        if (!$row || $row->median_daily_qty === null) {
            return null;
        }

        return (float) $row->median_daily_qty;
    }
}
