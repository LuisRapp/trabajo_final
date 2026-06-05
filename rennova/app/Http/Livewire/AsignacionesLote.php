<?php

namespace App\Http\Livewire;

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Services\AsignacionLoteService;
use Livewire\Component;

class AsignacionesLote extends Component
{
    public $lotes = [];

    public $empleados = [];

    public $maquinarias = [];

    public $historial = [];

    public $id_lote;

    public $empleados_seleccionados = [];

    public $maquinarias_seleccionadas = [];

    public $modo = 'nuevo'; // 'nuevo' o 'editar'

    public $guardando = false;

    public $mostrar_historial = true;

    public $busqueda_empleado = '';

    public $busqueda_maquinaria = '';

    protected $rules = [
        'id_lote' => 'required|exists:lotes,id_lote',
        'empleados_seleccionados' => 'array',
        'maquinarias_seleccionadas' => 'array',
    ];

    protected $messages = [
        'id_lote.required' => 'Debe seleccionar un lote',
        'id_lote.exists' => 'El lote seleccionado no existe',
    ];

    public function mount()
    {
        $this->lotes = Lote::orderBy('id_lote', 'desc')->get();
        $this->empleados = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')->orderBy('nombre')->get();
        $this->maquinarias = Maquinaria::with('tipoMaquinaria')->orderBy('modelo')->get();
        $this->cargarHistorial();
    }

    public function updatedIdLote()
    {
        $this->cargarAsignaciones();
        $this->modo = 'editar';
    }

    public function cargarAsignaciones()
    {
        $this->reset(['empleados_seleccionados', 'maquinarias_seleccionadas']);
        if (! $this->id_lote) {
            return;
        }

        $lote = Lote::with(['empleados:id_empleado', 'maquinarias:id_maquinaria'])
            ->find($this->id_lote);
        if (! $lote) {
            return;
        }

        $this->empleados_seleccionados = $lote->empleados->pluck('id_empleado')->toArray();
        $this->maquinarias_seleccionadas = $lote->maquinarias->pluck('id_maquinaria')->toArray();
    }

    public function cargarHistorial()
    {
        $this->historial = Lote::with(['empleados' => function ($q) {
            $q->select('empleados.id_empleado', 'apellido', 'nombre');
        }, 'maquinarias' => function ($q) {
            $q->select('maquinarias.id_maquinaria', 'modelo');
        }])
            ->has('empleados') // Solo lotes con al menos un empleado asignado
            ->orHas('maquinarias') // O al menos una maquinaria
            ->orderBy('id_lote', 'desc')
            ->get();
    }

