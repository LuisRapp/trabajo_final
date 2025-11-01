<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\TipoMantenimiento as TipoMantenimientoModel;

class TipoMantenimiento extends Component
{
    public $tipos, $tipo_id, $nombre, $busqueda = '';

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
        $query = TipoMantenimientoModel::where('activo', true);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%' . $busq . '%');
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
            ['nombre' => $this->nombre, 'activo' => true]
        );

        session()->flash('message', $this->tipo_id ? 'Tipo actualizado correctamente.' : 'Tipo creado correctamente.');
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $tipo = TipoMantenimientoModel::findOrFail($id);
        $this->tipo_id = $tipo->id_tipo_mantenimiento;
        $this->nombre = $tipo->nombre;
    }

    public function eliminar($id)
    {
        $tipo = TipoMantenimientoModel::findOrFail($id);
        $tipo->activo = false;
        $tipo->save();
        session()->flash('message', 'Tipo de mantenimiento dado de baja.');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre']);
    }
}
