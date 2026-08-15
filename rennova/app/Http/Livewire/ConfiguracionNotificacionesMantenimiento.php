<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Usuario;
use App\Services\NotificacionService;
use Livewire\Component;

class ConfiguracionNotificacionesMantenimiento extends Component
{
    use MensajesErrorUsuario;

    public $usuariosUmbral = [];

    public $usuariosRecordatorio = [];

    public $usuariosStock = [];

    public $usuarios;

    private NotificacionService $notificacionService;

    public function mount(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
        $this->usuarios = Usuario::orderBy('nombre')->get();
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $config = $this->notificacionService->cargarConfiguracionMantenimiento();
        $this->usuariosUmbral = $config['umbral'];
        $this->usuariosRecordatorio = $config['recordatorio'];
        $this->usuariosStock = $config['stock'];
    }

    public function guardarConfiguracion()
    {
        try {
            $this->notificacionService->guardarConfiguracionMantenimiento(
                $this->usuariosUmbral ?? [],
                $this->usuariosRecordatorio ?? [],
                $this->usuariosStock ?? []
            );
            session()->flash('message', 'Configuración de notificaciones guardada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar la configuración'));
        }
    }

    public function render()
    {
        return view('livewire.configuracion-notificaciones-mantenimiento');
    }
}
