<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Adelanto;
use App\Models\Empleado;

class Adelantos extends Component
{
    use WithPagination;
    public $adelanto_id, $id_empleado, $monto, $fecha_emision, $busqueda = '';
    public $tab_activo = 'listado';

    protected $rules = [
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'monto' => 'required|numeric|min:0',
        'fecha_emision' => 'required|date',
    ];

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado.',
        'monto.required' => 'El monto es obligatorio.',
        'monto.min' => 'El monto debe ser mayor o igual a 0.',
        'fecha_emision.required' => 'La fecha de adelanto es obligatoria.',
    ];

    public function render()
    {
        return view('livewire.adelantos', [
            'adelantos' => $this->obtenerAdelantos()->paginate(10),
            'empleados' => Empleado::orderBy('apellido')->orderBy('nombre')->get(),
        ]);
    }

    public function obtenerAdelantos()
    {
        $query = Adelanto::with('empleado');


        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereRaw("CAST(monto AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereDate('fecha_emision', $busq)
                  ->orWhereHas('empleado', function($qe) use ($busq) {
                      $qe->where('apellido', 'ILIKE', '%' . $busq . '%')
                         ->orWhere('nombre', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        return $query->orderBy('id_adelanto', 'desc');
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        Adelanto::updateOrCreate(
            ['id_adelanto' => $this->adelanto_id],
            [
                'id_empleado' => $this->id_empleado,
                'monto' => $this->monto,
                'fecha_emision' => $this->fecha_emision,
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
        $this->fecha_emision = $adelanto->fecha_emision;
        $this->tab_activo = 'nuevo';
    }

    public function eliminar($id)
    {
        Adelanto::findOrFail($id)->delete();
        session()->flash('message', 'Adelanto eliminado correctamente.');
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->reset(['adelanto_id', 'id_empleado', 'monto', 'fecha_emision']);
    }
}
