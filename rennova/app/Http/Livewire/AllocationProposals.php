<?php

namespace App\Http\Livewire;

use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\Lote;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionInsumo;
use App\Models\PropuestaAsignacionMaquinaria;
use App\Notifications\OrdenCompraPropuestaNotification;
use App\Services\AutomaticAllocationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class AllocationProposals extends Component
{
    public $loteId;

    public $lotes = [];

    public $filter_lote_id = '';

    public $filter_status = '';

    public $proposals = [];

    public $mostrar_listado = true;

    public $selected_proposal_id;

    public $selectedProposal;

    public $employeeSelected = [];

    public $maquinariaSelected = [];

    public $insumoSelected = [];

    public $guardando = false;

    public function mount($loteId = null)
    {
        $this->loteId = $loteId !== null ? (int) $loteId : null;

        $this->lotes = Lote::orderBy('id_lote', 'desc')->get();

        if ($this->loteId) {
            $this->filter_lote_id = (string) $this->loteId;
        }

        $this->refreshProposals();

        if ($this->loteId && ! $this->selected_proposal_id && $this->proposals && $this->proposals->count() > 0) {
            $this->seleccionar((int) $this->proposals->first()->id_allocation_proposal);
        }
    }

    public function updatedFilterLoteId()
    {
        $this->refreshProposals();
    }

    public function updatedFilterStatus()
    {
        $this->refreshProposals();
    }

    public function refreshProposals()
    {
        $query = PropuestaAsignacion::query()
            ->with(['lote', 'loteTarea'])
            ->orderByDesc('id_allocation_proposal');

        if (! empty($this->filter_lote_id)) {
            $query->where('id_lote', (int) $this->filter_lote_id);
        }

        if (! empty($this->filter_status)) {
            $query->where('status', $this->filter_status);
        }

        $this->proposals = $query->limit(200)->get();

        if ($this->selected_proposal_id) {
            $exists = $this->proposals->firstWhere('id_allocation_proposal', (int) $this->selected_proposal_id);
            if (! $exists) {
                $this->resetSelection();
            }
        }
    }

    public function generarAhora()
    {
        $loteId = $this->loteId ?: (int) $this->filter_lote_id;
        if (! $loteId) {
            session()->flash('error', 'Seleccione un lote para generar propuestas.');

            return;
        }

        GenerateAllocationProposalsForLote::dispatch($loteId);
        session()->flash('message', 'Generación solicitada. Refrescá en unos segundos.');
    }

    public function seleccionar($proposalId)
    {
        $this->selected_proposal_id = (int) $proposalId;
        $this->mostrar_listado = false;
        $this->loadSelectedProposal();
    }

    public function volver()
    {
        $this->mostrar_listado = true;
    }

    private function resetSelection(): void
    {
        $this->reset([
            'selected_proposal_id',
            'selectedProposal',
            'employeeSelected',
            'maquinariaSelected',
            'insumoSelected',
        ]);
    }

    private function loadSelectedProposal(): void
    {
        if (! $this->selected_proposal_id) {
            $this->resetSelection();

            return;
        }

        $proposal = PropuestaAsignacion::query()
            ->with([
                'lote',
                'loteTarea',
                'proposedEmployees.empleado.rolLaboral',
                'proposedMaquinarias.maquinaria.tipoMaquinaria',
                'proposedInsumos.insumo.unidadMedida',
            ])
            ->find($this->selected_proposal_id);

        if (! $proposal) {
            $this->resetSelection();

            return;
        }

        $this->selectedProposal = $proposal;

        $this->employeeSelected = $proposal->proposedEmployees
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_employee => (bool) $row->selected])
            ->toArray();

        $this->maquinariaSelected = $proposal->proposedMaquinarias
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_maquinaria => (bool) $row->selected])
            ->toArray();

        $this->insumoSelected = $proposal->proposedInsumos
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_insumo => (bool) $row->selected])
            ->toArray();
    }

    public function guardarSeleccion()
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            DB::transaction(function () {
                foreach ($this->employeeSelected as $rowId => $selected) {
                    PropuestaAsignacionEmpleado::where('id_allocation_proposal_employee', (int) $rowId)
                        ->where('id_allocation_proposal', (int) $this->selected_proposal_id)
                        ->update(['selected' => (bool) $selected]);
                }

                foreach ($this->maquinariaSelected as $rowId => $selected) {
                    PropuestaAsignacionMaquinaria::where('id_allocation_proposal_maquinaria', (int) $rowId)
                        ->where('id_allocation_proposal', (int) $this->selected_proposal_id)
                        ->update(['selected' => (bool) $selected]);
                }

                foreach ($this->insumoSelected as $rowId => $selected) {
                    PropuestaAsignacionInsumo::where('id_allocation_proposal_insumo', (int) $rowId)
                        ->where('id_allocation_proposal', (int) $this->selected_proposal_id)
                        ->update(['selected' => (bool) $selected]);
                }
            });

            $this->loadSelectedProposal();
            session()->flash('message', 'Selección guardada correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al guardar selección: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function confirmar()
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->guardarSeleccion();

            $proposal = PropuestaAsignacion::find((int) $this->selected_proposal_id);
            if ($proposal) {
                $meta = $proposal->meta ?? [];
                if ($this->isLowConfidence($meta)) {
                    $meta['review_required'] = true;
                    $meta['reviewed_at'] = now()->toISOString();
                }

                $proposal->status = 'confirmed';
                $proposal->confirmed_at = now();
                $proposal->meta = $meta;
                $proposal->save();
            }

            $this->enviarOrdenCompraSiCorresponde((int) $this->selected_proposal_id);

            $this->loadSelectedProposal();
            $this->refreshProposals();
            session()->flash('message', 'Propuesta confirmada.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al confirmar: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function aplicar()
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->guardarSeleccion();

            DB::transaction(function () {
                /** @var PropuestaAsignacion $proposal */
                $proposal = PropuestaAsignacion::query()
                    ->with(['lote', 'proposedEmployees', 'proposedMaquinarias'])
                    ->lockForUpdate()
                    ->findOrFail((int) $this->selected_proposal_id);

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
                    throw new \RuntimeException('Propuesta con baja confianza. Confirma primero para revision manual.');
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
            });

            $this->enviarOrdenCompraSiCorresponde((int) $this->selected_proposal_id);

            $this->loadSelectedProposal();
            $this->refreshProposals();
            session()->flash('message', 'Asignación aplicada al lote.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al aplicar: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
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

    public function render()
    {
        return view('livewire.allocation-proposals');
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

        // Evitar re-envíos (guardado en meta JSON).
        $meta = $proposal->meta ?? [];
        if (! empty($meta['purchase_order']['sent_at'] ?? null)) {
            return;
        }

        // Completa cantidades/costos si faltan.
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

        // Prioriza capataz seleccionado (si tiene email).
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

        // Fallback: primer empleado seleccionado con email.
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

        // Último fallback: admin_email.
        if (empty($emails)) {
            $admin = trim((string) config('mail.admin_email', ''));
            if ($admin !== '') {
                $emails[] = $admin;
            }
        }

        // Unifica.
        $emails = array_values(array_unique(array_filter($emails)));

        return $emails;
    }
}
