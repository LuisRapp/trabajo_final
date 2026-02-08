<?php

namespace App\Notifications;

use App\Models\MantenimientoPurchaseProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrdenCompraMantenimientoNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly MantenimientoPurchaseProposal $proposal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $p = $this->proposal->loadMissing([
            'mantenimiento',
            'maquinaria',
            'insumos.insumo.unidadMedida',
        ]);

        $mantenimientoId = $p->id_mantenimiento;
        $maquinaria = $p->maquinaria?->modelo ?? 'N/A';

        $subject = 'Orden de compra - Mantenimiento #' . $mantenimientoId;

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Orden de Compra (Mantenimiento Preventivo)')
            ->line('Se generó una propuesta de compra por falta de stock en el kit preventivo.')
            ->line('')
            ->line('**Mantenimiento #' . $mantenimientoId . '**')
            ->line('• Maquinaria: ' . $maquinaria)
            ->line('');

        foreach ($p->insumos as $row) {
            $insumo = $row->insumo?->nombre ?? 'Insumo';
            $unidad = $row->insumo?->unidadMedida?->abreviatura ?? '';
            $req = number_format((float) $row->cantidad_requerida, 2);
            $disp = number_format((float) $row->stock_disponible, 2);
            $falt = number_format((float) $row->faltante, 2);
            $linea = "• {$insumo}: requerido {$req} {$unidad} | disponible {$disp} {$unidad} | faltante {$falt} {$unidad}";
            $message->line($linea);
        }

        $message->line('')
            ->action('Ir a Mantenimientos', url('/mantenimientos'));

        return $message;
    }
}
