<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ConfiguracionSistema;
use Illuminate\Support\Facades\Artisan;

class ConfiguracionMantenimiento extends Component
{
    public $hora_recordatorio = '08:00';
    public $expresion_cron = '*/30 * * * *';

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $this->hora_recordatorio = ConfiguracionSistema::obtener('mantenimiento_hora_recordatorio', '08:00');
        $this->expresion_cron = ConfiguracionSistema::obtener('mantenimiento_hora_verificacion_umbrales', '*/30 * * * *');
    }

    public function guardarConfiguracion()
    {
        $this->validate([
            'hora_recordatorio' => 'required|date_format:H:i',
            'expresion_cron' => 'required|string',
        ], [
            'hora_recordatorio.required' => 'La hora de recordatorio es obligatoria',
            'hora_recordatorio.date_format' => 'El formato debe ser HH:MM (ej: 08:00)',
            'expresion_cron.required' => 'La expresión cron es obligatoria',
        ]);

        try {
            ConfiguracionSistema::establecer(
                'mantenimiento_hora_recordatorio',
                $this->hora_recordatorio,
                'Hora para enviar recordatorios de mantenimientos programados (formato HH:MM)',
                'time'
            );

            ConfiguracionSistema::establecer(
                'mantenimiento_hora_verificacion_umbrales',
                $this->expresion_cron,
                'Expresión cron para verificación de umbrales',
                'cron'
            );

            session()->flash('message', 'Configuración de mantenimiento guardada correctamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar configuración: ' . $e->getMessage());
        }
    }

    public function ejecutarVerificacionUmbrales()
    {
        try {
            Artisan::call('mantenimiento:check-umbrales');
            $output = Artisan::output();
            session()->flash('message', 'Verificación de umbrales ejecutada correctamente.');
            session()->flash('command_output', $output);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al ejecutar verificación: ' . $e->getMessage());
        }
    }

    public function ejecutarVerificacionProgramados()
    {
        try {
            Artisan::call('mantenimiento:check-programados');
            $output = Artisan::output();
            session()->flash('message', 'Verificación de mantenimientos programados ejecutada correctamente.');
            session()->flash('command_output', $output);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al ejecutar verificación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.configuracion-mantenimiento');
    }
}
