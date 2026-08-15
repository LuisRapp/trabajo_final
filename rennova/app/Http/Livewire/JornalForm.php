<?php

namespace App\Http\Livewire;

use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use Livewire\Component;

class JornalForm extends Component
{
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
        $jornalVigente = $this->obtenerJornalEmpleadoParaFecha($empleado?->id_empleado, $this->fecha) ?? 0;

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

    public function obtenerJornalEmpleadoParaFecha($empleadoId, $fecha): ?float
    {
        if (! $empleadoId || ! $fecha) {
            return null;
        }

        $empleado = $this->empleadosFiltrados->firstWhere('id_empleado', $empleadoId);
        if (! $empleado || ! $empleado->rolLaboral) {
            return null;
        }

        $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        if (! $rolId) {
            return null;
        }

        $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
            ->vigenteEnFecha($fecha)
            ->first();

        if ($hist) {
            return (float) ($hist->jornal_diario ?? 0);
        }

        return (float) ($empleado->rolLaboral->jornal_diario ?? 0);
    }

    private function actualizarJornalPorEmpleado(): void
    {
        $this->jornal_por_empleado = [];
        if (! $this->fecha) {
            return;
        }

        foreach ($this->empleadosFiltrados as $emp) {
            $this->jornal_por_empleado[$emp->id_empleado] = $this->obtenerJornalEmpleadoParaFecha($emp->id_empleado, $this->fecha) ?? (float) ($emp->rolLaboral->jornal_diario ?? 0);
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
