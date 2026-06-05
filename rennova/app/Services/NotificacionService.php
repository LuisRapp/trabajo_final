<?php

namespace App\Services;

use App\Models\NotificacionSistema;

class NotificacionService
{
    /**
     * Mark a notification as read.
     *
     * Sets leida=true and records the timestamp. No-op if already read.
     *
     * @param  \App\Models\NotificacionSistema  $notificacion  The notification to mark as read
     */
    public static function marcarComoLeida(NotificacionSistema $notificacion): void
    {
        if (! $notificacion->leida) {
            $notificacion->update([
                'leida' => true,
                'leida_at' => now(),
            ]);
        }
    }

    /**
     * Mark a notification as actioned (resolved).
     *
     * Sets accionada=true and records the timestamp. Also marks as read if not already.
     * No-op if already actioned.
     *
     * @param  \App\Models\NotificacionSistema  $notificacion  The notification to mark as actioned
     */
    public static function marcarComoAccionada(NotificacionSistema $notificacion): void
    {
        if (! $notificacion->accionada) {
            $notificacion->update([
                'accionada' => true,
                'accionada_at' => now(),
                'leida' => true,
                'leida_at' => $notificacion->leida_at ?? now(),
            ]);
        }
    }
}
