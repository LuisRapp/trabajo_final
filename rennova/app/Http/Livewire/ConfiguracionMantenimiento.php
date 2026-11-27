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

    public $hora_recordatorio = '08:00';

    public $expresion_cron = '30 6 * * *';

    public $cron_programados = '0 */4 * * *';

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $this->hora_recordatorio = ConfiguracionService::obtener('mantenimiento_hora_recordatorio', '08:00');
        $this->expresion_cron = ConfiguracionService::obtener('mantenimiento_hora_verificacion_umbrales', '30 6 * * *');
        $this->cron_programados = ConfiguracionService::obtener('mantenimiento_cron_programados', '0 */4 * * *');
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
                $fail('La expresión cron no es válida. Formato: minuto hora día mes día_semana.');
            }
        };
    }

    public function setCronUmbrales(string $cron): void
    {
        $this->expresion_cron = $cron;
    }

    public function setCronProgramados(string $cron): void
    {
        $this->cron_programados = $cron;
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

    public function render()
    {
        return view('livewire.configuracion-mantenimiento', [
            'proximaUmbrales' => $this->proxima($this->expresion_cron),
            'proximaProgramados' => $this->proxima($this->cron_programados),
        ]);
    }
}
