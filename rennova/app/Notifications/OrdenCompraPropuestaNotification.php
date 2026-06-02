<?php

namespace App\Notifications;

use App\Models\AllocationProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrdenCompraPropuestaNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AllocationProposal $proposal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $p = $this->proposal;

        $subject = 'Orden de compra - Propuesta #' . $p->id_allocation_proposal
            . ' (Lote #' . $p->id_lote . ' - ' . (string) ($p->tipo_tarea ?? '-') . ')';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Orden de Compra (Propuesta Automática)')
            ->line('Se generó una orden de compra estimada a partir de una propuesta confirmada en el sistema.')
            ->line('')
            ->line('**Propuesta #' . $p->id_allocation_proposal . '**')
            ->line('• Lote: #' . $p->id_lote)
            ->line('• Tarea: ' . (string) ($p->tipo_tarea ?? '-'))
            ->line('• Especie: ' . (string) ($p->especie ?? '-'))
            ->line('• Superficie: ' . (string) ($p->superficie_ha ?? '-') . ' ha')
            ->line('');

        $empleados = $p->proposedEmployees
            ?->where('selected', true)
            ->map(fn ($r) => trim((string) (($r->empleado->apellido ?? '') . ' ' . ($r->empleado->nombre ?? ''))))
            ->filter()
            ->values()
            ->all() ?? [];

        if (!empty($empleados)) {
            $message->line('**Personal seleccionado**: ' . implode(', ', $empleados));
        }

        $maqs = $p->proposedMaquinarias
            ?->where('selected', true)
            ->map(fn ($r) => (string) ($r->maquinaria->modelo ?? ''))
            ->filter()
            ->values()
            ->all() ?? [];

        if (!empty($maqs)) {
            $message->line('**Maquinarias seleccionadas**: ' . implode(', ', $maqs));
        }

        $message->line('');

        $insumos = $p->proposedInsumos
            ?->where('selected', true)
            ->values() ?? collect();

        if ($insumos->isEmpty()) {
            $message->line('No hay insumos seleccionados.');
        } else {
            $message->line('**Insumos (semana 1)**:');

            $total = 0.0;
            $hasTotal = false;

            foreach ($insumos as $row) {
                $nombre = (string) ($row->insumo->nombre ?? 'Insumo');
                $unidad = (string) ($row->insumo->unidadMedida->nombre ?? '');

                $cantidad = $row->cantidad_semana_1;
                $costo = $row->costo_estimado_semana_1;

                $line = '• ' . $nombre;
                if ($cantidad !== null) {
                    $line .= ': ' . (string) $cantidad . ($unidad ? (' ' . $unidad) : '');
                } else {
                    $line .= ': (cantidad N/A)';
                }

                if ($costo !== null) {
                    $line .= ' | costo estimado: $' . (string) $costo;
                    $total += (float) $costo;
                    $hasTotal = true;
                } else {
                    $line .= ' | costo estimado: N/A';
                }

                $message->line($line);
            }

            if ($hasTotal) {
                $message->line('');
                $message->line('**Total estimado semana 1**: $' . number_format($total, 2, ',', '.'));
            }
        }

        return $message
            ->line('')
            ->action('Ver propuesta', url('/propuestas-asignacion'))
            ->line('Este mail es una estimación basada en histórico y precios registrados.');
    }
}
