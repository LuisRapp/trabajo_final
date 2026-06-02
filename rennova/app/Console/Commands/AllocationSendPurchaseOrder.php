<?php

namespace App\Console\Commands;

use App\Models\AllocationProposal;
use App\Notifications\OrdenCompraPropuestaNotification;
use App\Services\AutomaticAllocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class AllocationSendPurchaseOrder extends Command
{
    protected $signature = 'allocation:send-oc {proposalId} {--to=}';

    protected $description = 'Envía mail de Orden de Compra (OC) para una AllocationProposal (incluye insumos semana 1 con cantidad/costo).';

    public function handle(AutomaticAllocationService $service): int
    {
        $proposalId = (int) $this->argument('proposalId');
        $to = $this->option('to');

        /** @var AllocationProposal|null $proposal */
        $proposal = AllocationProposal::query()
            ->with([
                'lote',
                'loteTarea',
                'proposedEmployees.empleado.rolLaboral',
                'proposedMaquinarias.maquinaria.tipoMaquinaria',
                'proposedInsumos.insumo.unidadMedida',
            ])
            ->find($proposalId);

        if (!$proposal) {
            $this->error('No se encontró la propuesta: ' . $proposalId);
            return self::FAILURE;
        }

        $service->ensureWeek1SupplyEstimates($proposal);
        $proposal->refresh();
        $proposal->load([
            'proposedEmployees.empleado.rolLaboral',
            'proposedMaquinarias.maquinaria.tipoMaquinaria',
            'proposedInsumos.insumo.unidadMedida',
        ]);

        $emails = [];
        if (is_string($to) && trim($to) !== '') {
            $emails = array_values(array_filter(array_map('trim', explode(',', $to))));
        } else {
            $emails = (array) config('mail.purchase_order_emails', []);
            if (empty($emails)) {
                $emails = [config('mail.admin_email', 'admin@example.com')];
            }
        }

        $emails = array_values(array_unique(array_filter($emails)));

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(new OrdenCompraPropuestaNotification($proposal));
        }

        $this->info('OC enviada a: ' . implode(', ', $emails));

        return self::SUCCESS;
    }
}
