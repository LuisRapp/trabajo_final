<?php

namespace App\Http\Livewire;

use App\Services\HistoricoRolLaboralService;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricoRolesLaborales extends Component
{
    use WithPagination;

    public $rolesLaborales = [];

    public $historico_id;

    public $rol_laboral_id;

    public $precio_tonelada;

    public $jornal_diario;

    public $fecha_inicio;

    public $fecha_fin;

    public $motivo_cambio;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected HistoricoRolLaboralService $servicio;

    public function boot(): void
    {
        $this->servicio = app(HistoricoRolLaboralService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'rol_laboral_id.required' => 'Debe seleccionar un rol laboral.',
        'precio_tonelada.min' => 'El precio por tonelada debe ser mayor o igual a 0.',
        'jornal_diario.min' => 'El jornal diario debe ser mayor o igual a 0.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        $this->rolesLaborales = $this->servicio->listarRoles();
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.historico-roles-laborales', [
            'historicos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->historico_id, [
            'rol_laboral_id' => $this->rol_laboral_id,
            'precio_tonelada' => $this->precio_tonelada,
            'jornal_diario' => $this->jornal_diario,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'motivo_cambio' => $this->motivo_cambio,
        ]);

        session()->flash('message', $this->historico_id ? 'Histórico actualizado correctamente.' : 'Histórico creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('historicoGuardado');
    }

    public function editar($id)
    {
        $historico = $this->servicio->obtener($id);
        $this->tab_activo = 'nuevo';
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
        $this->servicio->eliminar($id);
        session()->flash('message', 'Histórico eliminado correctamente.');
        $this->resetCampos();
    }

    public function resetCampos()
    {
        $this->reset(['historico_id', 'rol_laboral_id', 'precio_tonelada', 'jornal_diario', 'fecha_inicio', 'fecha_fin', 'motivo_cambio']);
    }
}
