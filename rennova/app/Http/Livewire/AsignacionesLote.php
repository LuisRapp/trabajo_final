<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Lote;
use App\Models\Empleado;
use App\Models\Maquinaria;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

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
        $this->lotes = Lote::orderBy('id_lote','desc')->get();
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
        $this->reset(['empleados_seleccionados','maquinarias_seleccionadas']);
        if (!$this->id_lote) return;

        $lote = Lote::with(['empleados:id_empleado','maquinarias:id_maquinaria'])
            ->find($this->id_lote);
        if (!$lote) return;

        $this->empleados_seleccionados = $lote->empleados->pluck('id_empleado')->toArray();
        $this->maquinarias_seleccionadas = $lote->maquinarias->pluck('id_maquinaria')->toArray();
    }

    public function cargarHistorial()
    {
        $this->historial = Lote::with(['empleados' => function($q) {
            $q->select('empleados.id_empleado', 'apellido', 'nombre');
        }, 'maquinarias' => function($q) {
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
            // Validar asignaciones únicas: verificar si los recursos ya están asignados a otros lotes activos
            $conflictosEmpleados = [];
            $conflictosMaquinarias = [];

            if (!empty($this->empleados_seleccionados)) {
                $lotesConflicto = Lote::where('estado', 'activo')
                    ->where('id_lote', '!=', $this->id_lote)
                    ->whereHas('empleados', function($q) {
                        $q->whereIn('empleados.id_empleado', $this->empleados_seleccionados);
                    })
                    ->with(['empleados' => function($q) {
                        $q->whereIn('empleados.id_empleado', $this->empleados_seleccionados)
                          ->select('empleados.id_empleado', 'apellido', 'nombre');
                    }])
                    ->get();

                foreach ($lotesConflicto as $lote) {
                    foreach ($lote->empleados as $empleado) {
                        $conflictosEmpleados[] = "{$empleado->apellido}, {$empleado->nombre} (ya asignado a Lote #{$lote->id_lote} - {$lote->ubicacion})";
                    }
                }
            }

            if (!empty($this->maquinarias_seleccionadas)) {
                $lotesConflictoMaq = Lote::where('estado', 'activo')
                    ->where('id_lote', '!=', $this->id_lote)
                    ->whereHas('maquinarias', function($q) {
                        $q->whereIn('maquinarias.id_maquinaria', $this->maquinarias_seleccionadas);
                    })
                    ->with(['maquinarias' => function($q) {
                        $q->whereIn('maquinarias.id_maquinaria', $this->maquinarias_seleccionadas)
                          ->select('maquinarias.id_maquinaria', 'modelo');
                    }])
                    ->get();

                foreach ($lotesConflictoMaq as $lote) {
                    foreach ($lote->maquinarias as $maquinaria) {
                        $conflictosMaquinarias[] = "{$maquinaria->modelo} (ya asignada a Lote #{$lote->id_lote} - {$lote->ubicacion})";
                    }
                }
            }

            // Si hay conflictos, mostrar error y no guardar
            if (!empty($conflictosEmpleados) || !empty($conflictosMaquinarias)) {
                $mensaje = "No se puede guardar: recursos ya asignados a otros lotes activos:\n";
                if (!empty($conflictosEmpleados)) {
                    $mensaje .= "\n• Empleados: " . implode(', ', $conflictosEmpleados);
                }
                if (!empty($conflictosMaquinarias)) {
                    $mensaje .= "\n• Maquinarias: " . implode(', ', $conflictosMaquinarias);
                }
                session()->flash('error', $mensaje);
                $this->guardando = false;
                return;
            }

            // Si no hay conflictos, proceder con la asignación
            DB::transaction(function () {
                $lote = Lote::findOrFail($this->id_lote);

                // Obtener estado actual antes de sincronizar
                $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
                $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

                $nuevosEmpleados = $this->empleados_seleccionados ?? [];
                $nuevasMaquinarias = $this->maquinarias_seleccionadas ?? [];

                // Calcular diferencias
                $empleadosAdjuntar = array_values(array_diff($nuevosEmpleados, $empleadosActuales));
                $empleadosDesvincular = array_values(array_diff($empleadosActuales, $nuevosEmpleados));

                $maquinariasAdjuntar = array_values(array_diff($nuevasMaquinarias, $maquinariasActuales));
                $maquinariasDesvincular = array_values(array_diff($maquinariasActuales, $nuevasMaquinarias));

                // Sincronizar
                $lote->empleados()->sync($nuevosEmpleados);
                $lote->maquinarias()->sync($nuevasMaquinarias);

                // Registrar auditoría de adjuntos
                if (!empty($empleadosAdjuntar)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'attached',
                        'old_values' => [],
                        'new_values' => [
                            'relation' => 'empleados',
                            'empleados_ids' => $empleadosAdjuntar,
                            'empleados' => Empleado::whereIn('id_empleado', $empleadosAdjuntar)
                                ->get(['id_empleado', 'apellido', 'nombre'])
                                ->map(fn($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                                ->toArray(),
                        ],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }

                if (!empty($maquinariasAdjuntar)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'attached',
                        'old_values' => [],
                        'new_values' => [
                            'relation' => 'maquinarias',
                            'maquinarias_ids' => $maquinariasAdjuntar,
                            'maquinarias' => Maquinaria::whereIn('id_maquinaria', $maquinariasAdjuntar)
                                ->get(['id_maquinaria', 'modelo'])
                                ->map(fn($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                                ->toArray(),
                        ],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }

                // Registrar auditoría de desvinculados
                if (!empty($empleadosDesvincular)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'detached',
                        'old_values' => [
                            'relation' => 'empleados',
                            'empleados_ids' => $empleadosDesvincular,
                            'empleados' => Empleado::whereIn('id_empleado', $empleadosDesvincular)
                                ->get(['id_empleado', 'apellido', 'nombre'])
                                ->map(fn($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                                ->toArray(),
                        ],
                        'new_values' => [],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }

                if (!empty($maquinariasDesvincular)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'detached',
                        'old_values' => [
                            'relation' => 'maquinarias',
                            'maquinarias_ids' => $maquinariasDesvincular,
                            'maquinarias' => Maquinaria::whereIn('id_maquinaria', $maquinariasDesvincular)
                                ->get(['id_maquinaria', 'modelo'])
                                ->map(fn($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                                ->toArray(),
                        ],
                        'new_values' => [],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }
            });

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones guardadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al guardar asignaciones: ' . $e->getMessage());
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
            DB::transaction(function () use ($loteId) {
                $lote = Lote::findOrFail($loteId);

                $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
                $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

                $lote->empleados()->detach();
                $lote->maquinarias()->detach();

                if (!empty($empleadosActuales)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'detached',
                        'old_values' => [
                            'relation' => 'empleados',
                            'empleados_ids' => $empleadosActuales,
                            'empleados' => Empleado::whereIn('id_empleado', $empleadosActuales)
                                ->get(['id_empleado', 'apellido', 'nombre'])
                                ->map(fn($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                                ->toArray(),
                        ],
                        'new_values' => [],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }

                if (!empty($maquinariasActuales)) {
                    Audit::create([
                        'auditable_type' => Lote::class,
                        'auditable_id' => $lote->id_lote,
                        'event' => 'detached',
                        'old_values' => [
                            'relation' => 'maquinarias',
                            'maquinarias_ids' => $maquinariasActuales,
                            'maquinarias' => Maquinaria::whereIn('id_maquinaria', $maquinariasActuales)
                                ->get(['id_maquinaria', 'modelo'])
                                ->map(fn($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                                ->toArray(),
                        ],
                        'new_values' => [],
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                    ]);
                }
            });

            $this->cargarHistorial();
            session()->flash('message', 'Asignaciones eliminadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar asignaciones: ' . $e->getMessage());
        }
    }

    public function liberar($loteId)
    {
        try {
            $lote = Lote::findOrFail($loteId);
            
            // Marcar lote como terminado y liberar recursos
            if ($lote->estado !== 'terminado') {
                DB::transaction(function () use ($lote) {
                    $lote->estado = 'terminado';
                    $lote->save();
                    
                    // Liberar empleados y maquinarias
                    $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
                    $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

                    $lote->empleados()->detach();
                    $lote->maquinarias()->detach();

                    if (!empty($empleadosActuales)) {
                        Audit::create([
                            'auditable_type' => Lote::class,
                            'auditable_id' => $lote->id_lote,
                            'event' => 'detached',
                            'old_values' => [
                                'relation' => 'empleados',
                                'empleados_ids' => $empleadosActuales,
                                'empleados' => Empleado::whereIn('id_empleado', $empleadosActuales)
                                    ->get(['id_empleado', 'apellido', 'nombre'])
                                    ->map(fn($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                                    ->toArray(),
                            ],
                            'new_values' => [],
                            'user_id' => auth()->id(),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'url' => request()->fullUrl(),
                        ]);
                    }

                    if (!empty($maquinariasActuales)) {
                        Audit::create([
                            'auditable_type' => Lote::class,
                            'auditable_id' => $lote->id_lote,
                            'event' => 'detached',
                            'old_values' => [
                                'relation' => 'maquinarias',
                                'maquinarias_ids' => $maquinariasActuales,
                                'maquinarias' => Maquinaria::whereIn('id_maquinaria', $maquinariasActuales)
                                    ->get(['id_maquinaria', 'modelo'])
                                    ->map(fn($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                                    ->toArray(),
                            ],
                            'new_values' => [],
                            'user_id' => auth()->id(),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'url' => request()->fullUrl(),
                        ]);
                    }
                });
                
                session()->flash('message', 'Lote marcado como terminado y recursos liberados.');
            } else {
                session()->flash('error', 'El lote ya está marcado como terminado.');
            }
            
            $this->cargarHistorial();
            $this->resetCampos();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al liberar recursos: ' . $e->getMessage());
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
        return $this->empleados->filter(function($emp) use ($busq) {
            $nombre_completo = strtolower($emp->apellido . ' ' . $emp->nombre);
            return str_contains($nombre_completo, $busq);
        });
    }

    public function getMaquinariasFiltradaProperty()
    {
        if (empty($this->busqueda_maquinaria)) {
            return $this->maquinarias;
        }
        
        $busq = strtolower($this->busqueda_maquinaria);
        return $this->maquinarias->filter(function($maq) use ($busq) {
            return str_contains(strtolower($maq->modelo), $busq);
        });
    }

    public function render()
    {
        return view('livewire.asignaciones-lote');
    }
}
