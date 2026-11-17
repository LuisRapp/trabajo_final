<?php

namespace App\Http\Livewire;

use App\Services\TipoMaquinariaService;
use Livewire\Component;
use Livewire\WithPagination;

class TiposMaquinaria extends Component
{
    use WithPagination;

    public $tipo_id;

    public $nombre;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected TipoMaquinariaService $servicio;

    public function boot(): void
    {
        $this->servicio = app(TipoMaquinariaService::class);
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    protected function rules()
    {
        return $this->servicio->reglas($this->tipo_id);
    }

    public function render()
    {
        return view('livewire.tipos-maquinaria', [
            'tipos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->authorize($this->tipo_id ? 'editar-tipos-maquinaria' : 'crear-tipos-maquinaria');
        $this->validate();

        $this->servicio->crearOActualizar($this->tipo_id, [
            'nombre' => $this->nombre,
        ]);

        session()->flash('message', $this->tipo_id ? 'Tipo actualizado correctamente.' : 'Tipo creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $this->authorize('editar-tipos-maquinaria');
        $this->tab_activo = 'nuevo';
        $tipo = $this->servicio->obtener($id);
        $this->tipo_id = $tipo->id_tipo_maquinaria;
        $this->nombre = $tipo->nombre;
    }

    public function eliminar($id)
    {
        $this->authorize('eliminar-tipos-maquinaria');
        $this->servicio->eliminar($id);
        session()->flash('message', 'Tipo eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre']);
    }
}
