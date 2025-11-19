<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Adelanto;
use App\Models\Empleado;

class Adelantos extends Component
{
    public $adelantos, $adelanto_id, $id_empleado, $monto, $fecha_adelanto, $busqueda = '';
    public $empleados;

    protected $rules = [
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'monto' => 'required|numeric|min:0',
        'fecha_adelanto' => 'required|date',
    ];

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado.',
        'monto.required' => 'El monto es obligatorio.',
        'monto.min' => 'El monto debe ser mayor o igual a 0.',
        'fecha_adelanto.required' => 'La fecha de adelanto es obligatoria.',
    ];

    public function mount()
    {
        $this->empleados = Empleado::all();
    }

    public function render()
    {
        $this->cargarAdelantos();
        return view('livewire.adelantos');
    }

    public function cargarAdelantos()
    {
        $query = Adelanto::with('empleado');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereRaw("CAST(monto AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereDate('fecha_adelanto', $busq)
                  ->orWhereHas('empleado', function($qe) use ($busq) {
                      $qe->where('apellido', 'ILIKE', '%' . $busq . '%')
                         ->orWhere('nombre', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->adelantos = $query->orderBy('id_adelanto', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarAdelantos();
    }

    public function guardar()
    {
        $this->validate();

        Adelanto::updateOrCreate(
            ['id_adelanto' => $this->adelanto_id],
            [
                'id_empleado' => $this->id_empleado,
                'monto' => $this->monto,
                'fecha_adelanto' => $this->fecha_adelanto,
            ]
        );

        session()->flash('message', $this->adelanto_id ? 'Adelanto actualizado correctamente.' : 'Adelanto creado correctamente.');
        $this->resetCampos();
        $this->dispatch('adelantoGuardado');
    }

    public function editar($id)
    {
        $adelanto = Adelanto::findOrFail($id);
        $this->adelanto_id = $adelanto->id_adelanto;
        $this->id_empleado = $adelanto->id_empleado;
        $this->monto = $adelanto->monto;
        $this->fecha_adelanto = $adelanto->fecha_adelanto;
    }

    public function eliminar($id)
    {
        Adelanto::findOrFail($id)->delete();
        session()->flash('message', 'Adelanto eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['adelanto_id', 'id_empleado', 'monto', 'fecha_adelanto']);
    }
}
