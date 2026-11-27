<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\ConfiguracionService;
use Cron\CronExpression;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class ConfiguracionMantenimiento extends Component
{
    use MensajesErrorUsuario;

    /** @var array<string, string> */
    public const PRESETS_UMBRALES = [
        '30 6 * * *' => 'Todos los días a las 06:30',
        '0 8 * * *' => 'Todos los días a las 08:00',
        '0 */6 * * *' => 'Cada 6 horas',
        '0 */12 * * *' => 'Cada 12 horas',
    ];

    /** @var array<string, string> */
    public const PRESETS_PROGRAMADOS = [
        '0 */4 * * *' => 'Cada 4 horas',
        '0 */6 * * *' => 'Cada 6 horas',
        '0 8 * * *' => 'Todos los días a las 08:00',
        '0 8,16 * * *' => 'Todos los días a las 08:00 y 16:00',
    ];

    public $hora_recordatorio = '08:00';

    public $expresion_cron = '30 6 * * *';

    public $cron_programados = '0 */4 * * *';

    /** @var string Valor del preset seleccionado o 'custom'. */
    public $modoUmbrales = '30 6 * * *';

    /** @var string Valor del preset seleccionado o 'custom'. */
    public $modoProgramados = '0 */4 * * *';

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $this->hora_recordatorio = ConfiguracionService::obtener('mantenimiento_hora_recordatorio', '08:00');
        $this->expresion_cron = ConfiguracionService::obtener('mantenimiento_hora_verificacion_umbrales', '30 6 * * *');
        $this->cron_programados = ConfiguracionService::obtener('mantenimiento_cron_programados', '0 */4 * * *');

        $this->modoUmbrales = array_key_exists($this->expresion_cron, self::PRESETS_UMBRALES) ? $this->expresion_cron : 'custom';
        $this->modoProgramados = array_key_exists($this->cron_programados, self::PRESETS_PROGRAMADOS) ? $this->cron_programados : 'custom';
    }

    public function updatedModoUmbrales(string $valor): void
    {
        if ($valor !== 'custom') {
            $this->expresion_cron = $valor;
        }
    }

    public function updatedModoProgramados(string $valor): void
    {
        if ($valor !== 'custom') {
            $this->cron_programados = $valor;
        }
    }

    protected function rules(): array
    {
        return [
            'hora_recordatorio' => 'required|date_format:H:i',
            'expresion_cron' => ['required', 'string', $this->reglaCron()],
            'cron_programados' => ['required', 'string', $this->reglaCron()],
        ];
    }

    protected $messages = [
        'hora_recordatorio.required' => 'La hora del recordatorio es obligatoria.',
        'hora_recordatorio.date_format' => 'La hora debe tener el formato HH:MM (por ejemplo 08:00).',
        'expresion_cron.required' => 'La frecuencia de verificación es obligatoria.',
        'cron_programados.required' => 'La frecuencia de revisión de órdenes es obligatoria.',
    ];

    /**
     * Valida una expresión cron usando el mismo parser que el scheduler.
     */
    private function reglaCron(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            try {
                new CronExpression($value);
            } catch (\Throwable $e) {
                $fail('La expresión de frecuencia no es válida.');
            }
        };
    }

    public function guardarConfiguracion()
    {
        $this->authorize('configurar-mantenimiento');
        $this->validate();

        try {
            ConfiguracionService::establecer(
                'mantenimiento_hora_recordatorio',
                $this->hora_recordatorio,
                'Hora para enviar recordatorios de mantenimientos programados (formato HH:MM)',
                'time'
            );

            ConfiguracionService::establecer(
                'mantenimiento_hora_verificacion_umbrales',
                $this->expresion_cron,
                'Expresión cron para verificación de umbrales de toneladas',
                'cron'
            );

            ConfiguracionService::establecer(
                'mantenimiento_cron_programados',
                $this->cron_programados,
                'Expresión cron para revisión de órdenes programadas y vencidas',
                'cron'
            );

            session()->flash('message', 'Configuración de mantenimiento guardada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar la configuración'));
        }
    }

    public function ejecutarVerificacionUmbrales()
    {
        $this->authorize('configurar-mantenimiento');

        try {
            Artisan::call('mantenimiento:check-umbrales');
            session()->flash('message', 'Verificación de umbrales ejecutada correctamente.');
            session()->flash('command_output', Artisan::output());
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'ejecutar la verificación'));
        }
    }

    public function ejecutarVerificacionProgramados()
    {
        $this->authorize('configurar-mantenimiento');

        try {
            Artisan::call('mantenimiento:check-programados', ['--forzar' => true]);
            session()->flash('message', 'Revisión de órdenes programadas ejecutada correctamente.');
            session()->flash('command_output', Artisan::output());
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'ejecutar la revisión'));
        }
    }

    private function proxima(?string $cron): ?string
    {
        if (! $cron) {
            return null;
        }

        try {
            return (new CronExpression($cron))->getNextRunDate(now())->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Traduce una expresión cron a una frase legible.
     */
    private function describir(string $cron): string
    {
        $cron = trim((string) preg_replace('/\s+/', ' ', $cron));

        $presets = self::PRESETS_UMBRALES + self::PRESETS_PROGRAMADOS;

        if (isset($presets[$cron])) {
            return $presets[$cron];
        }

        $partes = explode(' ', $cron);

        if (count($partes) === 5) {
            [$minuto, $hora, $dia, $mes, $diaSemana] = $partes;

            if ($dia === '*' && $mes === '*' && $diaSemana === '*') {
                if ($hora === '*' && preg_match('#^\*/([0-9]+)$#', $minuto, $m)) {
                    return 'Cada '.$m[1].' minutos';
                }

                if ($minuto === '0' && preg_match('#^\*/([0-9]+)$#', $hora, $m)) {
                    return 'Cada '.$m[1].' horas';
                }

                if (ctype_digit($minuto) && ctype_digit($hora)) {
                    return 'Todos los días a las '.str_pad($hora, 2, '0', STR_PAD_LEFT).':'.str_pad($minuto, 2, '0', STR_PAD_LEFT);
                }
            }
        }

        return 'Personalizado ('.$cron.')';
    }

    public function render()
    {
        return view('livewire.configuracion-mantenimiento', [
            'presetsUmbrales' => self::PRESETS_UMBRALES,
            'presetsProgramados' => self::PRESETS_PROGRAMADOS,
            'descripcionUmbrales' => $this->describir((string) $this->expresion_cron),
            'descripcionProgramados' => $this->describir((string) $this->cron_programados),
            'proximaUmbrales' => $this->proxima($this->expresion_cron),
            'proximaProgramados' => $this->proxima($this->cron_programados),
        ]);
    }
}
