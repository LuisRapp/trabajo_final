<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Venta;
use App\Models\Cliente;

class Ventas extends Component
{
    public $ventas, $recibo_id, $id_empleado, $id_cliente, $id_proveedor, $fecha_emision, $monto, $observaciones, $busqueda = '';
    public $clientes;

    protected $rules = [
        'id_cliente' => 'nullable|exists:clientes,id_cliente',
        'id_empleado' => 'nullable|exists:empleados,id_empleado',
        'id_proveedor' => 'nullable|exists:proveedors,id_proveedor',
        'fecha_emision' => 'required|date',
        'monto' => 'required|numeric|min:0',
        'observaciones' => 'nullable|string|max:150',
    ];

    protected $messages = [
        'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
        'monto.required' => 'El monto es obligatorio.',
        'monto.min' => 'El monto debe ser mayor o igual a 0.',
    ];

    public function mount()
    {
        $this->clientes = Cliente::all();
    }

    public function render()
    {
        $this->cargarVentas();
        return view('livewire.ventas');
    }

    public function cargarVentas()
    {
        $query = Venta::with('cliente')->where('activo', true);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereHas('cliente', function ($qc) use ($busq) {
                    $qc->where('razon_social', 'ILIKE', '%' . $busq . '%');
                })
                ->orWhereDate('fecha_emision', $busq)
                ->orWhereRaw("CAST(monto AS TEXT) ILIKE ?", ['%' . $busq . '%']);
            });
        }

        $this->ventas = $query->orderBy('id_recibo', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarVentas();
    }

    public function guardar()
    {
        $this->validate();

        Venta::updateOrCreate(
            ['id_recibo' => $this->recibo_id],
            [
                'id_empleado' => $this->id_empleado,
                'id_cliente' => $this->id_cliente,
                'id_proveedor' => $this->id_proveedor,
                'fecha_emision' => $this->fecha_emision,
                'monto' => $this->monto,
                'observaciones' => $this->observaciones,
            ]
        );

        session()->flash('message', $this->recibo_id ? 'Venta actualizada correctamente.' : 'Venta creada correctamente.');
        $this->resetCampos();
        $this->dispatch('ventaGuardada');
    }

    public function editar($id)
    {
        $venta = Venta::findOrFail($id);
        $this->recibo_id = $venta->id_recibo;
        $this->id_empleado = $venta->id_empleado;
        $this->id_cliente = $venta->id_cliente;
        $this->id_proveedor = $venta->id_proveedor;
        $this->fecha_emision = $venta->fecha_emision;
        $this->monto = $venta->monto;
        $this->observaciones = $venta->observaciones;
    }

    public function eliminar($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->activo = false;
        $venta->save();
        session()->flash('message', 'Venta dada de baja correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['recibo_id', 'id_empleado', 'id_cliente', 'id_proveedor', 'fecha_emision', 'monto', 'observaciones']);
    }
}
