<?php

namespace App\Http\Livewire;

use App\Services\UnidadMedidaService;
use Livewire\Component;
use Livewire\WithPagination;

class UnidadesMedida extends Component
{
    use WithPagination;

    public $unidad_id;

    public $nombre;

    public $abreviatura;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected UnidadMedidaService $servicio;

    public function boot(): void
    {
        $this->servicio = app(UnidadMedidaService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->unidad_id);
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
        'abreviatura.required' => 'La abreviatura es obligatoria.',
        'abreviatura.max' => 'La abreviatura no puede tener más de 10 caracteres.',
    ];

    public function render()
    {
        return view('livewire.unidades-medida', [
            'unidades' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->unidad_id, [
            'nombre' => $this->nombre,
            'abreviatura' => $this->abreviatura,
        ]);

        session()->flash('message', $this->unidad_id ? 'Unidad actualizada correctamente.' : 'Unidad creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('unidadGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $unidad = $this->servicio->obtener($id);
        $this->unidad_id = $unidad->id_unidad_medida;
        $this->nombre = $unidad->nombre;
        $this->abreviatura = $unidad->abreviatura;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Unidad eliminada correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['unidad_id', 'nombre', 'abreviatura']);
    }
}
