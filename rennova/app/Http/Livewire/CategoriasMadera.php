<?php

namespace App\Http\Livewire;

use App\Services\CategoriaMaderaService;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriasMadera extends Component
{
    use WithPagination;

    public $categoria_id;

    public $nombre;

    public $descripcion;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected CategoriaMaderaService $servicio;

    public function boot(): void
    {
        $this->servicio = app(CategoriaMaderaService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->categoria_id);
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    public function render()
    {
        return view('livewire.categorias-madera', [
            'categorias' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->categoria_id, [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ]);

        session()->flash('message', $this->categoria_id ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('categoriaGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $categoria = $this->servicio->obtener($id);
        $this->categoria_id = $categoria->id_categoria_madera;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Categoría eliminada correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['categoria_id', 'nombre', 'descripcion']);
    }
}
