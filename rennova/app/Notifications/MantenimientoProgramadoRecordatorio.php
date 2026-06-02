<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoProgramadoRecordatorio extends Notification
{
    use Queueable;

    protected $mantenimientos;
    protected $pendientesProgramar;

    public function __construct($mantenimientos, $pendientesProgramar = null)
    {
        $this->mantenimientos = $mantenimientos;
        $this->pendientesProgramar = $pendientesProgramar ?? collect();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hayProgramados = $this->mantenimientos && $this->mantenimientos->count() > 0;
        $hayPendientes = $this->pendientesProgramar && $this->pendientesProgramar->count() > 0;

        $subject = 'Recordatorio de Mantenimientos';
        if ($hayProgramados && $hayPendientes) {
            $subject = 'Recordatorio: Mantenimientos de hoy y pendientes';
        } elseif ($hayProgramados) {
            $subject = 'Recordatorio: Mantenimientos Programados para Hoy';
        } elseif ($hayPendientes) {
            $subject = 'Recordatorio: Mantenimientos Pendientes de Programar';
        }

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Recordatorio de Mantenimientos');

        if ($hayProgramados) {
            $message->line('Los siguientes mantenimientos estan programados para HOY:')
                ->line('');

            foreach ($this->mantenimientos as $mant) {
                $message->line("**Orden #{$mant->id_mantenimiento}**")
                    ->line("  - Maquinaria: {$mant->maquinaria->modelo}")
                    ->line("  - Tipo: {$mant->tipoMantenimiento->nombre}")
                    ->line("  - Fecha inicio: {$mant->fecha_inicio}")
                    ->line('');
            }
        }

        if ($hayPendientes) {
            $message->line('Los siguientes mantenimientos estan pendientes de programar (fecha limite cercana):')
                ->line('');

            foreach ($this->pendientesProgramar as $notif) {
                $mant = $notif->mantenimiento;
                if (!$mant) {
                    continue;
                }
                $fechaLimite = $notif->fecha_limite ? $notif->fecha_limite->format('d/m/Y') : 'N/A';
                $message->line("**Orden #{$mant->id_mantenimiento}**")
                    ->line("  - Maquinaria: {$mant->maquinaria->modelo}")
                    ->line("  - Tipo: {$mant->tipoMantenimiento->nombre}")
                    ->line("  - Fecha limite: {$fechaLimite}")
                    ->line('');
            }
        }

        if ($hayProgramados) {
            $message->line('Por favor, confirme la realizacion de estos mantenimientos en el sistema.')
                ->line('Si no se confirman hoy, se marcaran automaticamente como vencidos.');
        }

        if ($hayPendientes) {
            $message->line('Por favor, programe la fecha de inicio antes de la fecha limite.');
        }

        $message->action('Ir a Mantenimientos', url('/mantenimientos'));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'count' => $this->mantenimientos->count(),
        ];
    }
}
