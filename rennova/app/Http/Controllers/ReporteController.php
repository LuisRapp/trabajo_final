<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Lote;
use App\Models\ParteDiario;
use App\Models\Carga;
use App\Services\ForestalStatsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    protected ForestalStatsService $statsService;

    public function __construct(ForestalStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reporte $reporte)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reporte $reporte)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reporte $reporte)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reporte $reporte)
    {
        //
    }

    /**
     * Mostrar estadísticas forestales (costos, ingresos, rentabilidad)
     */
    public function estadisticasForestales()
    {
        // Obtener lotes activos del usuario
        $lotes = Lote::where('estado', 'activo')->get();

        // Si no hay lotes, pasar vacío
        if ($lotes->isEmpty()) {
            return view('reportes.estadisticas-forestales', [
                'lotes' => collect(),
                'estadisticas_globales' => [
                    'precio_promedio' => 0,
                    'costo_promedio' => 0,
                    'punto_equilibrio' => 0,
                    'total_lotes' => 0,
                ],
                'lotes_estadisticas' => [],
                'produccion_30_dias' => [],
                'fechas_30_dias' => [],
                'distribucion_costos' => [],
                'evolucion_6_meses' => [],
                'fechas_6_meses' => [],
            ]);
        }

        // Calcular estadísticas por lote
        $lotes_estadisticas = $lotes->map(function (Lote $lote) {
            $precio_promedio = $this->statsService->getPrecioPromedioVenta($lote);
            $costo_promedio = $this->statsService->getCostoPromedioPorTn($lote);
            $punto_equilibrio = $this->statsService->getPuntoEquilibrioDiario($lote);
            
            return [
                'id' => $lote->id_lote,
                'nombre' => $lote->ubicacion ?? $lote->propietario ?? 'Lote ' . $lote->id_lote,
                'hectareas' => $lote->superficie ?? 0,
                'precio_promedio' => $precio_promedio,
                'costo_promedio' => $costo_promedio,
                'punto_equilibrio' => $punto_equilibrio,
                'rentabilidad' => $precio_promedio - $costo_promedio,
            ];
        });

        // Estadísticas globales
        $estadisticas_globales = [
            'precio_promedio' => $lotes_estadisticas->avg('precio_promedio'),
            'costo_promedio' => $lotes_estadisticas->avg('costo_promedio'),
            'punto_equilibrio' => $lotes_estadisticas->avg('punto_equilibrio'),
            'total_lotes' => $lotes->count(),
            'rentabilidad_promedio' => $lotes_estadisticas->avg('rentabilidad'),
        ];

        // GRÁFICO 1: Producción últimos 30 días
        $hace_30_dias = Carbon::now()->subDays(30);
        $cargas_30_dias = Carga::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereDate('fecha_carga', '>=', $hace_30_dias->toDateString())
            ->selectRaw('DATE(fecha_carga) as fecha')
            ->selectRaw('SUM(peso_neto / 1000.0) as total_toneladas')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Crear array con todas las fechas de los últimos 30 días
        $fechas_30_dias = [];
        $produccion_30_dias = [];
        for ($i = 30; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i)->format('Y-m-d');
            $fechas_30_dias[] = Carbon::parse($fecha)->format('d/m');
            
            $carga = $cargas_30_dias->firstWhere('fecha', $fecha);
            $produccion_30_dias[] = $carga ? round((float)$carga->total_toneladas, 2) : 0;
        }

        // GRÁFICO 2: Distribución de Costos
        $hace_6_meses = Carbon::now()->subMonths(6);
        $costos_totales = ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereDate('fecha', '>=', $hace_6_meses->toDateString())
            ->selectRaw('SUM(COALESCE(costo_insumos, 0)) as insumos')
            ->selectRaw('SUM(COALESCE(costo_maquinaria, 0)) as maquinaria')
            ->selectRaw('SUM(COALESCE(costo_mano_obra, 0)) as mano_obra')
            ->first();

        $distribucion_costos = [
            ['name' => 'Insumos', 'value' => (float)($costos_totales->insumos ?? 0)],
            ['name' => 'Maquinaria', 'value' => (float)($costos_totales->maquinaria ?? 0)],
            ['name' => 'Mano de Obra', 'value' => (float)($costos_totales->mano_obra ?? 0)],
        ];

        // GRÁFICO 3: Evolución costo por tonelada últimos 6 meses
        $evolucion_costos = ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereDate('fecha', '>=', $hace_6_meses->toDateString())
            ->selectRaw('DATE_TRUNC(\'month\', fecha) as mes')
            ->selectRaw('AVG(costo_unitario_calculado) as costo_promedio')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $fechas_6_meses = [];
        $evolucion_6_meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes_fecha = Carbon::now()->subMonths($i);
            $fechas_6_meses[] = $mes_fecha->format('M Y');
            
            $costo = $evolucion_costos->firstWhere('mes', $mes_fecha->startOfMonth()->toDateTimeString());
            $evolucion_6_meses[] = $costo ? (float)$costo->costo_promedio : 0;
        }

        return view('reportes.estadisticas-forestales', [
            'lotes' => $lotes,
            'estadisticas_globales' => $estadisticas_globales,
            'lotes_estadisticas' => $lotes_estadisticas,
            'produccion_30_dias' => $produccion_30_dias,
            'fechas_30_dias' => $fechas_30_dias,
            'distribucion_costos' => $distribucion_costos,
            'evolucion_6_meses' => $evolucion_6_meses,
            'fechas_6_meses' => $fechas_6_meses,
        ]);
    }
}
