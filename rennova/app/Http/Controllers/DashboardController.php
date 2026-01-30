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
            $climaData = $this->esLoteDemoLluvia($loteSeleccionado)
                ? $this->mockPronosticoLluvia($loteSeleccionado)
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

                // Determinar el tipo de recomendación
                $nivelUrgencia = $climaData['nivel_urgencia'] ?? 'BAJA';
                $tipoAlerta = match($nivelUrgencia) {
                    'ALTA' => 'ACELERAR',
                    'MEDIA' => 'ACELERAR',
                    'CRITICA' => 'SUSPENDER',
                    default => 'NORMAL',
                };

                $pronosticoData = [
                    'alerta' => $tipoAlerta,
                    'pronostico' => $pronosticoFormateado,
                    'analisisImpacto' => [
                        'diasPerdidos' => $diasPerdidos,
                        'deficitTn' => $climaData['datos_calculados']['volumen_riesgo'] ?? 0,
                        'accionPorcentaje' => round($climaData['datos_calculados']['aumento_necesario_pct'] ?? 0),
                    ],
                    'loteNombre' => $loteSeleccionado->nombre ?? $loteSeleccionado->propietario ?? ('Lote #' . $loteSeleccionado->id_lote),
                    'recomendacionDetallada' => $climaData['recomendacion'] ?? '',
                ];
            } elseif (isset($climaData['error'])) {
                $pronosticoError = $climaData['error'];
            }
        }

        return view('index', compact('lotes', 'loteSeleccionado', 'pronosticoData', 'pronosticoError'));
    }

    private function esLoteDemoLluvia(Lote $lote): bool
    {
        $nombre = strtolower((string) ($lote->nombre ?? $lote->propietario ?? ''));
        return str_contains($nombre, 'lluvia');
    }

    private function mockPronosticoLluvia(Lote $lote): array
    {
        $diasDetalle = [];
        $diasPerdidosPorClima = 0;
        
        foreach (range(0, 6) as $i) {
            $fecha = Carbon::now()->addDays($i);
            $estado = 'OPERATIVO';
            $razon = null;

            // PRIMERO: Verificar si es fin de semana (prioridad sobre clima)
            if ($fecha->isWeekend()) {
                $estado = 'INACTIVO';
                $razon = 'Fin de semana (no laboral)';
            }
            // SEGUNDO: Verificar condiciones climáticas solo si NO es fin de semana
            elseif ($i === 1) {
                $estado = 'INACTIVO';
                $razon = 'Lluvia diurna > 5mm (06-18)';
                $diasPerdidosPorClima++;
            }
            elseif ($i === 2) {
                $estado = 'INACTIVO';
                $razon = 'Saturación alta + nubosidad + poco viento';
                $diasPerdidosPorClima++;
            }
            elseif ($i === 4) {
                $estado = 'OPERATIVO_CONDICIONAL';
                $razon = 'Lluvia nocturna';
            }

            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'dia_semana' => $fecha->isoFormat('dddd'),
                'estado' => $estado,
                'razon' => $razon,
            ];
        }

        // Calcular meta diaria basada en histórico del lote (últimos 30 días)
        $promedioHistorico = \App\Models\ParteDiario::where('fecha', '>=', Carbon::now()->subDays(30))
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
        
        // Calcular porcentaje de aumento necesario
        $aumentoPct = $diasOperativos > 0 && $metaDiaria > 0 
            ? round(($deficitToneladas / ($diasOperativos * $metaDiaria)) * 100) 
            : 0;

        return [
            'success' => true,
            'nivel_urgencia' => $diasPerdidosPorClima >= 2 ? 'MEDIA' : 'BAJA',
            'recomendacion' => "Se detecta lluvia en la ventana operativa. Ajustar planificación y evaluar anticipación.",
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
