<?php

namespace App\Http\Livewire;

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Carbon\Carbon;
use Livewire\Component;

class SelectorLote extends Component
{
    public $loteSeleccionado = null;
    public $lotes = [];
    public $pronosticoData = null;
    protected $climaService;

    public function mount(ClimaDecisionService $climaService)
    {
        $this->climaService = $climaService;
        // Cargar solo lotes activos
        $this->lotes = Lote::whereIn('estado', ['activo', 'en_proceso'])
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
        
        // Cargar el primer lote por defecto
        if (count($this->lotes) > 0) {
            $this->loteSeleccionado = $this->lotes[0]['id_lote'];
            $this->actualizarPronostico();
        }
    }

    public function updatedLoteSeleccionado()
    {
        $this->actualizarPronostico();
    }

    public function actualizarPronostico()
    {
        if (!$this->loteSeleccionado) {
            $this->pronosticoData = null;
            return;
        }

        $lote = Lote::where('id_lote', $this->loteSeleccionado)->first();
        
        if (!$lote) {
            $this->pronosticoData = null;
            return;
        }

        // Verificar que tiene coordenadas
        if (!$lote->latitud || !$lote->longitud) {
            $this->pronosticoData = null;
            return;
        }

        // Obtener pronóstico (mock si es lote demo con lluvia)
        if ($this->esLoteDemoLluvia($lote)) {
            $climaData = $this->mockPronosticoLluvia($lote);
        } else {
            $climaData = $this->climaService->analizarYRecomendar($lote);
        }
        
        if ($climaData && !isset($climaData['error'])) {
            // Mapear pronostico (dias_detalle) al formato del componente
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

            $diasPerdidos = 0;
            foreach ($diasDetalle as $dia) {
                if (($dia['estado'] ?? null) === 'INACTIVO') {
                    $diasPerdidos++;
                }
            }

            $nivelUrgencia = $climaData['nivel_urgencia'] ?? 'BAJA';
            $tipoAlerta = match($nivelUrgencia) {
                'ALTA' => 'ACELERAR',
                'MEDIA' => 'ACELERAR',
                'CRITICA' => 'SUSPENDER',
                default => 'NORMAL',
            };

            $this->pronosticoData = [
                'alerta' => $tipoAlerta,
                'pronostico' => $pronosticoFormateado,
                'analisisImpacto' => [
                    'diasPerdidos' => $diasPerdidos,
                    'deficitTn' => $climaData['datos_calculados']['volumen_riesgo'] ?? 0,
                    'accionPorcentaje' => round($climaData['datos_calculados']['aumento_necesario_pct'] ?? 0),
                ],
                'loteNombre' => $lote->nombre ?? $lote->propietario ?? ('Lote #' . $lote->id_lote),
                'recomendacionDetallada' => $climaData['recomendacion'] ?? '',
            ];
        } else {
            $this->pronosticoData = null;
        }
    }

    public function render()
    {
        return view('livewire.selector-lote');
    }

    private function esLoteDemoLluvia(Lote $lote): bool
    {
        $nombre = strtolower((string) ($lote->nombre ?? $lote->propietario ?? ''));
        return str_contains($nombre, 'lluvia');
    }

    private function mockPronosticoLluvia(Lote $lote): array
    {
        $diasDetalle = [];
        foreach (range(0, 6) as $i) {
            $fecha = Carbon::now()->addDays($i);
            $estado = 'OPERATIVO';
            $razon = null;

            if ($i === 1) {
                $estado = 'INACTIVO';
                $razon = 'Lluvia diurna > 5mm (06-18)';
            }
            if ($i === 2) {
                $estado = 'INACTIVO';
                $razon = 'Saturación alta + nubosidad + poco viento';
            }
            if ($i === 4) {
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

        return [
            'success' => true,
            'nivel_urgencia' => 'MEDIA',
            'recomendacion' => "Se detecta lluvia en la ventana operativa. Ajustar planificación y evaluar anticipación.",
            'dias_detalle' => $diasDetalle,
            'datos_calculados' => [
                'volumen_riesgo' => 120,
                'aumento_necesario_pct' => 18,
            ],
        ];
    }
}
