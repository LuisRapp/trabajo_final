<?php

namespace App\Services;

use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionInsumo;
use App\Models\PropuestaAsignacionMaquinaria;
use App\Notifications\OrdenCompraPropuestaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PropuestaAsignacionService
{
    /**
     * Save the user's resource selection for a proposal.
     *
     * Updates selected flags on employees, machinery, and inputs
     * within a single database transaction.
     *
     * @param  int  $proposalId  The proposal ID
     * @param  array<int|string, bool>  $employeeSelected  Row ID → selected flag
     * @param  array<int|string, bool>  $maquinariaSelected  Row ID → selected flag
     * @param  array<int|string, bool>  $insumoSelected  Row ID → selected flag
     */
    public function guardarSeleccion(int $proposalId, array $employeeSelected, array $maquinariaSelected, array $insumoSelected): void
    {
        DB::transaction(function () use ($proposalId, $employeeSelected, $maquinariaSelected, $insumoSelected) {
            foreach ($employeeSelected as $rowId => $selected) {
                PropuestaAsignacionEmpleado::where('id_allocation_proposal_employee', (int) $rowId)
                    ->where('id_allocation_proposal', $proposalId)
                    ->update(['selected' => (bool) $selected]);
            }

            foreach ($maquinariaSelected as $rowId => $selected) {
                PropuestaAsignacionMaquinaria::where('id_allocation_proposal_maquinaria', (int) $rowId)
                    ->where('id_allocation_proposal', $proposalId)
                    ->update(['selected' => (bool) $selected]);
            }

            foreach ($insumoSelected as $rowId => $selected) {
                PropuestaAsignacionInsumo::where('id_allocation_proposal_insumo', (int) $rowId)
                    ->where('id_allocation_proposal', $proposalId)
                    ->update(['selected' => (bool) $selected]);
            }
        });
    }

    /**
     * Confirm a proposal (marks as reviewed, changes status to confirmed).
     *
     * If the proposal has low confidence, it is marked for manual review
     * before allowing application.
     *
     * @param  int  $proposalId  The proposal ID to confirm
     */
    public function confirmarRecomendacion(int $proposalId): PropuestaAsignacion
    {
        $proposal = PropuestaAsignacion::findOrFail($proposalId);

        $meta = $proposal->meta ?? [];
        if ($this->esBajaConfianza($meta)) {
            $meta['review_required'] = true;
            $meta['reviewed_at'] = now()->toISOString();
        }

        $proposal->status = 'confirmed';
        $proposal->confirmed_at = now();
        $proposal->meta = $meta;
        $proposal->save();

        return $proposal;
    }

    /**
     * Apply a proposal to its lot: sync employees and machinery.
     *
     * Preconditions:
     * - Low-confidence proposals must be confirmed first
     * - Selected employees/machinery must not be busy in other in-process lots
     *
     * Closes competing proposals for the same lot/task before applying.
     *
     * @param  int  $proposalId  The proposal ID to apply
     *
     * @throws \RuntimeException If preconditions are not met
     */
    public function aplicarPropuesta(int $proposalId): PropuestaAsignacion
    {
        return DB::transaction(function () use ($proposalId) {
            /** @var PropuestaAsignacion $proposal */
            $proposal = PropuestaAsignacion::query()
                ->with(['lote', 'proposedEmployees', 'proposedMaquinarias'])
                ->lockForUpdate()
                ->findOrFail($proposalId);

            if ($proposal->status === 'applied') {
                return $proposal;
            }

            $lote = $proposal->lote;
            if (! $lote) {
                throw new \RuntimeException('La propuesta no tiene lote asociado.');
            }

            $meta = $proposal->meta ?? [];
            $lowConfidence = $this->esBajaConfianza($meta);
            if ($lowConfidence && $proposal->status !== 'confirmed') {
                throw new \RuntimeException('Propuesta con baja confianza. Confirma primero para revisión manual.');
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

            $busyEmployees = $this->buscarEmpleadosOcupados($empleadosIds, (int) $lote->id_lote);
            if (! empty($busyEmployees)) {
                throw new \RuntimeException('Algunos empleados ya están asignados a otros lotes en proceso.');
            }

            $busyMaquinarias = $this->buscarMaquinariasOcupadas($maquinariasIds, (int) $lote->id_lote);
            if (! empty($busyMaquinarias)) {
                throw new \RuntimeException('Algunas maquinarias ya están asignadas a otros lotes en proceso.');
            }

            $this->cerrarPropuestasCompetidoras($proposal);

            $lote->empleados()->sync($empleadosIds);
            $lote->maquinarias()->sync($maquinariasIds);

            $proposal->status = 'applied';
            if (! $proposal->confirmed_at) {
                $proposal->confirmed_at = now();
            }
            $proposal->applied_at = now();
            $proposal->save();

            return $proposal;
        });
    }

    /**
     * Dispatch job to generate allocation proposals for a lot.
     *
     * @param  int  $loteId  The lot ID to generate proposals for
     */
    public function despacharGeneracionRecomendaciones(int $loteId): void
    {
        GenerateAllocationProposalsForLote::dispatch($loteId);
    }

    /**
     * Send purchase order notification if applicable.
     *
     * Prevents re-sending if already sent (tracked in meta.purchase_order.sent_at).
     * Calls AutomaticAllocationService::ensureWeek1SupplyEstimates() to complete
     * quantities and costs before sending.
     *
     * @param  int  $proposalId  The proposal ID
     */
    public function enviarOrdenCompraSiCorresponde(int $proposalId): void
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

        $emails = $this->resolverDestinatariosOrdenCompra($proposal);
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

    /**
     * Determine if a proposal has low confidence based on its meta.
     */
    private function esBajaConfianza($meta): bool
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

    /**
     * Close competing proposals for the same lot/task when applying one.
     */
    private function cerrarPropuestasCompetidoras(PropuestaAsignacion $proposal): void
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

    /**
     * Find employees already assigned to other in-process lots.
     *
     * @param  array<int>  $empleadosIds
     * @return array<int>
     */
    private function buscarEmpleadosOcupados(array $empleadosIds, int $loteActualId): array
    {
        if (empty($empleadosIds)) {
            return [];
        }

        return DB::table('lote_empleado as le')
            ->join('lotes as l', 'l.id_lote', '=', 'le.id_lote')
            ->where('l.estado', 'en_proceso')
            ->where('l.id_lote', '!=', $loteActualId)
            ->whereIn('le.id_empleado', $empleadosIds)
            ->pluck('le.id_empleado')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Find machinery already assigned to other in-process lots.
     *
     * @param  array<int>  $maquinariasIds
     * @return array<int>
     */
    private function buscarMaquinariasOcupadas(array $maquinariasIds, int $loteActualId): array
    {
        if (empty($maquinariasIds)) {
            return [];
        }

        return DB::table('lote_maquinaria as lm')
            ->join('lotes as l', 'l.id_lote', '=', 'lm.id_lote')
            ->where('l.estado', 'en_proceso')
            ->where('l.id_lote', '!=', $loteActualId)
            ->whereIn('lm.id_maquinaria', $maquinariasIds)
            ->pluck('lm.id_maquinaria')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolve email recipients for a purchase order notification.
     *
     * Priority order:
     * 1. Configured purchase_order_emails
     * 2. Selected capataz (foreman) employees with email
     * 3. First selected employee with email (fallback)
     * 4. admin_email (last fallback)
     *
     * @return array<string>
     */
    private function resolverDestinatariosOrdenCompra(PropuestaAsignacion $proposal): array
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
}
