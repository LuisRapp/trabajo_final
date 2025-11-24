<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoProgramadoRecordatorio extends Notification
{
    use Queueable;

    protected $mantenimientos;

    public function __construct($mantenimientos)
    {
        $this->mantenimientos = $mantenimientos;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
                    ->subject('🔔 Recordatorio: Mantenimientos Programados para Hoy')
                    ->greeting('Recordatorio de Mantenimientos')
                    ->line('Los siguientes mantenimientos están programados para HOY:')
                    ->line('');

        foreach ($this->mantenimientos as $mant) {
            $message->line("**Orden #{$mant->id_mantenimiento}**")
                    ->line("  • Maquinaria: {$mant->maquinaria->modelo}")
                    ->line("  • Tipo: {$mant->tipoMantenimiento->nombre}")
                    ->line("  • Fecha inicio: {$mant->fecha_inicio}")
                    ->line('');
        }

        $message->line('Por favor, confirme la realización de estos mantenimientos en el sistema.')
                ->action('Ir a Mantenimientos', url('/mantenimientos'))
                ->line('Si no se confirman hoy, se marcarán automáticamente como vencidos.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'count' => $this->mantenimientos->count(),
        ];
    }
}
