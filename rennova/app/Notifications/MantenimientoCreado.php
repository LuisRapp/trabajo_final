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
    protected $faltantes;

    public function __construct(Mantenimiento $mantenimiento, array $faltantes = [])
    {
        $this->mantenimiento = $mantenimiento;
        $this->faltantes = $faltantes;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mantenimiento = $this->mantenimiento->load(['maquinaria', 'tipoMantenimiento']);

        $message = (new MailMessage)
            ->subject('Nueva Orden de Mantenimiento Generada')
            ->greeting('Nueva Orden de Mantenimiento')
            ->line('Se ha generado automaticamente una orden de mantenimiento preventivo.')
            ->line("Orden #: {$mantenimiento->id_mantenimiento}")
            ->line("Maquinaria: {$mantenimiento->maquinaria->modelo}")
            ->line("Tipo: {$mantenimiento->tipoMantenimiento->nombre}")
            ->line("Estado: {$mantenimiento->estado}")
            ->line('Por favor, revise el stock de insumos necesarios.');

        if (!empty($this->faltantes)) {
            $message->line('')
                ->line('Insumos faltantes detectados:');

            foreach ($this->faltantes as $ins) {
                $nombre = $ins['insumo'] ?? 'Insumo';
                $faltante = number_format((float) ($ins['faltante'] ?? 0), 2);
                $disponible = number_format((float) ($ins['disponible'] ?? 0), 2);
                $message->line(" - {$nombre}: faltan {$faltante} (disponible {$disponible})");
            }
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mantenimiento_id' => $this->mantenimiento->id_mantenimiento,
        ];
    }
}
