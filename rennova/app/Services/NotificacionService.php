<?php

namespace App\Services;

use App\Models\NotificacionSistema;
use Illuminate\Support\Facades\DB;

class NotificacionService
{
    /**
     * Marca una notificación como leída.
     *
     * Establece leida=true y registra la marca de hora. No opera si ya estaba leída.
     *
     * @param  \App\Models\NotificacionSistema  $notificacion  Notificación a marcar como leída
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
     * Marca una notificación como accionada (resuelta).
     *
     * Establece accionada=true y registra la marca de hora. También la marca como leída si no lo estaba.
     * No opera si ya estaba accionada.
     *
     * @param  \App\Models\NotificacionSistema  $notificacion  Notificación a marcar como accionada
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
     * Carga la configuración de notificaciones de mantenimiento.
     *
     * Lee la tabla configuracion_notificaciones_mantenimiento y devuelve
     * los identificadores de usuario agrupados por tipo de notificación.
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
     * Guarda la configuración de notificaciones de mantenimiento.
     *
     * Reemplaza toda la tabla configuracion_notificaciones_mantenimiento
     * con los usuarios provistos por tipo de notificación, dentro de una transacción.
     *
     * @param  array<int>  $usuariosUmbral  Usuarios para notificaciones de umbral
     * @param  array<int>  $usuariosRecordatorio  Usuarios para notificaciones de recordatorio
     * @param  array<int>  $usuariosStock  Usuarios para notificaciones de stock
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
     * Inserta las filas de configuración para un tipo de notificación.
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
