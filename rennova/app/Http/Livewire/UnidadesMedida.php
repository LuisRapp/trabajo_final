<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\UnidadMedida;

class UnidadesMedida extends Component
{
    public $unidades, $unidad_id, $nombre, $abreviatura, $busqueda = '';
    public $tab_activo = 'listado';

    protected $rules = [
        'nombre' => 'required|min:2|unique:unidad_medidas,nombre',
        'abreviatura' => 'required|max:10',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
        'abreviatura.required' => 'La abreviatura es obligatoria.',
        'abreviatura.max' => 'La abreviatura no puede tener más de 10 caracteres.',
    ];

    public function render()
    {
        $this->cargarUnidades();
        return view('livewire.unidades-medida');
    }

    public function cargarUnidades()
    {
        $query = UnidadMedida::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%' . $busq . '%')
                  ->orWhere('abreviatura', 'ILIKE', '%' . $busq . '%');
            });
        }

        $this->unidades = $query->orderBy('id_unidad_medida', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarUnidades();
    }

    public function guardar()
    {
        $this->validate();

        UnidadMedida::updateOrCreate(
            ['id_unidad_medida' => $this->unidad_id],
            [
                'nombre' => $this->nombre,
                'abreviatura' => $this->abreviatura,
            ]
        );

        session()->flash('message', $this->unidad_id ? 'Unidad actualizada correctamente.' : 'Unidad creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('unidadGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $unidad = UnidadMedida::findOrFail($id);
        $this->unidad_id = $unidad->id_unidad_medida;
        $this->nombre = $unidad->nombre;
        $this->abreviatura = $unidad->abreviatura;
    }

    public function eliminar($id)
    {
        UnidadMedida::findOrFail($id)->delete();
        session()->flash('message', 'Unidad eliminada correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['unidad_id', 'nombre', 'abreviatura']);
    }
}
