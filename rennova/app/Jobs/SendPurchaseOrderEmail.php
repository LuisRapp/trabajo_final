<?php

namespace App\Jobs;

use App\Models\AllocationProposal;
use App\Notifications\OrdenCompraPropuestaNotification;
use App\Services\AutomaticAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPurchaseOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $proposalId)
    {
    }

    public function handle(): void
    {
        $proposal = AllocationProposal::query()
            ->with([
                'lote',
                'loteTarea',
                'proposedEmployees.empleado.rolLaboral',
                'proposedMaquinarias.maquinaria.tipoMaquinaria',
                'proposedInsumos.insumo.unidadMedida',
            ])
            ->find($this->proposalId);

        if (!$proposal) {
            return;
        }

        $meta = $proposal->meta ?? [];
        if (!empty($meta['purchase_order']['sent_at'] ?? null)) {
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

    private function resolvePurchaseOrderRecipients(AllocationProposal $proposal): array
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
