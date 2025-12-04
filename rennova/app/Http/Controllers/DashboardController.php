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
        $lotes = Lote::all();
        $loteSeleccionado = null;
        if (request()->has('lote')) {
            $loteSeleccionado = $lotes->where('id', request('lote'))->first();
        }
        if (!$loteSeleccionado) {
            $loteSeleccionado = $lotes->sortByDesc('created_at')->first();
        }

        $pronosticoData = null;
        if ($loteSeleccionado) {
            $climaData = $this->climaService->analizarYRecomendar($loteSeleccionado);
            if ($climaData) {
                $pronosticoData = [
                    'alerta' => $climaData['recomendacion'] ?? 'NORMAL',
                    'pronostico' => $climaData['pronostico'] ?? [],
                    'analisisImpacto' => [
                        'diasPerdidos' => $climaData['dias_perdidos'] ?? 0,
                        'deficitTn' => $climaData['deficit_tn'] ?? 0,
                        'accionPorcentaje' => $climaData['accion_porcentaje'] ?? 0,
                    ],
                ];
            }
        }

        return view('index', compact('lotes', 'loteSeleccionado', 'pronosticoData'));
    }
}
