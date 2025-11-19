<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockInsuficiente extends Notification
{
    use Queueable;

    protected $advertencias;

    public function __construct(array $advertencias)
    {
        $this->advertencias = $advertencias;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
                    ->subject('⚠️ Advertencia: Stock Insuficiente para Mantenimientos')
                    ->greeting('Advertencia de Stock')
                    ->line('Se han generado órdenes de mantenimiento pero falta stock de algunos insumos:')
                    ->line('');

        foreach ($this->advertencias as $adv) {
            $message->line("**Orden #{$adv['orden']}** - Maquinaria {$adv['maquinaria']}");
            foreach ($adv['insumos'] as $ins) {
                $message->line("  • {$ins['insumo']}: Faltan {$ins['faltante']} unidades (disponible: {$ins['disponible']})");
            }
            $message->line('');
        }

        $message->line('Por favor, gestione la compra de estos insumos antes de aprobar las órdenes.')
                ->action('Ver Órdenes', url('/mantenimientos'))
                ->line('Este es un mensaje automático del sistema.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'advertencias_count' => count($this->advertencias),
        ];
    }
}