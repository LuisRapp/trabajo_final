<?php

namespace App\Http\Livewire;

use App\Models\RolLaboral;
use Livewire\Component;

class RolesLaborales extends Component
{
    public $roles;

    public $rol_id;

    public $nombre;

    public $busqueda = '';

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
        $this->cargarRoles();

        return view('livewire.roles-laborales');
    }

    public function cargarRoles()
    {
        $query = RolLaboral::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%'.$busq.'%');
        }

        $this->roles = $query->orderBy('id_rol_laboral', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarRoles();
    }

    public function guardar()
    {
        $this->validate();

        RolLaboral::updateOrCreate(
            ['id_rol_laboral' => $this->rol_id],
            ['nombre' => $this->nombre]
        );

        session()->flash('message', $this->rol_id ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.');
        $this->resetCampos();
        $this->dispatch('rolGuardado');
    }

    public function editar($id)
    {
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
