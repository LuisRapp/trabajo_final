<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mantenimiento;
use App\Models\User;
use App\Notifications\MantenimientoProgramadoRecordatorio;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CheckMantenimientosProgramados extends Command
{
    protected $signature = 'mantenimiento:check-programados';
    protected $description = 'Verifica mantenimientos programados para hoy y marca como vencidos los no confirmados';

    public function handle()
    {
        $this->info('Verificando mantenimientos programados...');
        
        $hoy = now()->toDateString();
        
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

            // Enviar recordatorio al administrador
            $this->enviarRecordatorio($mantenimientosHoy);
        } else {
            $this->info('No hay mantenimientos programados para hoy');
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

    protected function enviarRecordatorio($mantenimientos)
    {
        try {
            $userIds = DB::table('configuracion_notificaciones_mantenimiento')
                ->where('tipo_notificacion', 'recordatorio')
                ->pluck('user_id');
            
            if ($userIds->isEmpty()) {
                $adminEmail = config('mail.admin_email', 'admin@example.com');
                Notification::route('mail', $adminEmail)
                    ->notify(new MantenimientoProgramadoRecordatorio($mantenimientos));
                $this->info("📧 Recordatorio enviado a {$adminEmail} (fallback)");
            } else {
                $users = User::whereIn('id', $userIds)->get();
                Notification::send($users, new MantenimientoProgramadoRecordatorio($mantenimientos));
                $this->info("📧 Recordatorio enviado a {$users->count()} usuario(s)");
            }
        } catch (\Exception $e) {
            $this->warn("⚠  No se pudo enviar recordatorio: {$e->getMessage()}");
            Log::error("Error enviando recordatorio de mantenimientos", ['error' => $e->getMessage()]);
        }
    }
}
