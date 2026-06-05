<?php

namespace App\Http\Livewire;

use App\Models\TipoMaquinaria;
use Livewire\Component;
use Livewire\WithPagination;

class TiposMaquinaria extends Component
{
    use WithPagination;

    public $tipo_id;

    public $nombre;

    public $busqueda = '';

    public $tab_activo = 'listado';

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
                'unique:tipo_maquinarias,nombre,'.($this->tipo_id ?? 'NULL').',id_tipo_maquinaria',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.tipos-maquinaria', [
            'tipos' => $this->cargarTipos(),
        ]);
    }

    public function cargarTipos()
    {
        $query = TipoMaquinaria::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre', 'ILIKE', '%'.$busq.'%');
        }

        return $query->orderBy('id_tipo_maquinaria', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
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
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
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
