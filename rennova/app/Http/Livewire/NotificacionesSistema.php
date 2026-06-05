<?php

namespace App\Http\Livewire;

use App\Models\NotificacionSistema;
use App\Services\NotificacionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificacionesSistema extends Component
{
    use WithPagination;

    public $filtroTipo = 'todas';

    public $filtroEstado = 'todas';

    protected $queryString = ['filtroTipo', 'filtroEstado'];

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function marcarComoLeida($notificacionId)
    {
        $notificacion = NotificacionSistema::where('id', $notificacionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notificacion) {
            NotificacionService::marcarComoLeida($notificacion);
            $this->dispatch('notificacionActualizada');
        }
    }

    public function marcarComoAccionada($notificacionId)
    {
        $notificacion = NotificacionSistema::where('id', $notificacionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notificacion) {
            NotificacionService::marcarComoAccionada($notificacion);
            $this->dispatch('notificacionActualizada');
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

        $this->dispatch('notificacionActualizada');
        session()->flash('message', 'Todas las notificaciones han sido marcadas como leídas.');
    }

    public function irAMantenimiento($mantenimientoId, $notificacionId)
    {
        $this->marcarComoLeida($notificacionId);

        return redirect()->route('mantenimientos.index', ['highlight' => $mantenimientoId]);
    }

    public function render()
    {
        $query = NotificacionSistema::where('user_id', Auth::id())
            ->with('mantenimiento.maquinaria');

        // Filtro por tipo
        if ($this->filtroTipo !== 'todas') {
            $query->where('tipo', $this->filtroTipo);
        }

        // Filtro por estado
        if ($this->filtroEstado === 'no_leidas') {
            $query->noLeidas();
        } elseif ($this->filtroEstado === 'pendientes') {
            $query->pendientes();
        } elseif ($this->filtroEstado === 'vencidas') {
            $query->vencidas();
        } elseif ($this->filtroEstado === 'accionadas') {
            $query->where('accionada', true);
        }

        $notificaciones = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estadísticas
        $estadisticas = [
            'total' => NotificacionSistema::where('user_id', Auth::id())->count(),
            'no_leidas' => NotificacionSistema::where('user_id', Auth::id())->noLeidas()->count(),
            'pendientes' => NotificacionSistema::where('user_id', Auth::id())->pendientes()->count(),
            'vencidas' => NotificacionSistema::where('user_id', Auth::id())->vencidas()->count(),
        ];

        return view('livewire.notificaciones-sistema', [
            'notificaciones' => $notificaciones,
            'estadisticas' => $estadisticas,
        ]);
    }
}
