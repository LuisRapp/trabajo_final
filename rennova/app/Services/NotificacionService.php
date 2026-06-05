<?php

namespace App\Services;

use App\Models\NotificacionSistema;
use Illuminate\Support\Facades\DB;

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

    /**
     * Load maintenance notification configuration.
     *
     * Reads the configuracion_notificaciones_mantenimiento table and returns
     * user IDs grouped by notification type.
     *
     * @return array{umbral: array<int>, recordatorio: array<int>, stock: array<int>}
     */
    public function cargarConfiguracionMantenimiento(): array
    {
        $config = DB::table('configuracion_notificaciones_mantenimiento')->get();

        return [
            'umbral' => $config->where('tipo_notificacion', 'umbral')->pluck('user_id')->toArray(),
            'recordatorio' => $config->where('tipo_notificacion', 'recordatorio')->pluck('user_id')->toArray(),
            'stock' => $config->where('tipo_notificacion', 'stock')->pluck('user_id')->toArray(),
        ];
    }

    /**
     * Save maintenance notification configuration.
     *
     * Replaces the entire configuracion_notificaciones_mantenimiento table
     * with the provided user IDs per notification type, within a transaction.
     *
     * @param  array<int>  $usuariosUmbral  User IDs for threshold notifications
     * @param  array<int>  $usuariosRecordatorio  User IDs for reminder notifications
     * @param  array<int>  $usuariosStock  User IDs for stock notifications
     */
    public function guardarConfiguracionMantenimiento(array $usuariosUmbral, array $usuariosRecordatorio, array $usuariosStock): void
    {
        DB::transaction(function () use ($usuariosUmbral, $usuariosRecordatorio, $usuariosStock) {
            DB::table('configuracion_notificaciones_mantenimiento')->delete();

            $this->insertarConfiguracion($usuariosUmbral, 'umbral');
            $this->insertarConfiguracion($usuariosRecordatorio, 'recordatorio');
            $this->insertarConfiguracion($usuariosStock, 'stock');
        });
    }

    /**
     * Insert configuration rows for a notification type.
     *
     * @param  array<int>  $userIds
     */
    private function insertarConfiguracion(array $userIds, string $tipoNotificacion): void
    {
        foreach ($userIds as $userId) {
            DB::table('configuracion_notificaciones_mantenimiento')->insert([
                'user_id' => $userId,
                'tipo_notificacion' => $tipoNotificacion,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
