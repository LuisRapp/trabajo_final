<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;
use Carbon\Carbon;

class Auditorias extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $mostrarFiltros = true;

    public $busqueda = '';
    public $filtroModelo = '';
    public $filtroEvento = '';
    public $filtroUsuario = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';
    public $perPage = 20;

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroModelo' => ['except' => ''],
        'filtroEvento' => ['except' => ''],
    ];

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function updatingFiltroModelo()
    {
        $this->resetPage();
    }

    public function updatingFiltroEvento()
    {
        $this->resetPage();
    }

    public function updatingFiltroUsuario()
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->busqueda = '';
        $this->filtroModelo = '';
        $this->filtroEvento = '';
        $this->filtroUsuario = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    public function toggleFiltros(): void
    {
        $this->mostrarFiltros = ! $this->mostrarFiltros;
    }

    public function render()
    {
        $query = Audit::with('user')
            ->orderBy('created_at', 'desc');

        // Filtro por búsqueda en URL o IP
        if ($this->busqueda) {
            $query->where(function($q) {
                $q->where('url', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('tags', 'like', '%' . $this->busqueda . '%');
            });
        }

        // Filtro por modelo
        if ($this->filtroModelo) {
            $query->where('auditable_type', $this->filtroModelo);
        }

        // Filtro por evento
        if ($this->filtroEvento) {
            $query->where('event', $this->filtroEvento);
        }

        // Filtro por usuario
        if ($this->filtroUsuario) {
            $query->where('user_id', $this->filtroUsuario);
        }

        // Filtro por rango de fechas
        if ($this->filtroFechaDesde) {
            $query->whereDate('created_at', '>=', $this->filtroFechaDesde);
        }

        if ($this->filtroFechaHasta) {
            $query->whereDate('created_at', '<=', $this->filtroFechaHasta);
        }

        $auditorias = $query->paginate($this->perPage);

        // Obtener modelos únicos para el filtro
        $modelos = Audit::select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(function($modelo) {
                return [
                    'value' => $modelo,
                    'label' => class_basename($modelo)
                ];
            });

        // Obtener usuarios únicos para el filtro
        $usuarios = Audit::with('user')
            ->whereNotNull('user_id')
            ->select('user_id', 'user_type')
            ->distinct()
            ->get()
            ->map(function($audit) {
                return [
                    'id' => $audit->user_id,
                    'nombre' => $audit->user?->name ?? 'Usuario #' . $audit->user_id
                ];
            })
            ->unique('id')
            ->sortBy('nombre');

        return view('livewire.auditorias', [
            'auditorias' => $auditorias,
            'modelos' => $modelos,
            'usuarios' => $usuarios,
        ]);
    }
}
