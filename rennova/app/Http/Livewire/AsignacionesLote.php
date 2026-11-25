<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\AsignacionLoteService;
use Livewire\Component;

class AsignacionesLote extends Component
{
    use MensajesErrorUsuario;

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

    public $loteVolverId = null;

    protected AsignacionLoteService $servicio;

    protected $rules = [
        'id_lote' => 'required|exists:lotes,id_lote',
        'empleados_seleccionados' => 'array',
        'maquinarias_seleccionadas' => 'array',
    ];

    protected $messages = [
        'id_lote.required' => 'Debe seleccionar un lote',
        'id_lote.exists' => 'El lote seleccionado no existe',
    ];

    public function boot(): void
    {
        $this->servicio = app(AsignacionLoteService::class);
    }

    public function mount($loteId = null)
    {
        $this->loteVolverId = $loteId !== null ? (int) $loteId : null;

        $datos = $this->servicio->obtenerDatosFormulario();

        $this->lotes = $datos['lotes'];
        $this->empleados = $datos['empleados'];
        $this->maquinarias = $datos['maquinarias'];
        $this->cargarHistorial();

        if ($this->loteVolverId) {
            // Viniendo de la ficha: el lote ya esta elegido y el formulario al frente.
            $this->id_lote = $this->loteVolverId;
            $this->mostrar_historial = false;
            $this->cargarAsignaciones();
            $this->modo = (! empty($this->empleados_seleccionados) || ! empty($this->maquinarias_seleccionadas))
                ? 'editar'
                : 'nuevo';
        }
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

        $asignaciones = $this->servicio->obtenerAsignaciones((int) $this->id_lote);

        $this->empleados_seleccionados = $asignaciones['empleados'];
        $this->maquinarias_seleccionadas = $asignaciones['maquinarias'];
    }

    public function cargarHistorial()
    {
        $this->historial = $this->servicio->obtenerHistorial();
    }

    public function guardar()
    {
        $this->authorize('crear-asignaciones-lote');
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

            $this->servicio->asignarRecursos(
                (int) $this->id_lote,
                $this->empleados_seleccionados ?? [],
                $this->maquinarias_seleccionadas ?? [],
                $this->datosSolicitud()
            );

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones guardadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar las asignaciones'));
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
        $this->authorize('eliminar-asignaciones-lote');
        try {
            $this->servicio->eliminarAsignaciones((int) $loteId, $this->datosSolicitud());

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones eliminadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'eliminar las asignaciones'));
        }
    }

    public function liberar($loteId)
    {
        $this->authorize('editar-asignaciones-lote');
        try {
            $finalizado = $this->servicio->finalizar((int) $loteId, $this->datosSolicitud());

            if ($finalizado) {
                session()->flash('message', 'Lote finalizado y recursos liberados.');
            } else {
                session()->flash('error', 'El lote ya está finalizado.');
            }

            $this->cargarHistorial();
            $this->resetCampos();
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'liberar los recursos'));
        }
    }

    public function nuevaAsignacion()
    {
        $this->resetCampos();
        $this->mostrar_historial = false;
    }

    public function cancelar()
    {
        $this->authorize('editar-asignaciones-lote');
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
        return $this->servicio->detectarConflictosEmpleados((int) $this->id_lote, $this->empleados_seleccionados);
    }

    /**
     * Detecta conflictos de maquinarias ya asignadas a otros lotes activos.
     *
     * @return array<string>
     */
    private function detectarConflictosMaquinarias(): array
    {
        return $this->servicio->detectarConflictosMaquinarias((int) $this->id_lote, $this->maquinarias_seleccionadas);
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
