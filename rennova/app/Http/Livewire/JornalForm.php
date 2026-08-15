<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\JornalLookupTrait;
use App\Models\Empleado;
use Livewire\Component;

class JornalForm extends Component
{
    use JornalLookupTrait;

    public $jornal_id_empleado;

    public $jornales = [];

    public $jornal_por_empleado = [];

    // Props from parent
    public $fecha;

    public $empleadosFiltrados;

    public $es_dia_caido;

    public function mount($fecha = null, $empleadosFiltrados = null, $es_dia_caido = false, $jornales = []): void
    {
        $this->fecha = $fecha;
        $this->empleadosFiltrados = $empleadosFiltrados ?? collect();
        $this->es_dia_caido = $es_dia_caido;
        $this->jornales = $jornales;
        $this->actualizarJornalPorEmpleado();
    }

    public function agregarJornal(): void
    {
        $this->validate([
            'jornal_id_empleado' => 'required|exists:empleados,id_empleado',
        ], [
            'jornal_id_empleado.required' => 'Debe seleccionar un empleado',
        ]);

        foreach ($this->jornales as $j) {
            if ($j['id_empleado'] == $this->jornal_id_empleado) {
                session()->flash('error', 'El empleado ya está en la lista.');

                return;
            }
        }

        $empleado = Empleado::with('rolLaboral')->find($this->jornal_id_empleado);
        $jornalVigente = $this->obtenerJornalEmpleadoParaFecha($empleado?->id_empleado, $this->fecha, $this->empleadosFiltrados) ?? 0;

        $this->jornales[] = [
            'id_empleado' => $empleado->id_empleado,
            'nombre_completo' => $empleado->apellido.', '.$empleado->nombre,
            'rol' => $empleado->rolLaboral->nombre ?? 'N/A',
            'jornal_diario' => $jornalVigente,
            'observaciones' => null,
        ];

        $this->resetJornalForm();
        $this->dispatch('jornalAgregado', jornalData: end($this->jornales));
    }

    public function eliminarJornal(int $index): void
    {
        unset($this->jornales[$index]);
        $this->jornales = array_values($this->jornales);
        $this->dispatch('jornalEliminado', index: $index);
    }

    private function actualizarJornalPorEmpleado(): void
    {
        $this->jornal_por_empleado = [];
        if (! $this->fecha) {
            return;
        }

        foreach ($this->empleadosFiltrados as $emp) {
            $this->jornal_por_empleado[$emp->id_empleado] = $this->obtenerJornalEmpleadoParaFecha($emp->id_empleado, $this->fecha, $this->empleadosFiltrados) ?? (float) ($emp->rolLaboral->jornal_diario ?? 0);
        }
    }

    private function resetJornalForm(): void
    {
        $this->jornal_id_empleado = null;
    }

    public function render()
    {
        return view('livewire.jornal-form');
    }
}
