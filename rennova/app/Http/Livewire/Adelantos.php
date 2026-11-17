<?php

namespace App\Http\Livewire;

use App\Models\Adelanto;
use App\Models\Empleado;
use App\Services\EmpleadoPagoService;
use Livewire\Component;
use Livewire\WithPagination;

class Adelantos extends Component
{
    use WithPagination;

    public $adelanto_id;

    public $id_empleado;

    public $monto;

    public $fecha_emision;

    public $busqueda = '';

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
            'adelantos' => $this->obtenerAdelantos()->paginate(15),
            'empleados' => Empleado::orderBy('apellido')->orderBy('nombre')->get(),
        ]);
    }

    public function obtenerAdelantos()
    {
        $query = Adelanto::with('empleado');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereRaw('CAST(monto AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereDate('fecha_emision', $busq)
                    ->orWhereHas('empleado', function ($qe) use ($busq) {
                        $qe->where('apellido', 'ILIKE', '%'.$busq.'%')
                            ->orWhere('nombre', 'ILIKE', '%'.$busq.'%');
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
        $this->authorize($this->adelanto_id ? 'editar-adelantos' : 'crear-adelantos');
        $this->validate();

        // Regla: el adelanto no puede superar el 50% del promedio de
        // liquidaciones del empleado (LCT art. 130).
        $maximo = EmpleadoPagoService::montoMaximoAdelanto(Empleado::findOrFail($this->id_empleado));
        if ((float) $this->monto > $maximo) {
            $this->addError('monto', "El adelanto no puede superar el 50% del promedio de liquidaciones del empleado (tope: {$maximo}).");

            return;
        }

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
        $this->authorize('editar-adelantos');
        $adelanto = Adelanto::findOrFail($id);
        $this->adelanto_id = $adelanto->id_adelanto;
        $this->id_empleado = $adelanto->id_empleado;
        $this->monto = $adelanto->monto;
        $this->fecha_emision = $adelanto->fecha_emision;
        $this->tab_activo = 'nuevo';
    }

    public function eliminar($id)
    {
        $this->authorize('eliminar-adelantos');
        Adelanto::findOrFail($id)->delete();
        session()->flash('message', 'Adelanto eliminado correctamente.');
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->reset(['adelanto_id', 'id_empleado', 'monto', 'fecha_emision']);
    }
}
