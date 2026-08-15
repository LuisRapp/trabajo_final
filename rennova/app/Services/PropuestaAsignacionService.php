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
    // =========================================================================
    // Instance methods (existing — used by other callers)
    // =========================================================================

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

        $emails = self::resolverDestinatariosOrdenCompra($proposal);
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

    // =========================================================================
    // Static methods (extracted from Lotes.php)
    // =========================================================================

    /**
     * Load all proposals for a lot with eager-loaded relationships.
     *
     * @return array<PropuestaAsignacion>
     */
    public static function cargar(int $loteId): array
    {
        try {
            return PropuestaAsignacion::query()
                ->with([
                    'proposedInsumos.insumo.unidadMedida',
                    'proposedEmployees.empleado.rolLaboral',
                    'proposedMaquinarias.maquinaria.tipoMaquinaria',
                ])
                ->where('id_lote', $loteId)
                ->orderByDesc('id_allocation_proposal')
                ->get()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Generate allocation proposals for a lot.
     *
     * Closes existing drafts first, then dispatches generation synchronously.
     *
     * @return array{proposals: array<PropuestaAsignacion>, error: ?string}
     */
    public static function generar(int $loteId): array
    {
        $lote = \App\Models\Lote::find($loteId);
        if (! $lote) {
            return ['proposals' => [], 'error' => 'No se encontró el lote seleccionado.'];
        }

        if ($lote->estado === 'inactivo') {
            return ['proposals' => [], 'error' => 'El lote está inactivo. Activá el lote para generar recomendaciones.'];
        }

        try {
            DB::table('allocation_proposals')
                ->where('id_lote', $lote->id_lote)
                ->whereNull('deleted_at')
                ->where('status', 'draft')
                ->update(['status' => 'closed']);

            GenerateAllocationProposalsForLote::dispatchSync(
                $loteId,
                months: 24,
                minSamples: 5,
                gapDaysForRunSplit: 7,
                skipIfAlreadyGeneratedToday: true,
            );

            $proposals = self::cargar($loteId);

            if (empty($proposals)) {
                return ['proposals' => [], 'error' => 'No se generaron recomendaciones. Planificá tareas o intentá nuevamente.'];
            }

            return ['proposals' => $proposals, 'error' => null];
        } catch (\Throwable $e) {
            return ['proposals' => [], 'error' => 'No se pudieron generar las recomendaciones.'];
        }
    }

    /**
     * Confirm and apply a proposal atomically.
     *
     * Low-confidence proposals are marked for review instead of applied.
     * Competing proposals are closed. Employees and machinery are synced to the lot.
     * Sends purchase order notification if applicable.
     *
     * @return array{requiresReview: bool, reviewMessage: ?string, error: ?string}
     */
    public static function confirmar(int $proposalId): array
    {
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
                $lowConfidence = self::esBajaConfianza($meta);
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

                $busyEmployees = self::buscarEmpleadosOcupados($empleadosIds, (int) $lote->id_lote);
                if (! empty($busyEmployees)) {
                    throw new \RuntimeException('Algunos empleados ya estan asignados a otros lotes en proceso.');
                }

                $busyMaquinarias = self::buscarMaquinariasOcupadas($maquinariasIds, (int) $lote->id_lote);
                if (! empty($busyMaquinarias)) {
                    throw new \RuntimeException('Algunas maquinarias ya estan asignadas a otros lotes en proceso.');
                }

                self::cerrarPropuestasCompetidoras($proposal);

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

            if (! $requiresReview) {
                $service = app(PropuestaAsignacionService::class);
                $service->enviarOrdenCompraSiCorresponde((int) $proposalId);
            }

            return [
                'requiresReview' => $requiresReview,
                'reviewMessage' => $reviewMessage,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            \Log::error('Error en confirmarRecomendacion: '.$e->getMessage(), [
                'proposalId' => $proposalId,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'requiresReview' => false,
                'reviewMessage' => null,
                'error' => 'No se pudo aplicar la recomendación. Intente nuevamente o contacte al administrador.',
            ];
        }
    }

    /**
     * Update proposal data and resource selections.
     *
     * @param  array<string, mixed>  $editData  Validated estimation fields
     * @param  array<int, array<string, mixed>>  $editEmployees  Employee rows with id, selected
     * @param  array<int, array<string, mixed>>  $editMaquinarias  Machinery rows with id, selected
     * @param  array<int, array<string, mixed>>  $editInsumos  Input rows with id, selected, cantidad_semana_1
     * @return array{error: ?string}
     */
    public static function editar(int $proposalId, array $editData, array $editEmployees, array $editMaquinarias, array $editInsumos): array
    {
        $proposal = PropuestaAsignacion::find((int) $proposalId);
        if (! $proposal) {
            return ['error' => 'No se encontró la recomendación seleccionada.'];
        }

        if ($proposal->status === 'applied') {
            return ['error' => 'No se pueden editar recomendaciones que ya han sido aplicadas.'];
        }

        // Validate that selected employees are not already assigned to other applied proposals
        $empleadosSeleccionados = collect($editEmployees)
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
                return ['error' => 'Algunos empleados ya están asignados en otras propuestas aplicadas del mismo lote.'];
            }
        }

        try {
            DB::transaction(function () use ($proposal, $editData, $editEmployees, $editMaquinarias, $editInsumos) {
                $proposal->update($editData);

                foreach ($editEmployees as $emp) {
                    DB::table('allocation_proposal_employees')
                        ->where('id_allocation_proposal_employee', $emp['id'])
                        ->update(['selected' => $emp['selected']]);
                }

                foreach ($editMaquinarias as $maq) {
                    DB::table('allocation_proposal_maquinarias')
                        ->where('id_allocation_proposal_maquinaria', $maq['id'])
                        ->update(['selected' => $maq['selected']]);
                }

                foreach ($editInsumos as $ins) {
                    DB::table('allocation_proposal_insumos')
                        ->where('id_allocation_proposal_insumo', $ins['id'])
                        ->update([
                            'selected' => $ins['selected'],
                            'cantidad_semana_1' => $ins['cantidad_semana_1'],
                        ]);
                }
            });

            return ['error' => null];
        } catch (\Throwable $e) {
            return ['error' => 'Error al actualizar la recomendación. Intente nuevamente o contacte al administrador.'];
        }
    }

    /**
     * Delete a draft proposal.
     *
     * @return array{error: ?string, message: ?string}
     */
    public static function eliminar(int $proposalId): array
    {
        $proposal = PropuestaAsignacion::find($proposalId);
        if (! $proposal) {
            return ['error' => 'No se encontró la recomendación seleccionada.', 'message' => null];
        }

        if ($proposal->status !== 'draft') {
            return ['error' => 'Solo se pueden eliminar recomendaciones en borrador.', 'message' => null];
        }

        try {
            $proposal->delete();

            return ['error' => null, 'message' => 'Recomendación eliminada correctamente.'];
        } catch (\Throwable $e) {
            return ['error' => 'No se pudo eliminar la recomendación.', 'message' => null];
        }
    }

    /**
     * Delete all draft proposals for a lot.
     *
     * @return array{error: ?string, message: ?string}
     */
    public static function eliminarBorradores(?int $loteId): array
    {
        if (! $loteId) {
            return ['error' => 'No se encontró el lote seleccionado.', 'message' => null];
        }

        try {
            $count = PropuestaAsignacion::query()
                ->where('id_lote', $loteId)
                ->where('status', 'draft')
                ->delete();

            if ($count > 0) {
                return ['error' => null, 'message' => "Se eliminaron {$count} recomendación(es) en borrador."];
            }

            return ['error' => null, 'message' => 'No hay recomendaciones en borrador para eliminar.'];
        } catch (\Throwable $e) {
            return ['error' => 'No se pudieron eliminar las recomendaciones.', 'message' => null];
        }
    }

    // =========================================================================
    // Public static helpers (shared across Lotes, LaunchpadModal, services)
    // =========================================================================

    /**
     * Determine if a proposal has low confidence based on its meta.
     */
    public static function esBajaConfianza($meta): bool
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
    public static function cerrarPropuestasCompetidoras(PropuestaAsignacion $proposal): void
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
    public static function buscarEmpleadosOcupados(array $empleadosIds, int $loteActualId): array
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
    public static function buscarMaquinariasOcupadas(array $maquinariasIds, int $loteActualId): array
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
    private static function resolverDestinatariosOrdenCompra(PropuestaAsignacion $proposal): array
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
