<?php

namespace App\Http\Livewire;

use App\Models\RolLaboral;
use Livewire\Component;
use Livewire\WithPagination;

class RolesLaborales extends Component
{
    use WithPagination;

    public $rol_id;

    public $nombre;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected $rules = [
        'nombre' => 'required|min:3|unique:roles_laborales,nombre',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    public function render()
    {
        return view('livewire.roles-laborales', [
            'roles' => $this->cargarRoles(),
        ]);
    }

    public function cargarRoles()
    {
        $query = RolLaboral::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%'.$busq.'%');
        }

        return $query->orderBy('id_rol_laboral', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        RolLaboral::updateOrCreate(
            ['id_rol_laboral' => $this->rol_id],
            ['nombre' => $this->nombre]
        );

        session()->flash('message', $this->rol_id ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('rolGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $rol = RolLaboral::findOrFail($id);
        $this->rol_id = $rol->id_rol_laboral;
        $this->nombre = $rol->nombre;
    }

    public function eliminar($id)
    {
        RolLaboral::findOrFail($id)->delete();
        session()->flash('message', 'Rol eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['rol_id', 'nombre']);
    }
}
