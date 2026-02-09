<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mantenimiento;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use App\Notifications\MantenimientoProgramadoRecordatorio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CheckMantenimientosProgramados extends Command
{
    protected $signature = 'mantenimiento:check-programados';
    protected $description = 'Verifica mantenimientos programados para hoy y marca como vencidos los no confirmados';
    protected float $ultimoEnvioMail = 0.0;

    public function handle()
    {
        $this->info('Verificando mantenimientos programados...');
        
        $hoy = now()->toDateString();
        $limiteAviso = now()->addDays(2)->toDateString();
        
        // Buscar mantenimientos programados para hoy
        $mantenimientosHoy = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])
            ->where('estado', 'programado')
            ->where('fecha_programada', $hoy)
            ->get();

        if ($mantenimientosHoy->count() > 0) {
            $this->info("📅 {$mantenimientosHoy->count()} mantenimiento(s) programado(s) para hoy");
            
            foreach ($mantenimientosHoy as $mant) {
                $this->info("  - Orden #{$mant->id_mantenimiento}: {$mant->maquinaria->modelo}");
            }

        } else {
            $this->info('No hay mantenimientos programados para hoy');
        }

        // Buscar mantenimientos pendientes de programar con fecha limite cercana
        $pendientesProgramar = NotificacionSistema::with(['mantenimiento.maquinaria', 'mantenimiento.tipoMantenimiento'])
            ->where('tipo', 'umbral_alcanzado')
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<=', $limiteAviso)
            ->where(function ($q) {
                $q->where('accionada', false)->orWhereNull('accionada');
            })
            ->orderByDesc('created_at')
            ->get()
            ->unique('mantenimiento_id')
            ->values()
            ->filter(function ($notif) {
                $mant = $notif->mantenimiento;
                return $mant && $mant->estado === 'programado' && empty($mant->fecha_programada);
            });

        if ($pendientesProgramar->count() > 0) {
            $this->info("{$pendientesProgramar->count()} mantenimiento(s) pendientes de programar (limite <= {$limiteAviso})");
        }

        if ($mantenimientosHoy->count() > 0 || $pendientesProgramar->count() > 0) {
            // Enviar recordatorio al administrador
            $this->enviarRecordatorio($mantenimientosHoy, $pendientesProgramar);
        }

        // Marcar como vencidos los mantenimientos que no se confirmaron
        $mantenimientosVencidos = Mantenimiento::where('estado', 'programado')
            ->where('fecha_programada', '<', $hoy)
            ->get();

        if ($mantenimientosVencidos->count() > 0) {
            $this->warn("⚠️  {$mantenimientosVencidos->count()} mantenimiento(s) vencido(s)");
            
            foreach ($mantenimientosVencidos as $mant) {
                $mant->update(['estado' => 'vencido']);
                $this->warn("  - Orden #{$mant->id_mantenimiento} marcada como vencida");
                
                Log::warning("Mantenimiento vencido", [
                    'id_mantenimiento' => $mant->id_mantenimiento,
                    'id_maquinaria' => $mant->id_maquinaria,
                    'fecha_programada' => $mant->fecha_programada
                ]);
            }
        }

        $this->info("\nVerificación completada.");
        return 0;
    }

    protected function enviarRecordatorio($mantenimientos, $pendientesProgramar = null)
    {
        try {
            $pendientesProgramar = $pendientesProgramar ?? collect();

            if ($mantenimientos->isEmpty() && $pendientesProgramar->isEmpty()) {
                return;
            }

            $userIds = DB::table('configuracion_notificaciones_mantenimiento')
                ->where('tipo_notificacion', 'recordatorio')
                ->pluck('user_id');
            
            if ($userIds->isEmpty()) {
                $adminEmail = config('mail.admin_email', 'admin@example.com');
                $this->enviarConReintento(function () use ($adminEmail, $mantenimientos, $pendientesProgramar) {
                    $this->esperarParaEnviarMail();
                    Notification::route('mail', $adminEmail)
                        ->notify(new MantenimientoProgramadoRecordatorio($mantenimientos, $pendientesProgramar));
                });
                $this->info("📧 Recordatorio enviado a {$adminEmail} (fallback)");
            } else {
                $users = Usuario::whereIn('id', $userIds)->get();
                foreach ($users as $user) {
                    $this->enviarConReintento(function () use ($user, $mantenimientos, $pendientesProgramar) {
                        $this->esperarParaEnviarMail();
                        $user->notify(new MantenimientoProgramadoRecordatorio($mantenimientos, $pendientesProgramar));
                    });
                }
                $this->info("📧 Recordatorio enviado a {$users->count()} usuario(s)");
            }
        } catch (\Exception $e) {
            $this->warn("⚠  No se pudo enviar recordatorio: {$e->getMessage()}");
            Log::error("Error enviando recordatorio de mantenimientos", ['error' => $e->getMessage()]);
        }
    }

    protected function enviarConReintento(callable $enviar): void
    {
        $intentos = 0;
        $maxIntentos = 3;
        $espera = 2;

        while (true) {
            try {
                $enviar();
                return;
            } catch (\Exception $e) {
                $intentos++;
                $mensaje = $e->getMessage();
                $esRateLimit = stripos($mensaje, 'Too many emails per second') !== false || stripos($mensaje, '550') !== false;
                if (!$esRateLimit || $intentos >= $maxIntentos) {
                    throw $e;
                }
                sleep($espera);
                $espera *= 2;
            }
        }
    }

    protected function esperarParaEnviarMail(): void
    {
        $minInterval = 1.5;
        $ahora = microtime(true);
        $ultimoGlobal = cache()->get('mantenimiento_mail_last_sent_at');
        $referencia = max((float) $this->ultimoEnvioMail, (float) $ultimoGlobal);

        if ($referencia > 0) {
            $delta = $ahora - $referencia;
            if ($delta < $minInterval) {
                usleep((int)(($minInterval - $delta) * 1000000));
            }
        }

        $this->ultimoEnvioMail = microtime(true);
        cache()->put('mantenimiento_mail_last_sent_at', $this->ultimoEnvioMail, 60);
    }
}
