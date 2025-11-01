<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\HistoricoRolLaboral;
use App\Models\RolLaboral;

class HistoricoRolesLaborales extends Component
{
    public $historicos = [];
    public $roles = [];
    
    public $historico_id;
    public $rol_laboral_id;
    public $precio_tonelada;
    public $jornal_diario;
    public $fecha_inicio;
    public $fecha_fin;
    public $motivo_cambio;
    
    public $busqueda = '';

    protected $rules = [
        'rol_laboral_id' => 'required|exists:rol_laborals,id_rol_laboral',
        'precio_tonelada' => 'nullable|numeric|min:0',
        'jornal_diario' => 'nullable|numeric|min:0',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'nullable|date|after:fecha_inicio',
        'motivo_cambio' => 'nullable|string',
    ];

    protected $messages = [
        'rol_laboral_id.required' => 'Debe seleccionar un rol laboral.',
        'precio_tonelada.min' => 'El precio por tonelada debe ser mayor o igual a 0.',
        'jornal_diario.min' => 'El jornal diario debe ser mayor o igual a 0.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        $this->roles = RolLaboral::orderBy('nombre')->get();
        $this->cargarHistoricos();
    }

    public function cargarHistoricos()
    {
        $query = HistoricoRolLaboral::with(['rolLaboral']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereRaw("CAST(precio_tonelada AS TEXT) ILIKE ?", ["%{$busq}%"])
                  ->orWhereRaw("CAST(jornal_diario AS TEXT) ILIKE ?", ["%{$busq}%"])
                  ->orWhere('motivo_cambio', 'ILIKE', "%{$busq}%")
                  ->orWhereDate('fecha_inicio', $busq)
                  ->orWhereDate('fecha_fin', $busq)
                  ->orWhereHas('rolLaboral', function($qr) use ($busq) {
                      $qr->where('nombre', 'ILIKE', "%{$busq}%");
                  });
            });
        }

        $this->historicos = $query->orderByDesc('fecha_inicio')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarHistoricos();
    }

    public function render()
    {
        $this->cargarHistoricos();
        return view('livewire.historico-roles-laborales');
    }

    public function guardar()
    {
        $this->validate();

        HistoricoRolLaboral::updateOrCreate(
            ['id' => $this->historico_id],
            [
                'rol_laboral_id' => $this->rol_laboral_id,
                'precio_tonelada' => $this->precio_tonelada,
                'jornal_diario' => $this->jornal_diario,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'motivo_cambio' => $this->motivo_cambio,
            ]
        );

        $this->cargarHistoricos();
        session()->flash('message', $this->historico_id ? 'Histórico actualizado correctamente.' : 'Histórico creado correctamente.');
        $this->resetCampos();
        $this->dispatch('historicoGuardado');
    }

    public function editar($id)
    {
        $historico = HistoricoRolLaboral::findOrFail($id);
        $this->historico_id = $historico->id;
        $this->rol_laboral_id = $historico->rol_laboral_id;
        $this->precio_tonelada = $historico->precio_tonelada;
        $this->jornal_diario = $historico->jornal_diario;
        $this->fecha_inicio = $historico->fecha_inicio;
        $this->fecha_fin = $historico->fecha_fin;
        $this->motivo_cambio = $historico->motivo_cambio;
    }

    public function eliminar($id)
    {
        HistoricoRolLaboral::findOrFail($id)->delete();
        $this->cargarHistoricos();
        session()->flash('message', 'Histórico eliminado correctamente.');
        $this->resetCampos();
    }

    public function resetCampos()
    {
        $this->reset(['historico_id', 'rol_laboral_id', 'precio_tonelada', 'jornal_diario', 'fecha_inicio', 'fecha_fin', 'motivo_cambio']);
    }
}
