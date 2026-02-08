<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Lote;
use App\Models\ParteDiario;
use App\Models\Carga;
use App\Models\Recibo;
use App\Services\ForestalStatsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Dompdf\Dompdf;
use Dompdf\Options;

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
    public function estadisticasForestales(Request $request)
    {
        $rangeCustom = $request->filled('desde') || $request->filled('hasta');

        $fechaDesde = $request->get('desde')
            ? Carbon::parse($request->get('desde'))
            : Carbon::now()->subDays(30);

        $fechaHasta = $request->get('hasta')
            ? Carbon::parse($request->get('hasta'))
            : Carbon::now();

        if ($fechaHasta->lessThan($fechaDesde)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $filtroDesde = $fechaDesde->toDateString();
        $filtroHasta = $fechaHasta->toDateString();
        $rangoLabel = $rangeCustom
            ? ($fechaDesde->format('d/m/Y') . ' - ' . $fechaHasta->format('d/m/Y'))
            : 'Últimos 30 días';

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
                'filtro_desde' => $filtroDesde,
                'filtro_hasta' => $filtroHasta,
                'rango_label' => $rangoLabel,
                'range_custom' => $rangeCustom,
            ]);
        }

        // Calcular estadísticas por lote
        $lotes_estadisticas = $lotes->map(function (Lote $lote) use ($filtroDesde, $filtroHasta) {
            $precio_promedio = $this->statsService->getPrecioPromedioVenta(
                $lote,
                $filtroDesde,
                $filtroHasta
            );
            $costo_promedio = $this->statsService->getCostoPromedioPorTn(
                $lote,
                $filtroDesde,
                $filtroHasta,
                true,
                false
            );
            $punto_equilibrio = $this->statsService->getPuntoEquilibrioDiario(
                $lote,
                $filtroDesde,
                $filtroHasta,
                true,
                false
            );
            
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
        $estadisticas_globales = $this->calcularEstadisticasGlobales($lotes, $fechaDesde, $fechaHasta);

        // GRÁFICO 1: Producción últimos 30 días
        $cargas_30_dias = Carga::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereBetween('fecha_carga', [$filtroDesde, $filtroHasta])
            ->selectRaw('DATE(fecha_carga) as fecha')
            ->selectRaw('SUM(peso_neto / 1000.0) as total_toneladas')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Crear array con todas las fechas del rango
        $fechas_30_dias = [];
        $produccion_30_dias = [];
        $cursor = $fechaDesde->copy();
        while ($cursor->lessThanOrEqualTo($fechaHasta)) {
            $fecha = $cursor->format('Y-m-d');
            $fechas_30_dias[] = $cursor->format('d/m');

            $carga = $cargas_30_dias->firstWhere('fecha', $fecha);
            $produccion_30_dias[] = $carga ? round((float) $carga->total_toneladas, 2) : 0;
            $cursor->addDay();
        }

        // GRÁFICO 2: Distribución de Costos
        $costos_totales = ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereBetween('fecha', [$filtroDesde, $filtroHasta])
            ->selectRaw('SUM(COALESCE(costo_insumos, 0)) as insumos')
            ->selectRaw('SUM(COALESCE(costo_maquinaria, 0)) as maquinaria')
            ->first();

        $empleadosIds = DB::table('lote_empleado')
            ->whereIn('id_lote', $lotes->pluck('id_lote'))
            ->pluck('id_empleado')
            ->unique();

        $liquidacionesPeriodo = 0.0;
        if ($empleadosIds->isNotEmpty()) {
            $liquidacionesPeriodo = (float) (Recibo::whereIn('id_empleado', $empleadosIds)
                ->whereBetween('fecha_emision', [$filtroDesde, $filtroHasta])
                ->sum('monto') ?? 0);
        }

        $distribucion_costos = [
            ['name' => 'Insumos', 'value' => (float)($costos_totales->insumos ?? 0)],
            ['name' => 'Maquinaria', 'value' => (float)($costos_totales->maquinaria ?? 0)],
            ['name' => 'Mano de Obra', 'value' => round($liquidacionesPeriodo, 2)],
        ];

        // GRÁFICO 3: Evolución costo por tonelada últimos 6 meses
        $evolucion_costos = ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereBetween('fecha', [$filtroDesde, $filtroHasta])
            ->selectRaw('DATE_TRUNC(\'month\', fecha) as mes')
            ->selectRaw('AVG(costo_unitario_calculado) as costo_promedio')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $fechas_6_meses = [];
        $evolucion_6_meses = [];
        $mesCursor = $fechaDesde->copy()->startOfMonth();
        $mesFin = $fechaHasta->copy()->startOfMonth();
        while ($mesCursor->lessThanOrEqualTo($mesFin)) {
            $fechas_6_meses[] = $mesCursor->format('M Y');

            $costo = $evolucion_costos->firstWhere('mes', $mesCursor->toDateTimeString());
            $evolucion_6_meses[] = $costo ? (float) $costo->costo_promedio : 0;
            $mesCursor->addMonth();
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
            'filtro_desde' => $filtroDesde,
            'filtro_hasta' => $filtroHasta,
            'rango_label' => $rangoLabel,
            'range_custom' => $rangeCustom,
        ]);
    }

    /**
     * Generar PDF de estadísticas forestales (lote o global)
     */
    public function estadisticasForestalesPdf(Request $request)
    {
        $idLote = $request->get('id_lote');
        $tipo = $idLote ? 'lote' : $request->get('tipo', 'global');

        $fechaDesde = $request->get('desde')
            ? Carbon::parse($request->get('desde'))
            : Carbon::now()->subMonths(6);

        $fechaHasta = $request->get('hasta')
            ? Carbon::parse($request->get('hasta'))
            : Carbon::now();

        if ($fechaHasta->lessThan($fechaDesde)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $periodo = $fechaDesde->format('d/m/Y') . ' - ' . $fechaHasta->format('d/m/Y');
        $generadoPor = auth()->user()->name ?? auth()->user()->email ?? 'Usuario';
        $fechaHora = Carbon::now()->format('d/m/Y H:i');
        $base = [
            'tipo' => $tipo,
            'periodo' => $periodo,
            'generado_por' => $generadoPor,
            'fecha_hora' => $fechaHora,
            'reporte_nombre' => $tipo === 'lote'
                ? 'Indicadores Financieros por Lote'
                : 'Indicadores Financieros Globales',
        ];

        if ($tipo === 'lote') {
            $request->validate([
                'id_lote' => 'required|exists:lotes,id_lote',
            ]);

            $lote = Lote::with([
                'empleados.rolLaboral',
                'maquinarias.tipoMaquinaria',
            ])->findOrFail($idLote);

            $produccionToneladas = (float) Carga::where('id_lote', $lote->id_lote)
                ->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->sum(DB::raw('peso_neto / 1000.0'));

            $produccionMensual = Carga::where('id_lote', $lote->id_lote)
                ->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('DATE_TRUNC(\'month\', fecha_carga) as mes')
                ->selectRaw('SUM(peso_neto / 1000.0) as toneladas')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $costosMensual = ParteDiario::where('id_lote', $lote->id_lote)
                ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('DATE_TRUNC(\'month\', fecha) as mes')
                ->selectRaw('SUM(COALESCE(costo_total_dia, 0)) as costo_total')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $labels = [];
            $serieProduccion = [];
            $serieCostos = [];
            $cursor = $fechaDesde->copy()->startOfMonth();
            $end = $fechaHasta->copy()->startOfMonth();
            while ($cursor->lessThanOrEqualTo($end)) {
                $key = $cursor->toDateTimeString();
                $labels[] = $cursor->format('M Y');
                $prod = $produccionMensual->firstWhere('mes', $key);
                $cost = $costosMensual->firstWhere('mes', $key);
                $serieProduccion[] = $prod ? round((float) $prod->toneladas, 2) : 0;
                $serieCostos[] = $cost ? round((float) $cost->costo_total, 2) : 0;
                $cursor->addMonth();
            }

            $chart_costo_url = $this->buildChartImage($labels, $serieProduccion, $serieCostos);

            $superficie = (float) ($lote->superficie ?? 0);
            $rendimiento = $superficie > 0 ? $produccionToneladas / $superficie : 0;

            $costosRaw = ParteDiario::where('id_lote', $lote->id_lote)
                ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('SUM(COALESCE(costo_insumos,0)) as insumos')
                ->selectRaw('SUM(COALESCE(costo_maquinaria,0)) as maquinaria')
                ->selectRaw('SUM(COALESCE(costo_mano_obra,0)) as mano_obra')
                ->selectRaw('SUM(COALESCE(costo_total_dia,0)) as total')
                ->first();

            $ingresosPeriodo = (float) (DB::table('venta_cargas as vc')
                ->join('cargas as c', 'vc.id_carga', '=', 'c.id_carga')
                ->where('c.id_lote', $lote->id_lote)
                ->whereBetween('c.fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('SUM(COALESCE(vc.subtotal, vc.precio_unitario * vc.peso_toneladas)) as monto_total')
                ->value('monto_total') ?? 0);

            $gastosPeriodo = (float) (ParteDiario::where('id_lote', $lote->id_lote)
                ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('SUM(COALESCE(costo_total_dia,0)) as total')
                ->value('total') ?? 0);

            $empleadosIds = $lote->empleados()->pluck('empleados.id_empleado');
            $liquidacionesPeriodo = 0.0;
            if ($empleadosIds->isNotEmpty()) {
                $liquidacionesPeriodo = (float) (Recibo::whereIn('id_empleado', $empleadosIds)
                    ->whereBetween('fecha_emision', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                    ->sum('monto') ?? 0);
            }
            $gastosPeriodo += $liquidacionesPeriodo;

            $indicadores = [
                'precio_promedio' => $this->statsService->getPrecioPromedioVenta(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString()
                ),
                'costo_promedio' => $this->statsService->getCostoPromedioPorTn(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString(),
                    true,
                    false
                ),
                'punto_equilibrio' => $this->statsService->getPuntoEquilibrioDiario(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString(),
                    true,
                    false
                ),
            ];
            $indicadores['rentabilidad'] = $indicadores['precio_promedio'] - $indicadores['costo_promedio'];

            $data = array_merge($base, [
                'lote' => $lote,
                'produccion_toneladas' => round($produccionToneladas, 2),
                'rendimiento' => round($rendimiento, 2),
                'chart_costo_url' => $chart_costo_url,
                'ingresos_periodo' => round($ingresosPeriodo, 2),
                'gastos_periodo' => round($gastosPeriodo, 2),
                'liquidaciones_periodo' => round($liquidacionesPeriodo, 2),
                'costos_directos' => [
                    'insumos' => round((float) ($costosRaw->insumos ?? 0), 2),
                    'maquinaria' => round((float) ($costosRaw->maquinaria ?? 0), 2),
                    'mano_obra' => round((float) ($costosRaw->mano_obra ?? 0), 2),
                    'total' => round((float) ($costosRaw->total ?? 0), 2),
                ],
                'indicadores' => $indicadores,
            ]);
        } else {
            $lotes = Lote::where('estado', 'activo')->get();

            $toneladasPorLote = Carga::selectRaw('id_lote, SUM(peso_neto / 1000.0) as toneladas')
                ->whereIn('id_lote', $lotes->pluck('id_lote'))
                ->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->groupBy('id_lote')
                ->get()
                ->keyBy('id_lote');

            $lotes_estadisticas = $lotes->map(function (Lote $lote) use ($toneladasPorLote, $fechaDesde, $fechaHasta) {
                $precio_promedio = $this->statsService->getPrecioPromedioVenta(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString()
                );
                $costo_promedio = $this->statsService->getCostoPromedioPorTn(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString(),
                    true,
                    false
                );
                $punto_equilibrio = $this->statsService->getPuntoEquilibrioDiario(
                    $lote,
                    $fechaDesde->toDateString(),
                    $fechaHasta->toDateString(),
                    true,
                    false
                );
                $produccion = (float) ($toneladasPorLote[$lote->id_lote]->toneladas ?? 0);

                return [
                    'id' => $lote->id_lote,
                    'nombre' => $lote->ubicacion ?? $lote->propietario ?? 'Lote ' . $lote->id_lote,
                    'hectareas' => $lote->superficie ?? 0,
                    'produccion' => round($produccion, 2),
                    'precio_promedio' => $precio_promedio,
                    'costo_promedio' => $costo_promedio,
                    'punto_equilibrio' => $punto_equilibrio,
                    'rentabilidad' => $precio_promedio - $costo_promedio,
                ];
            });

            $estadisticas_globales = $this->calcularEstadisticasGlobales($lotes, $fechaDesde, $fechaHasta);

            $superficie_total = (float) $lotes->sum('superficie');

            $produccion_total = (float) Carga::whereIn('id_lote', $lotes->pluck('id_lote'))
                ->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->sum(DB::raw('peso_neto / 1000.0'));

            $rendimiento_promedio = $superficie_total > 0 ? $produccion_total / $superficie_total : 0;

            $produccionMensualGlobal = Carga::whereIn('id_lote', $lotes->pluck('id_lote'))
                ->whereBetween('fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('DATE_TRUNC(\'month\', fecha_carga) as mes')
                ->selectRaw('SUM(peso_neto / 1000.0) as toneladas')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $costosMensualGlobal = ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
                ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                ->selectRaw('DATE_TRUNC(\'month\', fecha) as mes')
                ->selectRaw('SUM(COALESCE(costo_total_dia, 0)) as costo_total')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $labelsGlobal = [];
            $serieProduccionGlobal = [];
            $serieCostosGlobal = [];
            $cursorGlobal = $fechaDesde->copy()->startOfMonth();
            $endGlobal = $fechaHasta->copy()->startOfMonth();
            while ($cursorGlobal->lessThanOrEqualTo($endGlobal)) {
                $key = $cursorGlobal->toDateTimeString();
                $labelsGlobal[] = $cursorGlobal->format('M Y');
                $prod = $produccionMensualGlobal->firstWhere('mes', $key);
                $cost = $costosMensualGlobal->firstWhere('mes', $key);
                $serieProduccionGlobal[] = $prod ? round((float) $prod->toneladas, 2) : 0;
                $serieCostosGlobal[] = $cost ? round((float) $cost->costo_total, 2) : 0;
                $cursorGlobal->addMonth();
            }

            $chart_costo_url = $this->buildChartImage($labelsGlobal, $serieProduccionGlobal, $serieCostosGlobal);

            $ingresosPeriodo = 0.0;
            $gastosPeriodo = 0.0;
            $liquidacionesPeriodo = 0.0;
            if ($lotes->isNotEmpty()) {
                $ingresosPeriodo = (float) (DB::table('venta_cargas as vc')
                    ->join('cargas as c', 'vc.id_carga', '=', 'c.id_carga')
                    ->whereIn('c.id_lote', $lotes->pluck('id_lote'))
                    ->whereBetween('c.fecha_carga', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                    ->selectRaw('SUM(COALESCE(vc.subtotal, vc.precio_unitario * vc.peso_toneladas)) as monto_total')
                    ->value('monto_total') ?? 0);

                $gastosPeriodo = (float) (ParteDiario::whereIn('id_lote', $lotes->pluck('id_lote'))
                    ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                    ->selectRaw('SUM(COALESCE(costo_total_dia,0)) as total')
                    ->value('total') ?? 0);

                $empleadosIds = DB::table('lote_empleado')
                    ->whereIn('id_lote', $lotes->pluck('id_lote'))
                    ->pluck('id_empleado')
                    ->unique();

                if ($empleadosIds->isNotEmpty()) {
                    $liquidacionesPeriodo = (float) (Recibo::whereIn('id_empleado', $empleadosIds)
                        ->whereBetween('fecha_emision', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
                        ->sum('monto') ?? 0);
                }

                $gastosPeriodo += $liquidacionesPeriodo;
            }

            $top_lotes = $lotes->map(function (Lote $lote) use ($toneladasPorLote) {
                $toneladas = (float) ($toneladasPorLote[$lote->id_lote]->toneladas ?? 0);
                $superficie = (float) ($lote->superficie ?? 0);
                $rendimiento = $superficie > 0 ? $toneladas / $superficie : 0;

                return [
                    'id' => $lote->id_lote,
                    'nombre' => $lote->ubicacion ?? $lote->propietario ?? 'Lote ' . $lote->id_lote,
                    'superficie' => $superficie,
                    'toneladas' => round($toneladas, 2),
                    'rendimiento' => round($rendimiento, 2),
                ];
            })->sortByDesc('rendimiento')->values()->take(3);

            $distribucion_ubicaciones = $lotes->groupBy('ubicacion')->map(function ($grupo, $ubicacion) {
                return [
                    'ubicacion' => $ubicacion ?: 'Sin ubicación',
                    'cantidad' => $grupo->count(),
                    'superficie' => round((float) $grupo->sum('superficie'), 2),
                ];
            })->values();

            $data = array_merge($base, [
                'lotes' => $lotes,
                'lotes_estadisticas' => $lotes_estadisticas,
                'estadisticas_globales' => $estadisticas_globales,
                'produccion_total' => round($produccion_total, 2),
                'rendimiento_promedio' => round($rendimiento_promedio, 2),
                'superficie_total' => round($superficie_total, 2),
                'chart_costo_url' => $chart_costo_url,
                'ingresos_periodo' => round($ingresosPeriodo, 2),
                'gastos_periodo' => round($gastosPeriodo, 2),
                'liquidaciones_periodo' => round($liquidacionesPeriodo, 2),
                'top_lotes' => $top_lotes,
                'distribucion_ubicaciones' => $distribucion_ubicaciones,
            ]);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $html = view('reportes.pdf.estadisticas-forestales', $data)->render();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $canvas->page_script('
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 10;
            $text = "Página $PAGE_NUM de $PAGE_COUNT";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 32;
            $pdf->text($x, $y, $text, $font, $size, [127, 140, 141]);
        ');

        $filename = $tipo === 'lote'
            ? 'reporte-lote-' . ($data['lote']->id_lote ?? 'n-a') . '-' . Carbon::now()->format('Ymd_His') . '.pdf'
            : 'reporte-global-' . Carbon::now()->format('Ymd_His') . '.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function buildChartImage(array $labels, array $serieProduccion, array $serieCostos): ?string
    {
        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Producción (Tn)',
                        'data' => $serieProduccion,
                        'borderColor' => '#1a3d2f',
                        'backgroundColor' => 'rgba(26,61,47,0.15)',
                        'lineTension' => 0.3,
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Costos (USD)',
                        'data' => $serieCostos,
                        'borderColor' => '#7f8c8d',
                        'backgroundColor' => 'rgba(127,140,141,0.15)',
                        'lineTension' => 0.3,
                        'yAxisID' => 'y1',
                    ],
                ],
            ],
            'options' => [
                'legend' => ['display' => true],
                'scales' => [
                    'yAxes' => [
                        [
                            'id' => 'y',
                            'position' => 'left',
                            'scaleLabel' => ['display' => true, 'labelString' => 'Producción (Tn)'],
                        ],
                        [
                            'id' => 'y1',
                            'position' => 'right',
                            'gridLines' => ['drawOnChartArea' => false],
                            'scaleLabel' => ['display' => true, 'labelString' => 'Costos (USD)'],
                        ],
                    ],
                    'xAxes' => [
                        [
                            'ticks' => ['autoSkip' => true, 'maxTicksLimit' => 8],
                        ],
                    ],
                ],
            ],
        ];

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return null;
            }
            return 'data:image/png;base64,' . base64_encode($response->body());
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function calcularEstadisticasGlobales($lotes, Carbon $fechaDesde, Carbon $fechaHasta): array
    {
        if ($lotes->isEmpty()) {
            return [
                'precio_promedio' => 0,
                'costo_promedio' => 0,
                'punto_equilibrio' => 0,
                'total_lotes' => 0,
                'rentabilidad_promedio' => 0,
            ];
        }

        $lotesIds = $lotes->pluck('id_lote');
        $desde = $fechaDesde->toDateString();
        $hasta = $fechaHasta->toDateString();

        $totalToneladas = (float) Carga::whereIn('id_lote', $lotesIds)
            ->whereBetween('fecha_carga', [$desde, $hasta])
            ->sum(DB::raw('peso_neto / 1000.0'));

        $totalVentas = (float) (DB::table('venta_cargas as vc')
            ->join('cargas as c', 'vc.id_carga', '=', 'c.id_carga')
            ->whereIn('c.id_lote', $lotesIds)
            ->whereBetween('c.fecha_carga', [$desde, $hasta])
            ->selectRaw('SUM(COALESCE(vc.subtotal, vc.precio_unitario * vc.peso_toneladas)) as monto_total')
            ->value('monto_total') ?? 0);

        $costosRaw = ParteDiario::whereIn('id_lote', $lotesIds)
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('SUM(COALESCE(costo_insumos, 0)) as insumos')
            ->selectRaw('SUM(COALESCE(costo_maquinaria, 0)) as maquinaria')
            ->selectRaw('AVG(COALESCE(costo_insumos, 0) + COALESCE(costo_maquinaria, 0)) as costo_diario_base')
            ->first();

        $empleadosIds = DB::table('lote_empleado')
            ->whereIn('id_lote', $lotesIds)
            ->pluck('id_empleado')
            ->unique();

        $liquidacionesPeriodo = 0.0;
        if ($empleadosIds->isNotEmpty()) {
            $liquidacionesPeriodo = (float) (Recibo::whereIn('id_empleado', $empleadosIds)
                ->whereBetween('fecha_emision', [$desde, $hasta])
                ->sum('monto') ?? 0);
        }

        $totalCostos = (float) ($costosRaw->insumos ?? 0) + (float) ($costosRaw->maquinaria ?? 0) + $liquidacionesPeriodo;

        $precioPromedio = $totalToneladas > 0 ? round($totalVentas / $totalToneladas, 2) : 0;
        $costoPromedio = $totalToneladas > 0 ? round($totalCostos / $totalToneladas, 2) : 0;
        $rentabilidad = round($precioPromedio - $costoPromedio, 2);

        $fijos = (float) ($costosRaw->costo_diario_base ?? 0);
        $fijos = $fijos > 0 ? $fijos : 75;

        $denominador = $precioPromedio - $costoPromedio;
        $puntoEquilibrio = 75;
        if ($denominador > 0 && $precioPromedio > 0) {
            $punto = $fijos / $denominador;
            if ($punto > 0 && is_finite($punto)) {
                $puntoEquilibrio = round($punto, 2);
            }
        }

        return [
            'precio_promedio' => $precioPromedio,
            'costo_promedio' => $costoPromedio,
            'punto_equilibrio' => $puntoEquilibrio,
            'total_lotes' => $lotes->count(),
            'rentabilidad_promedio' => $rentabilidad,
        ];
    }
}
