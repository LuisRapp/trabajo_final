<?php

namespace App\Http\Livewire;

use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Services\InventarioService;
use Livewire\Component;
use Livewire\WithPagination;

class Insumos extends Component
{
    use WithPagination;

    public $insumo_id;

    public $nombre;

    public $descripcion;

    public $id_unidad_medida;

    public $id_proveedor;

    public $busqueda = '';

    public $unidades;

    public $proveedores;

    public $tab_activo = 'listado';

    protected $rules = [
        'nombre' => 'required|min:3',
        'descripcion' => 'nullable|string',
        'id_unidad_medida' => 'required|exists:unidad_medidas,id_unidad_medida',
        'id_proveedor' => 'required|exists:proveedores,id_proveedor',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'id_unidad_medida.required' => 'Debe seleccionar una unidad de medida.',
        'id_proveedor.required' => 'Debe seleccionar un proveedor.',
    ];

    public function mount()
    {
        $this->unidades = UnidadMedida::all();
        $this->proveedores = Proveedor::all();
    }

    public function render()
    {
        return view('livewire.insumos', [
            'insumos' => $this->cargarInsumos(),
        ]);
    }

    public function cargarInsumos()
    {
        // Usar scope optimizado para evitar N+1 queries
        $query = InventarioService::queryInsumosConStockYPrecio()
            ->with(['unidadMedida', 'proveedor']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('descripcion', 'ILIKE', '%'.$busq.'%')
                    ->orWhereHas('proveedor', function ($qp) use ($busq) {
                        $qp->where('razon_social', 'ILIKE', '%'.$busq.'%');
                    })
                    ->orWhereHas('unidadMedida', function ($qu) use ($busq) {
                        $qu->where('nombre', 'ILIKE', '%'.$busq.'%')
                            ->orWhere('abreviatura', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_insumo', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        Insumo::updateOrCreate(
            ['id_insumo' => $this->insumo_id],
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'id_unidad_medida' => $this->id_unidad_medida,
                'id_proveedor' => $this->id_proveedor,
            ]
        );

        session()->flash('message', $this->insumo_id ? 'Insumo actualizado correctamente.' : 'Insumo creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('insumoGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $insumo = Insumo::findOrFail($id);
        $this->insumo_id = $insumo->id_insumo;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->id_unidad_medida = $insumo->id_unidad_medida;
        $this->id_proveedor = $insumo->id_proveedor;
    }

    public function eliminar($id)
    {
        Insumo::findOrFail($id)->delete();
        session()->flash('message', 'Insumo eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['insumo_id', 'nombre', 'descripcion', 'id_unidad_medida', 'id_proveedor']);
    }
}
