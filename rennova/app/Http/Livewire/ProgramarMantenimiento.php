<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NotificacionSistema;
use App\Models\Mantenimiento;
use Illuminate\Support\Facades\Auth;

class ProgramarMantenimiento extends Component
{
    public $notificacionId;
    public $notificacion;
    public $mantenimiento;
    public $fechaProgramada;
    public $fechaMinima;
    public $fechaMaxima;

    public function mount($notificacionId)
    {
        $this->notificacionId = $notificacionId;
        
        // Cargar notificación con mantenimiento
        $this->notificacion = NotificacionSistema::with([
                'mantenimiento.maquinaria', 
                'mantenimiento.tipoMantenimiento'
            ])
            ->where('id', $notificacionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Verificar que tiene mantenimiento asociado
        if (!$this->notificacion->mantenimiento_id || !$this->notificacion->mantenimiento) {
            session()->flash('error', 'Esta notificación no tiene un mantenimiento asociado.');
            return redirect()->route('notificaciones.index');
        }
        
        $this->mantenimiento = $this->notificacion->mantenimiento;
        
        // Calcular fechas mínima y máxima (7 días desde la creación de la notificación)
        $fechaNotificacion = $this->notificacion->created_at;
        $this->fechaMinima = $fechaNotificacion->format('Y-m-d');
        $this->fechaMaxima = $fechaNotificacion->copy()->addDays(7)->format('Y-m-d');
        
        // Establecer fecha por defecto
        $manana = now()->addDay();
        $fechaMin = \Carbon\Carbon::parse($this->fechaMinima);
        $this->fechaProgramada = $manana->gte($fechaMin) ? $manana->format('Y-m-d') : $this->fechaMinima;
    }

    public function guardarFecha()
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
        $this->mantenimiento->update([
            'fecha_inicio' => $this->fechaProgramada,
            'fecha_programada' => $this->fechaProgramada,
            'estado' => 'programado',
        ]);
        
        // Marcar notificación como leída y accionada
        $this->notificacion->update([
            'leida' => true,
            'leida_at' => now(),
            'accionada' => true,
            'accionada_at' => now(),
        ]);
        
        // Mensaje de éxito y redirección
        session()->flash('success', 'Mantenimiento programado exitosamente para el ' . \Carbon\Carbon::parse($this->fechaProgramada)->format('d/m/Y'));
        
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.programar-mantenimiento', [
            'notificacion' => $this->notificacion,
            'mantenimiento' => $this->mantenimiento,
        ]);
    }
}
