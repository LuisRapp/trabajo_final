<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Recibo;
use App\Models\Empleado;

class Recibos extends Component
{
    public $recibos, $recibo_id, $id_empleado, $fecha_emision, $monto_bruto, $descuentos, $monto, $observaciones, $busqueda = '';
    public $empleados;

    protected $rules = [
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'fecha_emision' => 'required|date',
        'monto_bruto' => 'required|numeric|min:0',
        'descuentos' => 'nullable|numeric|min:0',
        'observaciones' => 'nullable|string|max:150',
    ];

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado.',
        'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
        'monto_bruto.required' => 'El monto bruto es obligatorio.',
        'monto_bruto.min' => 'El monto bruto debe ser mayor o igual a 0.',
        'descuentos.min' => 'Los descuentos deben ser mayor o igual a 0.',
    ];

    // Calcula automáticamente el monto neto cuando cambian los valores
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['monto_bruto', 'descuentos'])) {
            $this->calcularMontoNeto();
        }
    }

    public function calcularMontoNeto()
    {
        $bruto = floatval($this->monto_bruto ?? 0);
        $desc = floatval($this->descuentos ?? 0);
        $this->monto = $bruto - $desc;
    }

    public function mount()
    {
        $this->empleados = Empleado::all();
    }

    public function render()
    {
        $this->cargarRecibos();
        return view('livewire.recibos');
    }

    public function cargarRecibos()
    {
        $query = Recibo::with('empleado')->where('activo', true);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereRaw("CAST(monto AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereRaw("CAST(monto_bruto AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereDate('fecha_emision', $busq)
                  ->orWhereHas('empleado', function($qe) use ($busq) {
                      $qe->where('apellido', 'ILIKE', '%' . $busq . '%')
                         ->orWhere('nombre', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->recibos = $query->orderBy('id_recibo', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarRecibos();
    }

    public function guardar()
    {
        $this->validate();

        // Calcular monto neto antes de guardar
        $this->calcularMontoNeto();

        Recibo::updateOrCreate(
            ['id_recibo' => $this->recibo_id],
            [
                'id_empleado' => $this->id_empleado,
                'fecha_emision' => $this->fecha_emision,
                'monto_bruto' => $this->monto_bruto,
                'descuentos' => $this->descuentos ?? 0,
                'monto' => $this->monto,
                'observaciones' => $this->observaciones,
            ]
        );

        session()->flash('message', $this->recibo_id ? 'Recibo actualizado correctamente.' : 'Recibo creado correctamente.');
        $this->resetCampos();
        $this->dispatch('reciboGuardado');
    }

    public function editar($id)
    {
        $recibo = Recibo::findOrFail($id);
        $this->recibo_id = $recibo->id_recibo;
        $this->id_empleado = $recibo->id_empleado;
        $this->fecha_emision = $recibo->fecha_emision;
        $this->monto_bruto = $recibo->monto_bruto;
        $this->descuentos = $recibo->descuentos;
        $this->monto = $recibo->monto;
        $this->observaciones = $recibo->observaciones;
    }

    public function eliminar($id)
    {
        $recibo = Recibo::findOrFail($id);
        $recibo->activo = false;
        $recibo->save();
        session()->flash('message', 'Recibo dado de baja correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['recibo_id', 'id_empleado', 'fecha_emision', 'monto_bruto', 'descuentos', 'monto', 'observaciones']);
    }
}
