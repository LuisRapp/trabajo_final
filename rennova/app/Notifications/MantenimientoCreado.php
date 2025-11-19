<?php

namespace App\Notifications;

use App\Models\Mantenimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoCreado extends Notification
{
    use Queueable;

    protected $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mantenimiento = $this->mantenimiento->load(['maquinaria', 'tipoMantenimiento']);
        
        return (new MailMessage)
                    ->subject('Nueva Orden de Mantenimiento Generada')
                    ->greeting('¡Nueva Orden de Mantenimiento!')
                    ->line("Se ha generado automáticamente una orden de mantenimiento preventivo.")
                    ->line("**Orden #:** {$mantenimiento->id_mantenimiento}")
                    ->line("**Maquinaria:** {$mantenimiento->maquinaria->modelo}")
                    ->line("**Tipo:** {$mantenimiento->tipoMantenimiento->nombre}")
                    ->line("**Estado:** {$mantenimiento->estado}")
                    ->action('Ver Orden', url("/mantenimientos"))
                    ->line('Por favor, revise el stock de insumos necesarios.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mantenimiento_id' => $this->mantenimiento->id_mantenimiento,
        ];
    }
}