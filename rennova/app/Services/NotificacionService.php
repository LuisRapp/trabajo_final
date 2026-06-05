<?php

namespace App\Services;

use App\Models\NotificacionSistema;

class NotificacionService
{
    /**
     * Marcar una notificación como leída
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
     * Marcar una notificación como accionada
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
