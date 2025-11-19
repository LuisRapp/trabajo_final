<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\Proveedor;

class Insumos extends Component
{
    public $insumos, $insumo_id, $nombre, $descripcion, $id_unidad_medida, $id_proveedor, $costo_unitario, $busqueda = '';
    public $unidades, $proveedores;

    protected $rules = [
        'nombre' => 'required|min:3',
        'descripcion' => 'nullable|string',
        'id_unidad_medida' => 'required|exists:unidad_medidas,id_unidad_medida',
        'id_proveedor' => 'required|exists:proveedors,id_proveedor',
        'costo_unitario' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'id_unidad_medida.required' => 'Debe seleccionar una unidad de medida.',
        'id_proveedor.required' => 'Debe seleccionar un proveedor.',
        'costo_unitario.required' => 'El costo unitario es obligatorio.',
        'costo_unitario.numeric' => 'El costo debe ser un número.',
        'costo_unitario.min' => 'El costo debe ser mayor o igual a 0.',
    ];

    public function mount()
    {
        $this->unidades = UnidadMedida::all();
        $this->proveedores = Proveedor::all();
    }

    public function render()
    {
        $this->cargarInsumos();
        return view('livewire.insumos');
    }

    public function cargarInsumos()
    {
        $query = Insumo::with(['unidadMedida', 'proveedor']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%' . $busq . '%')
                  ->orWhere('descripcion', 'ILIKE', '%' . $busq . '%')
                  ->orWhereRaw("CAST(costo_unitario AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereHas('proveedor', function($qp) use ($busq) {
                      $qp->where('razon_social', 'ILIKE', '%' . $busq . '%');
                  })
                  ->orWhereHas('unidadMedida', function($qu) use ($busq) {
                      $qu->where('nombre', 'ILIKE', '%' . $busq . '%')
                         ->orWhere('abreviatura', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->insumos = $query->orderBy('id_insumo', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarInsumos();
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
                'costo_unitario' => $this->costo_unitario,
            ]
        );

        session()->flash('message', $this->insumo_id ? 'Insumo actualizado correctamente.' : 'Insumo creado correctamente.');
        $this->resetCampos();
        $this->dispatch('insumoGuardado');
    }

    public function editar($id)
    {
        $insumo = Insumo::findOrFail($id);
        $this->insumo_id = $insumo->id_insumo;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->id_unidad_medida = $insumo->id_unidad_medida;
        $this->id_proveedor = $insumo->id_proveedor;
        $this->costo_unitario = $insumo->costo_unitario;
    }

    public function eliminar($id)
    {
        Insumo::findOrFail($id)->delete();
        session()->flash('message', 'Insumo eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['insumo_id', 'nombre', 'descripcion', 'id_unidad_medida', 'id_proveedor', 'costo_unitario']);
    }
}
