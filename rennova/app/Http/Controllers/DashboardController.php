<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\ClimaDecisionService;

class DashboardController extends Controller
{
    protected ClimaDecisionService $climaService;

    public function __construct(ClimaDecisionService $climaService)
    {
        $this->climaService = $climaService;
    }

    public function index()
    {
        // Solo lotes activos
        $lotes = Lote::where('estado', 'activo')->get();
        $loteSeleccionado = null;
        
        // Si hay un parámetro lote en la URL, buscar por id_lote
        if (request()->has('lote')) {
            $loteId = request('lote');
            $loteSeleccionado = Lote::where('id_lote', $loteId)->first();
        }
        
        // Si no se encontró o no se pasó parámetro, usar el primer lote
        if (!$loteSeleccionado && $lotes->count() > 0) {
            $loteSeleccionado = $lotes->sortByDesc('created_at')->first();
        }

        $pronosticoData = null;
        $pronosticoError = null;
        if ($loteSeleccionado) {
            $climaData = $this->climaService->analizarYRecomendar($loteSeleccionado);
            if ($climaData && !isset($climaData['error'])) {
                // Mapear pronostico (dias_detalle) al formato que espera el componente
                $pronosticoFormateado = [];
                $diasDetalle = $climaData['pronostico'] ?? $climaData['dias_detalle'] ?? [];
                
                if (is_array($diasDetalle)) {
                    foreach ($diasDetalle as $dia) {
                        $iconMap = [
                            'OPERATIVO' => 'sun',
                            'INACTIVO' => isset($dia['razon']) && strpos($dia['razon'], 'Lluvia') !== false ? 'storm' : 'cloud',
                        ];
                        $pronosticoFormateado[] = [
                            'label' => ucfirst(substr($dia['dia_semana'], 0, 3)) . ' (' . $dia['fecha_str'] . ')',
                            'estado' => $dia['estado'] === 'INACTIVO' ? 'NO OPERATIVO' : 'OPERATIVO',
                            'icono' => $iconMap[$dia['estado']] ?? 'sun',
                            'inactivo' => $dia['estado'] === 'INACTIVO',
                            'suelo' => $dia['razon'] ?? null,
                        ];
                    }
                }

                // Calcular días perdidos contando días inactivos
                $diasPerdidos = 0;
                foreach ($diasDetalle as $dia) {
                    if ($dia['estado'] === 'INACTIVO') {
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
                    'loteNombre' => $loteSeleccionado->propietario ?? ('Lote #' . $loteSeleccionado->id_lote),
                    'recomendacionDetallada' => $climaData['recomendacion'] ?? '',
                ];
            } elseif (isset($climaData['error'])) {
                $pronosticoError = $climaData['error'];
            }
        }

        return view('index', compact('lotes', 'loteSeleccionado', 'pronosticoData', 'pronosticoError'));
    }
}