    public function guardar()
    {
        $this->validate();
        $this->guardando = true;

        try {
            $conflictosEmpleados = $this->detectarConflictosEmpleados();
            $conflictosMaquinarias = $this->detectarConflictosMaquinarias();

            if (! empty($conflictosEmpleados) || ! empty($conflictosMaquinarias)) {
                $mensaje = "No se puede guardar: recursos ya asignados a otros lotes activos:\n";
                if (! empty($conflictosEmpleados)) {
                    $mensaje .= "\n• Empleados: ".implode(', ', $conflictosEmpleados);
                }
                if (! empty($conflictosMaquinarias)) {
                    $mensaje .= "\n• Maquinarias: ".implode(', ', $conflictosMaquinarias);
                }
                session()->flash('error', $mensaje);
                $this->guardando = false;

                return;
            }

            $servicio = app(AsignacionLoteService::class);
            $servicio->asignarRecursos(
                $this->id_lote,
                $this->empleados_seleccionados ?? [],
                $this->maquinarias_seleccionadas ?? [],
                $this->datosSolicitud()
            );

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones guardadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al guardar asignaciones: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function editarAsignacion($loteId)
    {
        $this->id_lote = $loteId;
        $this->cargarAsignaciones();
        $this->modo = 'editar';
        $this->mostrar_historial = false;
        $this->dispatch('scrollToForm');
    }

    public function eliminarAsignacion($loteId)
    {
        try {
            $servicio = app(AsignacionLoteService::class);
            $servicio->eliminarAsignaciones($loteId, $this->datosSolicitud());

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones eliminadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar asignaciones: '.$e->getMessage());
        }
    }

    public function liberar($loteId)
    {
        try {
            $lote = Lote::findOrFail($loteId);

            if ($lote->estado !== 'terminado') {
                $servicio = app(AsignacionLoteService::class);
                $servicio->liberarRecursos($loteId, $this->datosSolicitud());

                session()->flash('message', 'Lote marcado como terminado y recursos liberados.');
            } else {
                session()->flash('error', 'El lote ya está marcado como terminado.');
            }

            $this->cargarHistorial();
            $this->resetCampos();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al liberar recursos: '.$e->getMessage());
        }
    }

    public function nuevaAsignacion()
    {
        $this->resetCampos();
        $this->mostrar_historial = false;
    }

    public function cancelar()
    {
        $this->resetCampos();
        $this->mostrar_historial = true;
    }

    public function resetCampos()
    {
        $this->reset(['id_lote', 'empleados_seleccionados', 'maquinarias_seleccionadas', 'busqueda_empleado', 'busqueda_maquinaria']);
        $this->modo = 'nuevo';
    }

    public function getEmpleadosFiltradosProperty()
    {
        if (empty($this->busqueda_empleado)) {
            return $this->empleados;
        }

        $busq = strtolower($this->busqueda_empleado);

        return $this->empleados->filter(function ($emp) use ($busq) {
            $nombre_completo = strtolower($emp->apellido.' '.$emp->nombre);

            return str_contains($nombre_completo, $busq);
        });
    }

    public function getMaquinariasFiltradaProperty()
    {
        if (empty($this->busqueda_maquinaria)) {
            return $this->maquinarias;
        }

        $busq = strtolower($this->busqueda_maquinaria);

        return $this->maquinarias->filter(function ($maq) use ($busq) {
            return str_contains(strtolower($maq->modelo), $busq);
        });
    }

    /**
     * Detecta conflictos de empleados ya asignados a otros lotes activos.
     *
     * @return array<string>
     */
    private function detectarConflictosEmpleados(): array
    {
        if (empty($this->empleados_seleccionados)) {
            return [];
        }

        $conflictos = [];
        $lotesConflicto = Lote::where('estado', 'activo')
            ->where('id_lote', '!=', $this->id_lote)
            ->whereHas('empleados', function ($q) {
                $q->whereIn('empleados.id_empleado', $this->empleados_seleccionados);
            })
            ->with(['empleados' => function ($q) {
                $q->whereIn('empleados.id_empleado', $this->empleados_seleccionados)
                    ->select('empleados.id_empleado', 'apellido', 'nombre');
            }])
            ->get();

        foreach ($lotesConflicto as $lote) {
            foreach ($lote->empleados as $empleado) {
                $conflictos[] = "{$empleado->apellido}, {$empleado->nombre} (ya asignado a Lote #{$lote->id_lote} - {$lote->ubicacion})";
            }
        }

        return $conflictos;
    }

    /**
     * Detecta conflictos de maquinarias ya asignadas a otros lotes activos.
     *
     * @return array<string>
     */
    private function detectarConflictosMaquinarias(): array
    {
        if (empty($this->maquinarias_seleccionadas)) {
            return [];
        }

        $conflictos = [];
        $lotesConflicto = Lote::where('estado', 'activo')
            ->where('id_lote', '!=', $this->id_lote)
            ->whereHas('maquinarias', function ($q) {
                $q->whereIn('maquinarias.id_maquinaria', $this->maquinarias_seleccionadas);
            })
            ->with(['maquinarias' => function ($q) {
                $q->whereIn('maquinarias.id_maquinaria', $this->maquinarias_seleccionadas)
                    ->select('maquinarias.id_maquinaria', 'modelo');
            }])
            ->get();

        foreach ($lotesConflicto as $lote) {
            foreach ($lote->maquinarias as $maquinaria) {
                $conflictos[] = "{$maquinaria->modelo} (ya asignada a Lote #{$lote->id_lote} - {$lote->ubicacion})";
            }
        }

        return $conflictos;
    }

    /**
     * Retorna el contexto de la solicitud actual para auditoría.
     *
     * @return array{user_id: int|null, ip_address: string|null, user_agent: string|null, url: string}
     */
    private function datosSolicitud(): array
    {
        return [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ];
    }

    public function render()
    {
        return view('livewire.asignaciones-lote');
    }
}
