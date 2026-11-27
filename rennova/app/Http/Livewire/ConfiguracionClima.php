<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\ClimaConfiguracionService;
use Livewire\Component;

class ConfiguracionClima extends Component
{
    use MensajesErrorUsuario;

    public $umbral_lluvia_mm = 10;

    public $lluvia_madrugada_mm = 2;

    public $lluvia_diurna_mm = 5;

    public $saturacion_mm = 12;

    public $umbral_nubosidad_pct = 60;

    public $secado_viento_kmh = 15;

    public $secado_et0_mm = 4;

    public $aumento_maximo_pct = 25;

    public $forecast_dias = 7;

    public $mantenimiento_umbral_pct = 80;

    public function mount(): void
    {
        $this->cargar();
    }

    public function cargar(): void
    {
        foreach (app(ClimaConfiguracionService::class)->valores() as $clave => $valor) {
            $this->{$clave} = $valor;
        }
    }

    protected function rules(): array
    {
        return [
            'umbral_lluvia_mm' => 'required|numeric|min:0',
            'lluvia_madrugada_mm' => 'required|numeric|min:0',
            'lluvia_diurna_mm' => 'required|numeric|min:0',
            'saturacion_mm' => 'required|numeric|min:0',
            'umbral_nubosidad_pct' => 'required|numeric|min:0|max:100',
            'secado_viento_kmh' => 'required|numeric|min:0',
            'secado_et0_mm' => 'required|numeric|min:0',
            'aumento_maximo_pct' => 'required|numeric|min:0|max:100',
            'forecast_dias' => 'required|integer|min:1|max:16',
            'mantenimiento_umbral_pct' => 'required|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        '*.required' => 'Este parámetro es obligatorio.',
        '*.numeric' => 'Tiene que ser un número.',
        '*.min' => 'No puede ser negativo.',
        'umbral_nubosidad_pct.max' => 'La nubosidad se expresa en porcentaje (0 a 100).',
        'aumento_maximo_pct.max' => 'El aumento máximo se expresa en porcentaje (0 a 100).',
        'mantenimiento_umbral_pct.max' => 'El umbral de mantenimiento se expresa en porcentaje (0 a 100).',
        'forecast_dias.integer' => 'Los días de pronóstico tienen que ser un número entero.',
        'forecast_dias.min' => 'El pronóstico tiene que ser de al menos 1 día.',
        'forecast_dias.max' => 'El servicio climático permite hasta 16 días.',
    ];

    public function guardar(): void
    {
        $this->authorize('configurar-clima');
        $this->validate();

        try {
            app(ClimaConfiguracionService::class)->guardar($this->valoresFormulario());
            $this->cargar();
            session()->flash('message', 'Parámetros climáticos guardados correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar los parámetros climáticos'));
        }
    }

    public function restaurar(): void
    {
        $this->authorize('configurar-clima');

        try {
            app(ClimaConfiguracionService::class)->restaurar();
            $this->cargar();
            session()->flash('message', 'Parámetros restaurados a los valores por defecto.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'restaurar los parámetros climáticos'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function valoresFormulario(): array
    {
        return [
            'umbral_lluvia_mm' => $this->umbral_lluvia_mm,
            'lluvia_madrugada_mm' => $this->lluvia_madrugada_mm,
            'lluvia_diurna_mm' => $this->lluvia_diurna_mm,
            'saturacion_mm' => $this->saturacion_mm,
            'umbral_nubosidad_pct' => $this->umbral_nubosidad_pct,
            'secado_viento_kmh' => $this->secado_viento_kmh,
            'secado_et0_mm' => $this->secado_et0_mm,
            'aumento_maximo_pct' => $this->aumento_maximo_pct,
            'forecast_dias' => $this->forecast_dias,
            'mantenimiento_umbral_pct' => $this->mantenimiento_umbral_pct,
        ];
    }

    public function render()
    {
        return view('livewire.configuracion-clima');
    }
}
