<?php

namespace App\Services;

/**
 * Envío de mails del proceso de mantenimiento.
 *
 * Centraliza la política de envío con reintentos ante rate-limit del
 * servidor de correo y el intervalo mínimo entre envíos consecutivos
 * (compartida por los mails de orden generada y de recordatorios).
 */
class MailMantenimientoService
{
    private const MAX_INTENTOS = 3;

    private const INTERVALO_MINIMO_SEGUNDOS = 1.5;

    private float $ultimoEnvio = 0.0;

    /**
     * Ejecuta el envío respetando el intervalo mínimo y reintentando
     * ante rate-limit (hasta 3 intentos con espera exponencial).
     *
     * @param  callable  $envio  Closure que realiza el envío efectivo
     *
     * @throws \Exception Si el envío falla definitivamente
     */
    public function enviar(callable $envio): void
    {
        $intentos = 0;
        $espera = 2;

        while (true) {
            try {
                $this->esperarIntervalo();
                $envio();

                return;
            } catch (\Exception $e) {
                $intentos++;
                $mensaje = $e->getMessage();
                $esRateLimit = stripos($mensaje, 'Too many emails per second') !== false
                    || stripos($mensaje, '550') !== false;

                if (! $esRateLimit || $intentos >= self::MAX_INTENTOS) {
                    throw $e;
                }

                sleep($espera);
                $espera *= 2;
            }
        }
    }

    /**
     * Espera el intervalo mínimo entre envíos (rate-limit manual compartido
     * entre corridas del proceso vía cache).
     */
    private function esperarIntervalo(): void
    {
        $ahora = microtime(true);
        $ultimoGlobal = cache()->get('mantenimiento_mail_last_sent_at');
        $referencia = max($this->ultimoEnvio, (float) $ultimoGlobal);

        if ($referencia > 0) {
            $delta = $ahora - $referencia;

            if ($delta < self::INTERVALO_MINIMO_SEGUNDOS) {
                usleep((int) ((self::INTERVALO_MINIMO_SEGUNDOS - $delta) * 1000000));
            }
        }

        $this->ultimoEnvio = microtime(true);
        cache()->put('mantenimiento_mail_last_sent_at', $this->ultimoEnvio, 60);
    }
}
