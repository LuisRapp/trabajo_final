<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\PropuestaAsignacion;
use App\Notifications\OrdenCompraPropuestaNotification;
use App\Services\AutomaticAllocationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class Lotes extends Component
{
    public $lotes;

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
        $this->cargarLotes();
    }

    public function cargarLotes()
    {
        $query = Lote::query();

        // Aplicar búsqueda si existe
        if (! empty($this->busqueda)) {
            $query->where(function ($q) {
                $q->where('propietario', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('ubicacion', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('especie', 'ILIKE', '%'.$this->busqueda.'%');
            });
        }

        $this->lotes = $query->orderBy('id_lote', 'desc')->get();
    }

    // Actualizar listado cuando cambie la búsqueda
    public function updatedBusqueda()
    {
        $this->cargarLotes();
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
        $this->cargarLotes();

        // Emitir evento para cambiar a la pestaña de listado
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
        $this->cargarLotes();
    }

    public function finalizarLote($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $lote = Lote::findOrFail($id);

                // Cambiar estado a cerrado
                $lote->update(['estado' => 'cerrado']);

                // Liberar empleados asignados al lote
                DB::table('lote_empleado')
                    ->where('id_lote', $id)
                    ->delete();

                // Liberar maquinarias asignadas al lote
                DB::table('lote_maquinaria')
                    ->where('id_lote', $id)
                    ->delete();

                // Opcional: marcar todas las propuestas como cerradas
                DB::table('allocation_proposals')
                    ->where('id_lote', $id)
                    ->update(['status' => 'closed']);
            });

            session()->flash('message', 'Lote finalizado correctamente. Los recursos han sido liberados.');
            $this->cargarLotes();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al finalizar el lote: '.$e->getMessage());
        }
    }

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

        $lote = Lote::find($this->modalLoteId);
        if (! $lote) {
            $this->recomendacionesError = 'No se encontró el lote seleccionado.';

            return;
        }

        if ($lote->estado === 'inactivo') {
            $this->recomendacionesError = 'El lote está inactivo. Activá el lote para generar recomendaciones.';

            return;
        }

        try {
            DB::table('allocation_proposals')
                ->where('id_lote', $lote->id_lote)
                ->where('status', 'draft')
                ->update(['status' => 'closed']);

            GenerateAllocationProposalsForLote::dispatchSync(
                $this->modalLoteId,
                months: 24,
                minSamples: 5,
                gapDaysForRunSplit: 7,
                skipIfAlreadyGeneratedToday: true,
            );

            $this->cargarRecomendaciones();

            if (empty($this->recomendaciones)) {
                $this->recomendacionesError = 'No se generaron recomendaciones. Planificá tareas o intentá nuevamente.';

                return;
            }

            $this->recomendacionesMensaje = 'Recomendaciones generadas correctamente.';
        } catch (\Throwable $e) {
            $this->recomendacionesError = 'No se pudieron generar las recomendaciones.';
        }
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

        $requiresReview = false;
        $reviewMessage = null;

        try {
            DB::transaction(function () use ($proposalId, &$requiresReview, &$reviewMessage) {
                /** @var PropuestaAsignacion $proposal */
                $proposal = PropuestaAsignacion::query()
                    ->with(['lote', 'proposedEmployees', 'proposedMaquinarias'])
                    ->lockForUpdate()
                    ->findOrFail((int) $proposalId);

                if ($proposal->status === 'applied') {
                    return;
                }

                $lote = $proposal->lote;
                if (! $lote) {
                    throw new \RuntimeException('La propuesta no tiene lote asociado.');
                }

                $meta = $proposal->meta ?? [];
                $lowConfidence = $this->isLowConfidence($meta);
                if ($lowConfidence && $proposal->status !== 'confirmed') {
                    $meta['review_required'] = true;
                    $meta['reviewed_at'] = now()->toISOString();
                    $proposal->meta = $meta;
                    $proposal->status = 'confirmed';
                    if (! $proposal->confirmed_at) {
                        $proposal->confirmed_at = now();
                    }
                    $proposal->save();
                    $requiresReview = true;
                    $reviewMessage = 'Propuesta con baja confianza. Confirmada para revision manual. Vuelva a aplicar para asignar.';

                    return;
                }

                $empleadosIds = $proposal->proposedEmployees
                    ->where('selected', true)
                    ->pluck('id_empleado')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $maquinariasIds = $proposal->proposedMaquinarias
                    ->where('selected', true)
                    ->pluck('id_maquinaria')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $busyEmployees = $this->findBusyEmployees($empleadosIds, (int) $lote->id_lote);
                if (! empty($busyEmployees)) {
                    throw new \RuntimeException('Algunos empleados ya estan asignados a otros lotes en proceso.');
                }

                $busyMaquinarias = $this->findBusyMaquinarias($maquinariasIds, (int) $lote->id_lote);
                if (! empty($busyMaquinarias)) {
                    throw new \RuntimeException('Algunas maquinarias ya estan asignadas a otros lotes en proceso.');
                }

                $this->closeOtherProposals($proposal);

                $lote->empleados()->sync($empleadosIds);
                $lote->maquinarias()->sync($maquinariasIds);

                $proposal->status = 'applied';
                if (! $proposal->confirmed_at) {
                    $proposal->confirmed_at = now();
                }
                $proposal->applied_at = now();
                $proposal->save();

                if ($lote->estado !== 'en_proceso') {
                    $lote->update(['estado' => 'en_proceso']);
                }
            });

            if ($requiresReview) {
                $this->recomendacionesMensaje = $reviewMessage;
                $this->cargarRecomendaciones();

                return;
            }

            $this->enviarOrdenCompraSiCorresponde((int) $proposalId);

            $this->recomendacionesMensaje = 'Recomendación aplicada y lote actualizado a En explotación.';
            $this->cargarRecomendaciones();
        } catch (\Throwable $e) {
            \Log::error('Error en confirmarRecomendacion: '.$e->getMessage(), [
                'proposalId' => $proposalId,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->recomendacionesError = 'No se pudo aplicar la recomendación: '.$e->getMessage();
        }
    }

    public function startEdit($proposalId)
    {
        $proposal = PropuestaAsignacion::find((int) $proposalId);
        if (! $proposal) {
            $this->recomendacionesError = 'No se encontró la recomendación seleccionada.';

            return;
        }

        // Bloquear edición de propuestas que ya están aplicadas
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

        // Cargar empleados, maquinarias e insumos para edición
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

        $proposal = PropuestaAsignacion::find((int) $proposalId);
        if (! $proposal) {
            $this->recomendacionesError = 'No se encontró la recomendación seleccionada.';

            return;
        }

        // Bloquear edición de propuestas que ya están aplicadas
        if ($proposal->status === 'applied') {
            $this->recomendacionesError = 'No se pueden editar recomendaciones que ya han sido aplicadas.';

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

        // Validar que los empleados seleccionados no estén ya asignados a otras propuestas aplicadas
        $empleadosSeleccionados = collect($this->editProposedEmployees)
            ->filter(fn ($e) => $e['selected'])
            ->pluck('id_empleado')
            ->toArray();

        if (! empty($empleadosSeleccionados)) {
            $empleadosDuplicados = DB::table('allocation_proposal_employees as ape')
                ->join('allocation_proposals as ap', 'ape.id_allocation_proposal', '=', 'ap.id_allocation_proposal')
                ->where('ap.id_lote', $proposal->id_lote)
                ->where('ap.status', 'applied')
                ->where('ap.id_allocation_proposal', '!=', $proposal->id_allocation_proposal)
                ->where('ape.selected', true)
                ->whereIn('ape.id_empleado', $empleadosSeleccionados)
                ->pluck('ape.id_empleado')
                ->unique()
                ->toArray();

            if (! empty($empleadosDuplicados)) {
                $this->recomendacionesError = 'Algunos empleados ya están asignados en otras propuestas aplicadas del mismo lote.';

                return;
            }
        }

        try {
            DB::transaction(function () use ($proposal, $validator) {
                // Actualizar estimaciones
                $proposal->update($validator->validated());

                // Actualizar selecciones de empleados
                foreach ($this->editProposedEmployees as $emp) {
                    DB::table('allocation_proposal_employees')
                        ->where('id_allocation_proposal_employee', $emp['id'])
                        ->update(['selected' => $emp['selected']]);
                }

                // Actualizar selecciones de maquinarias
                foreach ($this->editProposedMaquinarias as $maq) {
                    DB::table('allocation_proposal_maquinarias')
                        ->where('id_allocation_proposal_maquinaria', $maq['id'])
                        ->update(['selected' => $maq['selected']]);
                }

                // Actualizar selecciones de insumos
                foreach ($this->editProposedInsumos as $ins) {
                    DB::table('allocation_proposal_insumos')
                        ->where('id_allocation_proposal_insumo', $ins['id'])
                        ->update([
                            'selected' => $ins['selected'],
                            'cantidad_semana_1' => $ins['cantidad_semana_1'],
                        ]);
                }
            });

            $this->recomendacionesMensaje = 'Recomendación actualizada correctamente.';
        } catch (\Throwable $e) {
            $this->recomendacionesError = 'Error al actualizar la recomendación: '.$e->getMessage();

            return;
        }

        $this->editProposalId = null;
        $this->editData = [];
        $this->editProposedEmployees = [];
        $this->editProposedMaquinarias = [];
        $this->editProposedInsumos = [];
        $this->cargarRecomendaciones();
    }

    public function eliminarRecomendacion(int $proposalId): void
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        $proposal = PropuestaAsignacion::find($proposalId);
        if (! $proposal) {
            $this->recomendacionesError = 'No se encontró la recomendación seleccionada.';

            return;
        }

        // Solo permitir eliminar borradores
        if ($proposal->status !== 'draft') {
            $this->recomendacionesError = 'Solo se pueden eliminar recomendaciones en borrador.';

            return;
        }

        try {
            $proposal->delete();
            $this->recomendacionesMensaje = 'Recomendación eliminada correctamente.';
            $this->cargarRecomendaciones();
        } catch (\Throwable $e) {
            $this->recomendacionesError = 'No se pudo eliminar la recomendación.';
        }
    }

    public function eliminarBorradores(): void
    {
        $this->recomendacionesError = null;
        $this->recomendacionesMensaje = null;

        if (! $this->modalLoteId) {
            $this->recomendacionesError = 'No se encontró el lote seleccionado.';

            return;
        }

        try {
            $count = PropuestaAsignacion::query()
                ->where('id_lote', $this->modalLoteId)
                ->where('status', 'draft')
                ->delete();

            if ($count > 0) {
                $this->recomendacionesMensaje = "Se eliminaron {$count} recomendación(es) en borrador.";
            } else {
                $this->recomendacionesMensaje = 'No hay recomendaciones en borrador para eliminar.';
            }

            $this->cargarRecomendaciones();
        } catch (\Throwable $e) {
            $this->recomendacionesError = 'No se pudieron eliminar las recomendaciones.';
        }
    }

    private function cargarRecomendaciones(): void
    {
        if (! $this->modalLoteId) {
            $this->recomendaciones = [];

            return;
        }

        try {
            $this->recomendaciones = PropuestaAsignacion::query()
                ->with([
                    'proposedInsumos.insumo.unidadMedida',
                    'proposedEmployees.empleado.rolLaboral',
                    'proposedMaquinarias.maquinaria.tipoMaquinaria',
                ])
                ->where('id_lote', $this->modalLoteId)
                ->orderByDesc('id_allocation_proposal')
                ->get()
                ->all();
        } catch (\Throwable $e) {
            $this->recomendaciones = [];
            $this->recomendacionesError = 'No se pudieron cargar las recomendaciones.';
        }
    }

    public function toggleExpand($proposalId)
    {
        $proposalId = (int) $proposalId;
        $this->expandedProposalId = $this->expandedProposalId === $proposalId ? null : $proposalId;
    }

    private function isLowConfidence($meta): bool
    {
        if (! is_array($meta)) {
            return false;
        }

        if (! empty($meta['review_required'])) {
            return true;
        }

        $reason = $meta['default_rates']['reason'] ?? null;

        return $reason === 'sin_historico';
    }

    private function closeOtherProposals(PropuestaAsignacion $proposal): void
    {
        $query = PropuestaAsignacion::query()
            ->where('id_lote', $proposal->id_lote)
            ->where('id_allocation_proposal', '!=', $proposal->id_allocation_proposal);

        if (! empty($proposal->id_lote_tarea)) {
            $query->where('id_lote_tarea', $proposal->id_lote_tarea);
        } else {
            $query->whereNull('id_lote_tarea')
                ->where('tipo_tarea', $proposal->tipo_tarea);
        }

        $query->where(function ($q) {
            $q->whereNull('status')
                ->orWhereIn('status', ['draft', 'confirmed', 'applied']);
        })->update(['status' => 'closed']);
    }

    private function findBusyEmployees(array $empleadosIds, int $currentLoteId): array
    {
        if (empty($empleadosIds)) {
            return [];
        }

        return DB::table('lote_empleado as le')
            ->join('lotes as l', 'l.id_lote', '=', 'le.id_lote')
            ->where('l.estado', 'en_proceso')
            ->where('l.id_lote', '!=', $currentLoteId)
            ->whereIn('le.id_empleado', $empleadosIds)
            ->pluck('le.id_empleado')
            ->unique()
            ->values()
            ->all();
    }

    private function findBusyMaquinarias(array $maquinariasIds, int $currentLoteId): array
    {
        if (empty($maquinariasIds)) {
            return [];
        }

        return DB::table('lote_maquinaria as lm')
            ->join('lotes as l', 'l.id_lote', '=', 'lm.id_lote')
            ->where('l.estado', 'en_proceso')
            ->where('l.id_lote', '!=', $currentLoteId)
            ->whereIn('lm.id_maquinaria', $maquinariasIds)
            ->pluck('lm.id_maquinaria')
            ->unique()
            ->values()
            ->all();
    }

    private function enviarOrdenCompraSiCorresponde(int $proposalId): void
    {
        /** @var PropuestaAsignacion|null $proposal */
        $proposal = PropuestaAsignacion::query()
            ->with([
                'lote',
                'loteTarea',
                'proposedEmployees.empleado.rolLaboral',
                'proposedMaquinarias.maquinaria.tipoMaquinaria',
                'proposedInsumos.insumo.unidadMedida',
            ])
            ->find($proposalId);

        if (! $proposal) {
            return;
        }

        $meta = $proposal->meta ?? [];
        if (! empty($meta['purchase_order']['sent_at'] ?? null)) {
            return;
        }

        app(AutomaticAllocationService::class)->ensureWeek1SupplyEstimates($proposal);
        $proposal->refresh();
        $proposal->load([
            'proposedEmployees.empleado.rolLaboral',
            'proposedMaquinarias.maquinaria.tipoMaquinaria',
            'proposedInsumos.insumo.unidadMedida',
        ]);

        $emails = $this->resolvePurchaseOrderRecipients($proposal);
        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(new OrdenCompraPropuestaNotification($proposal));
        }

        $meta['purchase_order'] = [
            'sent_at' => now()->toISOString(),
            'recipients' => $emails,
        ];
        $proposal->meta = $meta;
        $proposal->save();
    }

    private function resolvePurchaseOrderRecipients(PropuestaAsignacion $proposal): array
    {
        $emails = [];

        foreach ((array) config('mail.purchase_order_emails', []) as $e) {
            $e = trim((string) $e);
            if ($e !== '') {
                $emails[] = $e;
            }
        }

        foreach ($proposal->proposedEmployees->where('selected', true) as $row) {
            $email = trim((string) ($row->empleado->email ?? ''));
            if ($email === '') {
                continue;
            }

            $rol = mb_strtolower((string) ($row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? '')));
            if ($rol !== '' && str_contains($rol, 'capataz')) {
                $emails[] = $email;
            }
        }

        if (empty($emails)) {
            $fallback = $proposal->proposedEmployees
                ->where('selected', true)
                ->map(fn ($r) => trim((string) ($r->empleado->email ?? '')))
                ->filter()
                ->first();

            if ($fallback) {
                $emails[] = (string) $fallback;
            }
        }

        if (empty($emails)) {
            $admin = trim((string) config('mail.admin_email', ''));
            if ($admin !== '') {
                $emails[] = $admin;
            }
        }

        return array_values(array_unique(array_filter($emails)));
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

    public function render()
    {
        return view('livewire.lotes');
    }
}
