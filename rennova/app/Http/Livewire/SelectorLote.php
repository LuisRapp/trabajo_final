<?php

namespace App\Http\Livewire;

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Livewire\Component;

class SelectorLote extends Component
{
    public $loteSeleccionado = null;
    public $lotes = [];
    public $pronosticoData = null;
    protected $climaService;

    public function __construct()
    {
        $this->climaService = app(ClimaDecisionService::class);
    }

    public function mount()
    {
        // Cargar solo lotes activos
        $this->lotes = Lote::where('estado', 'activo')->get()->toArray();
        
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

        // Obtener pronóstico
        $climaData = $this->climaService->analizarYRecomendar($lote);
        
        if ($climaData && !isset($climaData['error'])) {
            $this->pronosticoData = [
                'alerta' => $climaData['recomendacion'] ?? 'NORMAL',
                'pronostico' => $climaData['pronostico'] ?? [],
                'analisisImpacto' => [
                    'diasPerdidos' => $climaData['dias_perdidos'] ?? 0,
                    'deficitTn' => $climaData['deficit_tn'] ?? 0,
                    'accionPorcentaje' => $climaData['accion_porcentaje'] ?? 0,
                ],
                'loteNombre' => $lote->nombre ?? ('Lote #' . $lote->id_lote),
            ];
        } else {
            $this->pronosticoData = null;
        }
    }

    public function render()
    {
        return view('livewire.selector-lote');
    }
}
