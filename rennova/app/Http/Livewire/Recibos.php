<?php

namespace App\Http\Livewire;

use App\Services\ReciboService;
use Livewire\Component;
use Livewire\WithPagination;

class Recibos extends Component
{
    use WithPagination;

    public $recibo_id;

    public $id_empleado;

    public $fecha_emision;

    public $monto_bruto;

    public $descuentos;

    public $monto;

    public $observaciones;

    public $busqueda = '';

    public $tab_activo = 'listado';

    public $empleados;

    protected ReciboService $servicio;

    public function boot(): void
    {
        $this->servicio = app(ReciboService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado.',
        'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
        'monto_bruto.required' => 'El monto bruto es obligatorio.',
        'monto_bruto.min' => 'El monto bruto debe ser mayor o igual a 0.',
        'descuentos.min' => 'Los descuentos deben ser mayor o igual a 0.',
    ];

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
        $this->empleados = $this->servicio->listarEmpleados();
    }

    public function render()
    {
        return view('livewire.recibos', [
            'recibos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->calcularMontoNeto();

        $this->servicio->crearOActualizar($this->recibo_id, [
            'id_empleado' => $this->id_empleado,
            'fecha_emision' => $this->fecha_emision,
            'monto_bruto' => $this->monto_bruto,
            'descuentos' => $this->descuentos,
            'observaciones' => $this->observaciones,
        ]);

        session()->flash('message', $this->recibo_id ? 'Recibo actualizado correctamente.' : 'Recibo creado correctamente.');
        $this->resetCampos();
        $this->dispatch('reciboGuardado');
    }

    public function editar($id)
    {
        $recibo = $this->servicio->obtener($id);
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
        $this->servicio->eliminar($id);
        session()->flash('message', 'Recibo dado de baja correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['recibo_id', 'id_empleado', 'fecha_emision', 'monto_bruto', 'descuentos', 'monto', 'observaciones']);
    }
}
