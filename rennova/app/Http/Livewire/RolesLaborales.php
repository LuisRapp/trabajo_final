<?php

namespace App\Http\Livewire;

use App\Services\RolLaboralService;
use Livewire\Component;
use Livewire\WithPagination;

class RolesLaborales extends Component
{
    use WithPagination;

    public $rol_id;

    public $nombre;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected RolLaboralService $servicio;

    public function boot(): void
    {
        $this->servicio = app(RolLaboralService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->rol_id);
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    public function render()
    {
        return view('livewire.roles-laborales', [
            'roles' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->rol_id, [
            'nombre' => $this->nombre,
        ]);

        session()->flash('message', $this->rol_id ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('rolGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $rol = $this->servicio->obtener($id);
        $this->rol_id = $rol->id_rol_laboral;
        $this->nombre = $rol->nombre;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Rol eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['rol_id', 'nombre']);
    }
}
