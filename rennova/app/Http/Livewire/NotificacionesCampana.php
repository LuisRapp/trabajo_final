<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NotificacionSistema;
use Illuminate\Support\Facades\Auth;

class NotificacionesCampana extends Component
{
    public $notificaciones = [];
    public $cantidadNoLeidas = 0;
    public $mostrarDropdown = false;
    
    // Modal de programación
    public $mostrarModalProgramacion = false;
    public $notificacionSeleccionada = null;
    public $mantenimientoSeleccionado = null;
    public $fechaProgramada = '';
    public $fechaMinima = '';
    public $fechaMaxima = '';

    protected $listeners = ['notificacionCreada' => 'cargarNotificaciones'];

    public function mount()
    {
        $this->cargarNotificaciones();
    }

    public function cargarNotificaciones()
    {
        $this->notificaciones = NotificacionSistema::where('user_id', Auth::id())
            ->noLeidas()
            ->vigentes()
            ->with('mantenimiento.maquinaria')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->cantidadNoLeidas = NotificacionSistema::where('user_id', Auth::id())
            ->noLeidas()
            ->vigentes()
            ->count();
    }

    public function marcarComoLeida($notificacionId)
    {
        $notificacion = NotificacionSistema::where('id', $notificacionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notificacion) {
            $notificacion->marcarComoLeida();
            $this->cargarNotificaciones();
        }
    }

    public function marcarTodasComoLeidas()
    {
        NotificacionSistema::where('user_id', Auth::id())
            ->noLeidas()
            ->update([
                'leida' => true,
                'leida_at' => now(),
            ]);

        $this->cargarNotificaciones();
    }

    public function irANotificacion($notificacionId)
    {
        $notificacion = NotificacionSistema::find($notificacionId);
        
        if ($notificacion && $notificacion->mantenimiento_id) {
            // Redirigir a la vista de programar mantenimiento
            return redirect()->route('programar-mantenimiento', ['notificacionId' => $notificacionId]);
        } else {
            // Si no tiene mantenimiento asociado, ir a la lista de notificaciones
            $this->marcarComoLeida($notificacionId);
            return redirect()->route('notificaciones.index');
        }
    }
    
    public function abrirModalProgramacion($notificacionId)
    {
        $this->notificacionSeleccionada = NotificacionSistema::with('mantenimiento.maquinaria')
            ->find($notificacionId);
        
        if ($this->notificacionSeleccionada && $this->notificacionSeleccionada->mantenimiento) {
            $this->mantenimientoSeleccionado = $this->notificacionSeleccionada->mantenimiento;
            
            // Calcular fechas mínima y máxima (7 días desde la creación de la notificación)
            $fechaNotificacion = $this->notificacionSeleccionada->created_at;
            $this->fechaMinima = $fechaNotificacion->format('Y-m-d');
            $this->fechaMaxima = $fechaNotificacion->addDays(7)->format('Y-m-d');
            
            // Establecer fecha por defecto (mañana o fecha mínima si ya pasó)
            $manana = now()->addDay();
            $fechaMin = \Carbon\Carbon::parse($this->fechaMinima);
            $this->fechaProgramada = $manana->gte($fechaMin) ? $manana->format('Y-m-d') : $this->fechaMinima;
            
            $this->mostrarModalProgramacion = true;
        }
    }
    
    public function cerrarModalProgramacion()
    {
        $this->mostrarModalProgramacion = false;
        $this->notificacionSeleccionada = null;
        $this->mantenimientoSeleccionado = null;
        $this->fechaProgramada = '';
        $this->resetErrorBag();
    }
    
    public function programarMantenimiento()
    {
        $this->validate([
            'fechaProgramada' => [
                'required',
                'date',
                'after_or_equal:' . $this->fechaMinima,
                'before_or_equal:' . $this->fechaMaxima,
            ],
        ], [
            'fechaProgramada.required' => 'La fecha programada es obligatoria.',
            'fechaProgramada.date' => 'La fecha programada debe ser una fecha válida.',
            'fechaProgramada.after_or_equal' => 'La fecha debe ser posterior o igual al ' . \Carbon\Carbon::parse($this->fechaMinima)->format('d/m/Y'),
            'fechaProgramada.before_or_equal' => 'La fecha debe ser anterior o igual al ' . \Carbon\Carbon::parse($this->fechaMaxima)->format('d/m/Y'),
        ]);
        
        // Actualizar el mantenimiento
        $this->mantenimientoSeleccionado->update([
            'fecha_programada' => $this->fechaProgramada,
            'estado' => 'programado',
        ]);
        
        // Marcar notificación como leída y accionada
        $this->notificacionSeleccionada->update([
            'leida' => true,
            'leida_at' => now(),
            'accionada' => true,
            'accionada_at' => now(),
        ]);
        
        // Recargar notificaciones
        $this->cargarNotificaciones();
        
        // Cerrar modal
        $this->cerrarModalProgramacion();
        
        // Mensaje de éxito
        session()->flash('success', 'Mantenimiento programado exitosamente para el ' . \Carbon\Carbon::parse($this->fechaProgramada)->format('d/m/Y'));
    }

    public function toggleDropdown()
    {
        $this->mostrarDropdown = !$this->mostrarDropdown;
    }

    public function render()
    {
        return view('livewire.notificaciones-campana');
    }
}
