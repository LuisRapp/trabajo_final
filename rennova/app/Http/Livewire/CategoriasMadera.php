<?php

namespace App\Http\Livewire;

use App\Models\CategoriaMadera;
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

    protected $rules = [
        'nombre' => 'required|min:3|unique:categoria_maderas,nombre',
        'descripcion' => 'nullable|string',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
    ];

    public function render()
    {
        return view('livewire.categorias-madera', [
            'categorias' => $this->cargarCategorias(),
        ]);
    }

    public function cargarCategorias()
    {
        $query = CategoriaMadera::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('descripcion', 'ILIKE', '%'.$busq.'%');
            });
        }

        return $query->orderBy('id_categoria_madera', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        CategoriaMadera::updateOrCreate(
            ['id_categoria_madera' => $this->categoria_id],
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]
        );

        session()->flash('message', $this->categoria_id ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('categoriaGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $categoria = CategoriaMadera::findOrFail($id);
        $this->categoria_id = $categoria->id_categoria_madera;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
    }

    public function eliminar($id)
    {
        CategoriaMadera::findOrFail($id)->delete();
        session()->flash('message', 'Categoría eliminada correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['categoria_id', 'nombre', 'descripcion']);
    }
}
