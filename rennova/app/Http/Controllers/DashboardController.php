<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected ClimaDecisionService $climaService;

    public function __construct(ClimaDecisionService $climaService)
    {
        $this->climaService = $climaService;
    }

    public function index()
    {
        // Solo lotes activos o en proceso
        $lotes = Lote::whereIn('estado', ['activo', 'en_proceso'])->get();
        $loteSeleccionado = null;
        
        // Si hay un parámetro lote válido en la URL, buscar por id_lote
        if (request()->filled('lote')) {
            $loteId = (int) request('lote');
            $loteSeleccionado = Lote::whereIn('estado', ['activo', 'en_proceso'])
                ->where('id_lote', $loteId)
                ->first();
        }
        
        // Si no se encontró o no se pasó parámetro, usar el último creado
        if (!$loteSeleccionado && $lotes->count() > 0) {
            $loteSeleccionado = $lotes->sortByDesc('created_at')->first();
        }

        $pronosticoData = null;
        $pronosticoError = null;
        if ($loteSeleccionado) {
            $escenarioDemo = $this->obtenerEscenarioDemo($loteSeleccionado);
            $climaData = $escenarioDemo
                ? $this->mockPronosticoEscenario($loteSeleccionado, $escenarioDemo)
                : $this->climaService->analizarYRecomendar($loteSeleccionado);
            if ($climaData && !isset($climaData['error'])) {
                // Mapear pronostico (dias_detalle) al formato que espera el componente
                $pronosticoFormateado = [];
                $diasDetalle = $climaData['pronostico'] ?? $climaData['dias_detalle'] ?? [];
                
                if (is_array($diasDetalle)) {
                    foreach ($diasDetalle as $dia) {
                        $iconMap = [
                            'OPERATIVO' => 'sun',
                            'OPERATIVO_CONDICIONAL' => 'cloud',
                            'INACTIVO' => isset($dia['razon']) && strpos($dia['razon'], 'Lluvia') !== false ? 'storm' : 'cloud',
                        ];
                        $estado = $dia['estado'] ?? 'OPERATIVO';
                        $pronosticoFormateado[] = [
                            'label' => ucfirst(substr($dia['dia_semana'], 0, 3)) . ' (' . $dia['fecha_str'] . ')',
                            'estado' => $estado === 'INACTIVO' ? 'NO OPERATIVO' : ($estado === 'OPERATIVO_CONDICIONAL' ? 'OPERATIVO COND.' : 'OPERATIVO'),
                            'icono' => $iconMap[$estado] ?? 'sun',
                            'inactivo' => $estado === 'INACTIVO',
                            'suelo' => $dia['razon'] ?? null,
                        ];
                    }
                }

                // Calcular días perdidos contando días inactivos (PERO NO FINES DE SEMANA)
                $diasPerdidos = 0;
                foreach ($diasDetalle as $dia) {
                    $estado = $dia['estado'] ?? null;
                    $razon = $dia['razon'] ?? '';
                    // Solo contar como perdido si es INACTIVO pero NO por fin de semana
                    if ($estado === 'INACTIVO' && stripos($razon, 'fin de semana') === false) {
                        $diasPerdidos++;
                    }
                }

                // Determinar acción recomendada y alerta
                $nivelUrgencia = $climaData['nivel_urgencia'] ?? 'BAJA';
                $accionRecomendada = $climaData['accion_recomendada'] ?? null;
                $aumentoNecesario = round($climaData['datos_calculados']['aumento_necesario_pct'] ?? 0);

                if (!$accionRecomendada) {
                    if ($nivelUrgencia === 'CRITICA') {
                        $accionRecomendada = 'SUSPENSION_JORNADA';
                    } elseif ($aumentoNecesario > 0) {
                        $accionRecomendada = 'AUMENTAR_PRODUCCION';
                    } else {
                        $accionRecomendada = 'OPERACION_NORMAL';
                    }
                }

                $tipoAlerta = match($accionRecomendada) {
                    'SUSPENSION_JORNADA' => 'SUSPENDER',
                    'MANTENIMIENTO_PREVENTIVO' => 'SUSPENDER',
                    'AUMENTAR_PRODUCCION' => 'ACELERAR',
                    default => 'NORMAL',
                };

                if (in_array($accionRecomendada, ['SUSPENSION_JORNADA', 'MANTENIMIENTO_PREVENTIVO', 'OPERACION_NORMAL'], true)) {
                    $aumentoNecesario = 0;
                }
                $aumentoNecesario = min(25, max(0, $aumentoNecesario));

                $pronosticoData = [
                    'alerta' => $tipoAlerta,
                    'pronostico' => $pronosticoFormateado,
                    'analisisImpacto' => [
                        'diasPerdidos' => $diasPerdidos,
                        'deficitTn' => $climaData['datos_calculados']['volumen_riesgo'] ?? 0,
                        'accionPorcentaje' => $aumentoNecesario,
                    ],
                    'loteNombre' => $loteSeleccionado->nombre ?? $loteSeleccionado->propietario ?? ('Lote #' . $loteSeleccionado->id_lote),
                    'recomendacionDetallada' => $climaData['recomendacion'] ?? '',
                    'estrategia' => $climaData['estrategia'] ?? null,
                    'accion_recomendada' => $accionRecomendada,
                    'nivel_urgencia' => $climaData['nivel_urgencia'] ?? null,
                ];
            } elseif (isset($climaData['error'])) {
                $pronosticoError = $climaData['error'];
            }
        }

        return view('index', compact('lotes', 'loteSeleccionado', 'pronosticoData', 'pronosticoError'));
    }

    private function obtenerEscenarioDemo(Lote $lote): ?string
    {
        if (!app()->environment(['local', 'testing'])) {
            return null;
        }
        if (!request()->boolean('demo')) {
            return null;
        }
        $override = (string) request('escenario', '');
        if ($override !== '') {
            $override = $this->normalizarTexto($override);
            $permitidos = [
                'normal',
                'lluvia_moderada',
                'lluvia_intensa',
                'reaccion_inmediata',
                'mantenimiento_preventivo',
                'suspension_total',
            ];
            if (in_array($override, $permitidos, true)) {
                return $override;
            }
        }
        $nombreFuente = trim((string) ($lote->nombre ?: $lote->propietario ?: ''));
        $nombre = $this->normalizarTexto($nombreFuente);
        if ($nombre === '') {
            return null;
        }

        if (str_contains($nombre, 'demo') || str_contains($nombre, 'simul') || str_contains($nombre, 'escenario') || str_contains($nombre, 'lluvia')) {
            if (str_contains($nombre, 'intensa')) {
                return 'lluvia_intensa';
            }
            if (str_contains($nombre, 'moderada') || str_contains($nombre, 'media')) {
                return 'lluvia_moderada';
            }
            if (str_contains($nombre, 'mantenimiento') || str_contains($nombre, 'preventivo')) {
                return 'mantenimiento_preventivo';
            }
            if (str_contains($nombre, 'reaccion') || str_contains($nombre, 'inmediata')) {
                return 'reaccion_inmediata';
            }
            if (str_contains($nombre, 'suspension') || str_contains($nombre, 'suspender')) {
                return 'suspension_total';
            }
            if (str_contains($nombre, 'normal') || str_contains($nombre, 'estable')) {
                return 'normal';
            }

            return 'lluvia_moderada';
        }

        return null;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = trim(mb_strtolower($texto));
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        return strtolower($texto);
    }

    private function mockPronosticoEscenario(Lote $lote, string $escenario): array
    {
        $diasDetalle = [];
        $diasPerdidosPorClima = 0;

        $patrones = [
            'normal' => [
                'nivel_urgencia' => 'BAJA',
                'recomendacion' => "✅ OPERACIÓN NORMAL\n\nNo se pronostican lluvias relevantes. Mantener ritmo de producción.",
                'estrategia' => 'NORMAL',
                'accion_recomendada' => 'OPERACION_NORMAL',
                'dias' => [],
            ],
            'lluvia_moderada' => [
                'nivel_urgencia' => 'MEDIA',
                'recomendacion' => "📋 PLANIFICACIÓN ESTRATÉGICA\n\nLluvias moderadas. Ajustar planificación y compensar en días operativos.",
                'estrategia' => 'ANTICIPACION_PLANIFICADA',
                'accion_recomendada' => 'AUMENTAR_PRODUCCION',
                'dias' => [
                    1 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia diurna > 6mm'],
                    3 => ['estado' => 'OPERATIVO_CONDICIONAL', 'razon' => 'Lluvia nocturna'],
                ],
            ],
            'lluvia_intensa' => [
                'nivel_urgencia' => 'ALTA',
                'recomendacion' => "🚨 ALERTA CLIMÁTICA\n\nLluvia intensa. Aplicar plan de máximo esfuerzo para mitigar el déficit.",
                'estrategia' => 'ANTICIPACION_MAXIMA',
                'accion_recomendada' => 'AUMENTAR_PRODUCCION',
                'dias' => [
                    1 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia diurna > 15mm'],
                    2 => ['estado' => 'INACTIVO', 'razon' => 'Saturación alta + nubosidad'],
                    3 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia persistente'],
                    5 => ['estado' => 'OPERATIVO_CONDICIONAL', 'razon' => 'Lluvia nocturna'],
                ],
            ],
            'reaccion_inmediata' => [
                'nivel_urgencia' => 'ALTA',
                'recomendacion' => "⚠️ REACCIÓN INMEDIATA\n\nLluvia hoy. Reorganizar recursos y priorizar cargas críticas.",
                'estrategia' => 'REACCION',
                'accion_recomendada' => 'MANTENIMIENTO_PREVENTIVO',
                'dias' => [
                    0 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia intensa inmediata'],
                    1 => ['estado' => 'INACTIVO', 'razon' => 'Saturación alta'],
                    2 => ['estado' => 'INACTIVO', 'razon' => 'Viento + lluvia'],
                    4 => ['estado' => 'OPERATIVO_CONDICIONAL', 'razon' => 'Ventana corta'],
                ],
            ],
            'mantenimiento_preventivo' => [
                'nivel_urgencia' => 'ALTA',
                'recomendacion' => "🔧 MANTENIMIENTO PREVENTIVO\n\nLluvia activa o inminente. Usar el tiempo no operativo para mantenimiento.",
                'estrategia' => 'REACCION',
                'accion_recomendada' => 'MANTENIMIENTO_PREVENTIVO',
                'dias' => [
                    0 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia persistente'],
                    1 => ['estado' => 'INACTIVO', 'razon' => 'Saturación alta'],
                    2 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia diurna > 10mm'],
                    3 => ['estado' => 'OPERATIVO_CONDICIONAL', 'razon' => 'Ventana corta'],
                ],
            ],
            'suspension_total' => [
                'nivel_urgencia' => 'CRITICA',
                'recomendacion' => "🛑 SUSPENSIÓN TEMPORAL\n\nCondiciones inviables. Suspender operaciones y reprogramar tareas.",
                'estrategia' => 'REACCION',
                'accion_recomendada' => 'SUSPENSION_JORNADA',
                'dias' => [
                    0 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    1 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    2 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    3 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    4 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    5 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                    6 => ['estado' => 'INACTIVO', 'razon' => 'Lluvia continua'],
                ],
            ],
        ];

        $config = $patrones[$escenario] ?? $patrones['lluvia_moderada'];
        $tz = env('APP_TIMEZONE', config('app.timezone', 'UTC'));
        date_default_timezone_set($tz);
        $now = Carbon::now($tz);
        $hoyEsFinde = $now->isWeekend();
        
        foreach (range(0, 6) as $i) {
            $fecha = $now->copy()->addDays($i);
            $estado = 'OPERATIVO';
            $razon = null;

            if (isset($config['dias'][$i])) {
                $estado = $config['dias'][$i]['estado'] ?? $estado;
                $razon = $config['dias'][$i]['razon'] ?? $razon;
            }

            // PRIMERO: Verificar si es fin de semana (prioridad sobre clima)
            if ($fecha->isWeekend()) {
                $estado = 'INACTIVO';
                $razon = 'Fin de semana (no laboral)';
            }
            // Contabilizar solo inactivos por clima (no fin de semana)
            elseif ($estado === 'INACTIVO') {
                $diasPerdidosPorClima++;
            }

            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'dia_semana' => $fecha->isoFormat('dddd'),
                'estado' => $estado,
                'razon' => $razon,
            ];
        }

        // Regla adicional: solo el día inmediato posterior a lluvia real (sin encadenar)
        $diasDetalleBase = $diasDetalle;
        foreach ($diasDetalleBase as $i => $dia) {
            if ($i === 0) {
                continue;
            }

            $ayer = $diasDetalleBase[$i - 1];
            $hoy = $diasDetalle[$i];
            $razonHoy = $this->normalizarTexto((string) ($hoy['razon'] ?? ''));
            $esFinDeSemanaHoy = $razonHoy !== '' && stripos($razonHoy, 'fin de semana') !== false;
            $razonAyer = $this->normalizarTexto((string) ($ayer['razon'] ?? ''));
            $ayerFueLluvia = $ayer['estado'] === 'INACTIVO'
                && ($razonAyer === '' || stripos($razonAyer, 'fin de semana') === false)
                && stripos($razonAyer, 'suelo humedo') === false;

            if ($ayerFueLluvia && !$esFinDeSemanaHoy && in_array($hoy['estado'], ['OPERATIVO', 'OPERATIVO_CONDICIONAL'], true)) {
                $diasDetalle[$i]['estado'] = 'INACTIVO';
                $diasDetalle[$i]['razon'] = 'Suelo húmedo post-lluvia';
                $diasPerdidosPorClima++;
            }
        }

        // Calcular meta diaria basada en histórico del lote (últimos 30 días)
        $promedioHistorico = \App\Models\ParteDiario::where('fecha', '>=', $now->copy()->subDays(30))
            ->whereHas('cargas')
            ->whereHas('lote', function($query) use ($lote) {
                $query->where('id_lote', $lote->id_lote);
            })
            ->withSum('cargas', 'peso_neto')
            ->get()
            ->avg('cargas_sum_peso_neto');

        // Convertir de kilos a toneladas (default 50 si no hay datos)
        $metaDiaria = $promedioHistorico ? round($promedioHistorico / 1000.0, 2) : 50;
        
        // Calcular déficit dinámicamente
        $deficitToneladas = $diasPerdidosPorClima * $metaDiaria;
        
        // Contar días operativos (excluyendo fines de semana e inactivos por clima)
        $diasOperativos = 0;
        foreach ($diasDetalle as $dia) {
            $estado = $dia['estado'] ?? 'OPERATIVO';
            $razon = $dia['razon'] ?? '';
            if ($estado === 'OPERATIVO' || $estado === 'OPERATIVO_CONDICIONAL') {
                $diasOperativos++;
            }
        }
        
        // Calcular porcentaje de aumento necesario (máximo 25%)
        $aumentoPct = $diasOperativos > 0 && $metaDiaria > 0
            ? round(($deficitToneladas / ($diasOperativos * $metaDiaria)) * 100)
            : 0;
        $aumentoPct = min(25, max(0, $aumentoPct));

        // Ajustar recomendación demo para evitar confusiones (déficit total vs por día)
        if ($deficitToneladas > 0 && $diasOperativos > 0) {
            $volumenRecuperable = round($metaDiaria * ($aumentoPct / 100) * $diasOperativos, 2);
            $deficitResidual = max(0, round($deficitToneladas - $volumenRecuperable, 2));
            $recoBase = $config['recomendacion'] ?? '';
            $recoExtra = "Déficit total estimado: {$deficitToneladas} tn\n"
                . "Días operativos disponibles: {$diasOperativos}\n"
                . "Aumento máximo por día: {$aumentoPct}% (meta: {$metaDiaria} tn/día)\n"
                . "Volumen recuperable: {$volumenRecuperable} tn\n"
                . "Déficit residual: {$deficitResidual} tn";
            $config['recomendacion'] = trim($recoBase . "\n\n" . $recoExtra);
        }

        // Si hoy es fin de semana, no sugerir aumento de producción en demo
        if ($hoyEsFinde) {
            if (($config['accion_recomendada'] ?? null) === 'AUMENTAR_PRODUCCION') {
                $config['accion_recomendada'] = 'OPERACION_NORMAL';
                $aumentoPct = 0;
            }
            if (!empty($config['recomendacion'])) {
                $config['recomendacion'] = trim($config['recomendacion'] . "\n\nFin de semana: no se recomienda aumentar producción hoy.");
            }
            if (($config['nivel_urgencia'] ?? null) === 'ALTA' && ($config['accion_recomendada'] ?? null) === 'OPERACION_NORMAL') {
                $config['nivel_urgencia'] = 'BAJA';
            }
        }

        // Si no hay días operativos, forzar suspensión
        if ($diasOperativos === 0 && (($config['accion_recomendada'] ?? null) !== 'MANTENIMIENTO_PREVENTIVO')) {
            $config = $patrones['suspension_total'];
            $aumentoPct = 0;
        }

        if ($diasPerdidosPorClima === 0) {
            $config = $patrones['normal'];
        }

        return [
            'success' => true,
            'nivel_urgencia' => $config['nivel_urgencia'] ?? ($diasPerdidosPorClima >= 2 ? 'MEDIA' : 'BAJA'),
            'recomendacion' => $config['recomendacion'] ?? 'Se detecta lluvia en la ventana operativa. Ajustar planificación y evaluar anticipación.',
            'estrategia' => $config['estrategia'] ?? null,
            'accion_recomendada' => $config['accion_recomendada'] ?? null,
            'dias_detalle' => $diasDetalle,
            'datos_calculados' => [
                'volumen_riesgo' => $deficitToneladas,
                'aumento_necesario_pct' => $aumentoPct,
                'meta_diaria' => $metaDiaria,
                'dias_perdidos' => $diasPerdidosPorClima,
                'dias_operativos_disponibles' => $diasOperativos,
            ],
        ];
    }
}
