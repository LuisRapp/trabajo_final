<?php

namespace App\Http\Livewire;

use App\Models\TipoMantenimiento as TipoMantenimientoModel;
use Livewire\Component;

class TipoMantenimiento extends Component
{
    public $tipos;

    public $tipo_id;

    public $nombre;

    public $busqueda = '';
    public $tab_activo = 'listado';

    protected $rules = [
        'nombre' => 'required|min:3|unique:tipo_mantenimientos,nombre',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    public function render()
    {
        $this->cargarTipos();

        return view('livewire.tipo-mantenimiento');
    }

    public function cargarTipos()
    {
        $query = TipoMantenimientoModel::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%'.$busq.'%');
        }

        $this->tipos = $query->orderBy('id_tipo_mantenimiento', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarTipos();
    }

    public function guardar()
    {
        $this->validate();

        TipoMantenimientoModel::updateOrCreate(
            ['id_tipo_mantenimiento' => $this->tipo_id],
            ['nombre' => $this->nombre]
        );

        session()->flash('message', $this->tipo_id ? 'Tipo actualizado correctamente.' : 'Tipo creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $tipo = TipoMantenimientoModel::findOrFail($id);
        $this->tipo_id = $tipo->id_tipo_mantenimiento;
        $this->nombre = $tipo->nombre;
    }

    public function eliminar($id)
    {
        $tipo = TipoMantenimientoModel::findOrFail($id);
        $tipo->delete();
        session()->flash('message', 'Tipo de mantenimiento dado de baja.');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre']);
    }
}
