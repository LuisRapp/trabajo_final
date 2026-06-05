<?php

namespace App\Http\Livewire;

use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use Livewire\Component;
use Livewire\WithPagination;

class Maquinarias extends Component
{
    use WithPagination;

    public $maquinaria_id;

    public $id_tipo_maquinaria;

    public $modelo;

    public $estado;

    public $es_alquilada;

    public $fecha_inicio_actividades;

    public $umbral_toneladas;

    public $busqueda = '';

    public $tipos;

    public $tab_activo = 'listado';

    protected $rules = [
        'id_tipo_maquinaria' => 'required|exists:tipo_maquinarias,id_tipo_maquinaria',
        'modelo' => 'required|min:2',
        'estado' => 'required|in:operativa,en_mantenimiento,fuera_de_servicio',
        'es_alquilada' => 'required|boolean',
        'fecha_inicio_actividades' => 'required|date',
        'umbral_toneladas' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'id_tipo_maquinaria.required' => 'Debe seleccionar un tipo de maquinaria.',
        'modelo.required' => 'El modelo es obligatorio.',
        'modelo.min' => 'El modelo debe tener al menos 2 caracteres.',
        'estado.required' => 'El estado es obligatorio.',
        'es_alquilada.required' => 'Debe indicar si es alquilada.',
        'fecha_inicio_actividades.required' => 'La fecha de inicio es obligatoria.',
        'umbral_toneladas.numeric' => 'El umbral debe ser un número.',
        'umbral_toneladas.min' => 'El umbral debe ser mayor o igual a 0.',
    ];

    public function mount()
    {
        $this->tipos = TipoMaquinaria::all();
    }

    public function render()
    {
        return view('livewire.maquinarias', [
            'maquinarias' => $this->cargarMaquinarias(),
        ]);
    }

    public function cargarMaquinarias()
    {
        $query = Maquinaria::with('tipoMaquinaria');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('modelo', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('estado', 'ILIKE', '%'.$busq.'%')
                    ->orWhereHas('tipoMaquinaria', function ($qt) use ($busq) {
                        $qt->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_maquinaria', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        Maquinaria::updateOrCreate(
            ['id_maquinaria' => $this->maquinaria_id],
            [
                'id_tipo_maquinaria' => $this->id_tipo_maquinaria,
                'modelo' => $this->modelo,
                'estado' => $this->estado,
                'es_alquilada' => $this->es_alquilada,
                'fecha_inicio_actividades' => $this->fecha_inicio_actividades,
                'umbral_toneladas' => $this->umbral_toneladas,
            ]
        );

        session()->flash('message', $this->maquinaria_id ? 'Maquinaria actualizada correctamente.' : 'Maquinaria creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('maquinariaGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $maquinaria = Maquinaria::findOrFail($id);
        $this->maquinaria_id = $maquinaria->id_maquinaria;
        $this->id_tipo_maquinaria = $maquinaria->id_tipo_maquinaria;
        $this->modelo = $maquinaria->modelo;
        $this->estado = $maquinaria->estado;
        $this->es_alquilada = $maquinaria->es_alquilada;
        $this->fecha_inicio_actividades = $maquinaria->fecha_inicio_actividades;
        $this->umbral_toneladas = $maquinaria->umbral_toneladas;
    }

    public function eliminar($id)
    {
        Maquinaria::findOrFail($id)->delete();
        session()->flash('message', 'Maquinaria eliminada correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['maquinaria_id', 'id_tipo_maquinaria', 'modelo', 'estado', 'es_alquilada', 'fecha_inicio_actividades', 'umbral_toneladas']);
    }
}
