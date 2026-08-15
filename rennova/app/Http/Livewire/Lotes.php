<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\PropuestaAsignacion;
use App\Services\AsignacionLoteService;
use App\Services\PropuestaAsignacionService;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class Lotes extends Component
{
    use MensajesErrorUsuario;
    use WithPagination;

    public $propietario;

    public $ubicacion;

    public $superficie;

    public $estado = 'activo';

    public $condicion_compra;

    public $especie;

    public $latitud;

    public $longitud;

    public $main_task_type;

    public $lote_id;

    public $busqueda = '';

    public $mostrarModalRecomendaciones = false;

    public $modalLoteId = null;

    public $recomendaciones = [];

    public $recomendacionesError = null;

    public $recomendacionesMensaje = null;

    public $editProposalId = null;

    public $editData = [];

    public $expandedProposalId = null;

    public $editProposedEmployees = [];

    public $editProposedMaquinarias = [];

    public $editProposedInsumos = [];

    public $editingProposals = [];

    protected $rules = [
        'propietario' => 'required|string|min:3|max:100',
        'ubicacion' => 'required|string|min:3|max:150',
        'especie' => 'required|string|min:2|max:100',
        'superficie' => 'required|numeric|min:0.1|max:10000',
        'condicion_compra' => 'required|in:propio,alquilado',
        'estado' => 'required|in:activo,en_proceso,inactivo,cerrado,baja',
        'main_task_type' => 'required|in:tala_rasa,raleo,poda,limpieza',
        'latitud' => 'nullable|numeric|between:-90,90',
        'longitud' => 'nullable|numeric|between:-180,180',
    ];

    protected $messages = [
        'required' => 'Este campo es obligatorio.',
        'numeric' => 'Debe ingresar un número válido.',
        'min' => 'El valor es demasiado bajo.',
        'max' => 'El valor es demasiado alto.',
        'in' => 'Seleccione una opción válida.',
    ];

    public function mount()
    {
        $this->resetCampos();
    }

    public function cargarLotes()
    {
        $query = Lote::query();

        if (! empty($this->busqueda)) {
            $query->where(function ($q) {
                $q->where('propietario', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('ubicacion', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('especie', 'ILIKE', '%'.$this->busqueda.'%');
            });
        }

        return $query->orderBy('id_lote', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->propietario = '';
        $this->ubicacion = '';
        $this->superficie = '';
        $this->estado = 'activo';
        $this->condicion_compra = '';
        $this->especie = '';
        $this->latitud = null;
        $this->longitud = null;
        $this->main_task_type = TaskType::TALA_RASA->value;
        $this->lote_id = null;
    }

    public function getTaskTypesProperty(): array
    {
        return TaskType::cases();
    }

    // =========================================================================
    // CRUD — delegates to Eloquent (simple operations)
    // =========================================================================

    public function guardar()
    {
        $this->validate();

        try {
            if ($this->lote_id) {
                $lote = Lote::find($this->lote_id);
                $fromEstado = $lote->estado;
                $lote->update($this->only(['propietario', 'ubicacion', 'superficie', 'estado', 'condicion_compra', 'especie', 'latitud', 'longitud', 'main_task_type']));
                session()->flash('message', 'Lote actualizado correctamente.');

                if ($fromEstado !== 'en_proceso' && $lote->estado === 'en_proceso') {
                    $tareasActivas = LoteTarea::query()
                        ->where('id_lote', $lote->id_lote)
                        ->whereIn('estado', ['planificada', 'en_ejecucion'])
                        ->count();

                    if ($tareasActivas === 0) {
                        session()->flash('message', 'El lote pasó a En explotación. Antes, planificá las tareas para generar recomendaciones reales (ej: 5 ha raleo + 5 ha tala rasa).');

                        return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
                    }

                    return redirect()->route('lotes.recomendaciones', ['loteId' => $lote->id_lote]);
                }
            } else {
                $lote = Lote::create($this->only(['propietario', 'ubicacion', 'superficie', 'estado', 'condicion_compra', 'especie', 'latitud', 'longitud', 'main_task_type']));

                if ($lote->estado === 'en_proceso') {
                    session()->flash('message', 'Lote creado en En explotación. Planificá tareas para generar recomendaciones.');

                    return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
                }

                session()->flash('message', 'Lote creado correctamente.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar el lote. Verificá los datos e intentá nuevamente.');

            return;
        }
        $this->resetCampos();

        $this->dispatch('loteGuardado');
    }

    public function editar($id)
    {
        $lote = Lote::findOrFail($id);
        $this->lote_id = $lote->id_lote;
        $this->propietario = $lote->propietario;
        $this->ubicacion = $lote->ubicacion;
        $this->superficie = $lote->superficie;
        $this->estado = $lote->estado;
        $this->condicion_compra = $lote->condicion_compra;
        $this->especie = $lote->especie;
        $this->latitud = $lote->latitud;
        $this->longitud = $lote->longitud;
        $this->main_task_type = $lote->main_task_type;
    }

    public function eliminar($id)
    {
        Lote::destroy($id);
        session()->flash('message', 'Lote eliminado correctamente.');
        $this->resetCampos();
    }

    public function finalizarLote($id)
    {
        try {
            AsignacionLoteService::finalizar((int) $id);
            session()->flash('message', 'Lote finalizado correctamente. Los recursos han sido liberados.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'finalizar el lote'));
        }
    }

    // =========================================================================
    // Recommendations — delegates to PropuestaAsignacionService
    // =========================================================================

    public function openLaunchpad($loteId)
    {
        $this->modalLoteId = (int) $loteId;
        $this->mostrarModalRecomendaciones = true;
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;
        $this->editProposalId = null;
        $this->editData = [];
        $this->expandedProposalId = null;
        $this->cargarRecomendaciones();

        $lote = Lote::find($this->modalLoteId);
        if ($lote && $lote->estado !== 'inactivo') {
            $exists = PropuestaAsignacion::query()
                ->where('id_lote', $lote->id_lote)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'closed');
                })
                ->exists();

            if (! $exists) {
                GenerateAllocationProposalsForLote::dispatch(
                    $this->modalLoteId,
                    months: 24,
                    minSamples: 5,
                    gapDaysForRunSplit: 7,
                    skipIfAlreadyGeneratedToday: true,
                );
                $this->recomendacionesMensaje = 'Generando recomendaciones...';
            }
        }
    }

    public function generarRecomendaciones()
    {
        if (! $this->modalLoteId) {
            return;
        }

        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        $resultado = PropuestaAsignacionService::generar($this->modalLoteId);

        if ($resultado['error']) {
            $this->recomendacionesError = $resultado['error'];

            return;
        }

        $this->recomendaciones = $resultado['proposals'];
        $this->recomendacionesMensaje = 'Recomendaciones generadas correctamente.';
    }

    public function refrescarRecomendaciones()
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;
        $this->cargarRecomendaciones();
    }

    public function confirmarRecomendacion($proposalId)
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        $resultado = PropuestaAsignacionService::confirmar((int) $proposalId);

        if ($resultado['error']) {
            $this->recomendacionesError = $resultado['error'];

            return;
        }

        if ($resultado['requiresReview']) {
            $this->recomendacionesMensaje = $resultado['reviewMessage'];
        } else {
            $this->recomendacionesMensaje = 'Recomendación aplicada y lote actualizado a En explotación.';
        }

        $this->cargarRecomendaciones();
    }

    public function eliminarRecomendacion(int $proposalId): void
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        $resultado = PropuestaAsignacionService::eliminar($proposalId);

        if ($resultado['error']) {
            $this->recomendacionesError = $resultado['error'];

            return;
        }

        $this->recomendacionesMensaje = $resultado['message'];
        $this->cargarRecomendaciones();
    }

    public function eliminarBorradores(): void
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        $resultado = PropuestaAsignacionService::eliminarBorradores($this->modalLoteId);

        if ($resultado['error']) {
            $this->recomendacionesError = $resultado['error'];

            return;
        }

        $this->recomendacionesMensaje = $resultado['message'];
        $this->cargarRecomendaciones();
    }

    // =========================================================================
    // Edit proposal — delegates to PropuestaAsignacionService
    // =========================================================================

    public function startEdit($proposalId)
    {
        $proposal = PropuestaAsignacion::find((int) $proposalId);
        if (! $proposal) {
            $this->recomendacionesError = 'No se encontró la recomendación seleccionada.';

            return;
        }

        if ($proposal->status === 'applied') {
            $this->recomendacionesError = 'No se pueden editar recomendaciones que ya han sido aplicadas.';

            return;
        }

        $this->editProposalId = (int) $proposalId;
        $this->editData = [
            'estimated_person_days' => $proposal->estimated_person_days,
            'estimated_machine_days' => $proposal->estimated_machine_days,
            'estimated_duration_days' => $proposal->estimated_duration_days,
            'suggested_team_size' => $proposal->suggested_team_size,
            'suggested_machinery_count' => $proposal->suggested_machinery_count,
        ];

        $this->editProposedEmployees = $proposal->proposedEmployees
            ->map(fn ($e) => [
                'id' => $e->id_allocation_proposal_employee,
                'id_empleado' => $e->id_empleado,
                'nombre' => $e->empleado->apellido.', '.$e->empleado->nombre,
                'selected' => (bool) $e->selected,
            ])
            ->values()
            ->toArray();

        $this->editProposedMaquinarias = $proposal->proposedMaquinarias
            ->map(fn ($m) => [
                'id' => $m->id_allocation_proposal_maquinaria,
                'id_maquinaria' => $m->id_maquinaria,
                'nombre' => $m->maquinaria->modelo ?? 'Maquinaria',
                'selected' => (bool) $m->selected,
            ])
            ->values()
            ->toArray();

        $this->editProposedInsumos = $proposal->proposedInsumos
            ->map(fn ($i) => [
                'id' => $i->id_allocation_proposal_insumo,
                'id_insumo' => $i->id_insumo,
                'nombre' => $i->insumo->nombre,
                'cantidad_semana_1' => $i->cantidad_semana_1,
                'selected' => (bool) $i->selected,
            ])
            ->values()
            ->toArray();
    }

    public function cancelEdit()
    {
        $this->editProposalId = null;
        $this->editData = [];
        $this->editProposedEmployees = [];
        $this->editProposedMaquinarias = [];
        $this->editProposedInsumos = [];
    }

    public function saveEdit($proposalId)
    {
        if ($this->editProposalId !== (int) $proposalId) {
            return;
        }

        $validator = Validator::make($this->editData, [
            'estimated_person_days' => 'nullable|numeric|min:0',
            'estimated_machine_days' => 'nullable|numeric|min:0',
            'estimated_duration_days' => 'nullable|numeric|min:0',
            'suggested_team_size' => 'nullable|integer|min:1',
            'suggested_machinery_count' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            $this->recomendacionesError = 'Revisá los valores numéricos antes de guardar.';

            return;
        }

        $resultado = PropuestaAsignacionService::editar(
            (int) $proposalId,
            $validator->validated(),
            $this->editProposedEmployees,
            $this->editProposedMaquinarias,
            $this->editProposedInsumos
        );

        if ($resultado['error']) {
            $this->recomendacionesError = $resultado['error'];

            return;
        }

        $this->recomendacionesMensaje = 'Recomendación actualizada correctamente.';
        $this->editProposalId = null;
        $this->editData = [];
        $this->editProposedEmployees = [];
        $this->editProposedMaquinarias = [];
        $this->editProposedInsumos = [];
        $this->cargarRecomendaciones();
    }

    // =========================================================================
    // UI helpers
    // =========================================================================

    public function toggleExpand($proposalId)
    {
        $proposalId = (int) $proposalId;
        $this->expandedProposalId = $this->expandedProposalId === $proposalId ? null : $proposalId;
    }

    public function cerrarModalRecomendaciones()
    {
        $this->mostrarModalRecomendaciones = false;
        $this->modalLoteId = null;
        $this->recomendaciones = [];
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;
        $this->editProposalId = null;
        $this->editData = [];
        $this->expandedProposalId = null;
    }

    private function cargarRecomendaciones(): void
    {
        if (! $this->modalLoteId) {
            $this->recomendaciones = [];

            return;
        }

        $this->recomendaciones = PropuestaAsignacionService::cargar($this->modalLoteId);

        if (empty($this->recomendaciones) && $this->recomendacionesError === null) {
            // Service returns [] on error too; only set error if we had a lote but got nothing
            // and no error was already set by the caller
        }
    }

    public function render()
    {
        return view('livewire.lotes', [
            'lotes' => $this->cargarLotes(),
        ]);
    }
}
