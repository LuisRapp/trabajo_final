<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\TipoMaquinaria;

class TiposMaquinaria extends Component
{
    public $tipos, $tipo_id, $nombre, $busqueda = '';

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    protected function rules()
    {
        return [
            'nombre' => [
                'required',
                'min:3',
                'unique:tipo_maquinarias,nombre,' . ($this->tipo_id ?? 'NULL') . ',id_tipo_maquinaria'
            ],
        ];
    }

    public function render()
    {
        $this->cargarTipos();
        return view('livewire.tipos-maquinaria');
    }

    public function cargarTipos()
    {
        $query = TipoMaquinaria::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%' . $busq . '%');
        }

        $this->tipos = $query->orderBy('id_tipo_maquinaria', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarTipos();
    }

    public function guardar()
    {
        $this->validate();

        TipoMaquinaria::updateOrCreate(
            ['id_tipo_maquinaria' => $this->tipo_id],
            [
                'nombre' => $this->nombre,
            ]
        );

        session()->flash('message', $this->tipo_id ? 'Tipo actualizado correctamente.' : 'Tipo creado correctamente.');
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $tipo = TipoMaquinaria::findOrFail($id);
        $this->tipo_id = $tipo->id_tipo_maquinaria;
        $this->nombre = $tipo->nombre;
    }

    public function eliminar($id)
    {
        TipoMaquinaria::findOrFail($id)->delete();
        session()->flash('message', 'Tipo eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre']);
    }
}
