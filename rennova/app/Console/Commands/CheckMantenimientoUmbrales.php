<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maquinaria;
use App\Models\Mantenimiento;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Insumo;
use App\Models\User;
use App\Notifications\MantenimientoCreado;
use App\Notifications\StockInsuficiente;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckMantenimientoUmbrales extends Command
{
    protected $signature = 'mantenimiento:check-umbrales';
    protected $description = 'Verifica umbrales de toneladas y genera órdenes de mantenimiento preventivo';

    public function handle()
    {
        $this->info('Iniciando verificación de umbrales de mantenimiento...');
        
        $maquinarias = Maquinaria::with(['tipoMaquinaria'])->where('estado', 'operativa')->get();
        $ordenesGeneradas = 0;
        $advertenciasStock = [];

        foreach ($maquinarias as $maquinaria) {
            // Verificar si la maquinaria tiene umbral configurado
            if (!$maquinaria->umbral_toneladas) {
                continue;
            }

            // Obtener el snapshot del último mantenimiento preventivo
            $ultimoMantenimiento = Mantenimiento::where('id_maquinaria', $maquinaria->id_maquinaria)
                ->whereNotNull('toneladas_snapshot')
                ->orderBy('fecha_fin', 'desc')
                ->first();

            $toneladasDesdeUltimo = $ultimoMantenimiento 
                ? ($maquinaria->toneladas_acumuladas - $ultimoMantenimiento->toneladas_snapshot)
                : $maquinaria->toneladas_acumuladas;

            $umbral = $maquinaria->umbral_toneladas;

            // Verificar si supera el umbral
            if ($toneladasDesdeUltimo >= $umbral) {
                // Verificar que no exista ya una orden pendiente o en curso
                $ordenPendiente = Mantenimiento::where('id_maquinaria', $maquinaria->id_maquinaria)
                    ->whereIn('estado', ['programado', 'en curso'])
                    ->exists();

                if ($ordenPendiente) {
                    $this->warn("Maquinaria {$maquinaria->id_maquinaria} ya tiene orden pendiente");
                    continue;
                }

                // Obtener el tipo de mantenimiento "Preventivo"
                $tipoPreventivo = \App\Models\TipoMantenimiento::where('nombre', 'LIKE', '%preventivo%')
                    ->where('activo', true)
                    ->first();

                if (!$tipoPreventivo) {
                    $this->error("No existe tipo de mantenimiento preventivo configurado");
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Crear orden de mantenimiento
                    $mantenimiento = Mantenimiento::create([
                        'id_maquinaria' => $maquinaria->id_maquinaria,
                        'id_tipo_mantenimiento' => $tipoPreventivo->id_tipo_mantenimiento,
                        'fecha_inicio' => now()->toDateString(),
                        'estado' => 'programado'
                    ]);

                    // Obtener kit de insumos preventivos de la maquinaria específica
                    $kit = KitMantenimientoPreventivo::where('id_maquinaria', $maquinaria->id_maquinaria)
                        ->whereNull('deleted_at')
                        ->with('insumo')
                        ->get();

                    $faltaStock = false;
                    $insumosConProblema = [];

                    foreach ($kit as $item) {
                        $insumo = $item->insumo;
                        $stockDisponible = $insumo->stock_disponible ?? 0;

                        if ($stockDisponible < $item->cantidad_requerida) {
                            $faltaStock = true;
                            $insumosConProblema[] = [
                                'insumo' => $insumo->nombre,
                                'requerido' => $item->cantidad_requerida,
                                'disponible' => $stockDisponible,
                                'faltante' => $item->cantidad_requerida - $stockDisponible
                            ];
                        }
                    }

                    DB::commit();

                    $ordenesGeneradas++;
                    $this->info("✓ Orden creada para Maquinaria {$maquinaria->id_maquinaria} (ID orden: {$mantenimiento->id_mantenimiento})");

                    // Enviar notificación por email
                    $this->enviarNotificacion(new MantenimientoCreado($mantenimiento));

                    if ($faltaStock) {
                        $advertenciasStock[] = [
                            'maquinaria' => $maquinaria->id_maquinaria,
                            'orden' => $mantenimiento->id_mantenimiento,
                            'insumos' => $insumosConProblema
                        ];
                        $this->warn("⚠ ADVERTENCIA: Falta stock para algunos insumos");
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error creando orden para maquinaria {$maquinaria->id_maquinaria}: {$e->getMessage()}");
                    Log::error("Error en CheckMantenimientoUmbrales", [
                        'maquinaria' => $maquinaria->id_maquinaria,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info("\n=== RESUMEN ===");
        $this->info("Órdenes generadas: {$ordenesGeneradas}");
        
        if (count($advertenciasStock) > 0) {
            $this->warn("\nADVERTENCIAS DE STOCK:");
            foreach ($advertenciasStock as $adv) {
                $this->warn("Orden #{$adv['orden']} - Maquinaria {$adv['maquinaria']}:");
                foreach ($adv['insumos'] as $ins) {
                    $this->warn("  - {$ins['insumo']}: Faltan {$ins['faltante']} unidades");
                }
            }

            // TODO: Aquí se enviará notificación por email cuando implementes la librería
            Log::warning("Órdenes creadas con falta de stock", [
                'advertencias' => $advertenciasStock
            ]);
            
            // Enviar notificación de stock insuficiente
            $this->enviarNotificacion(new StockInsuficiente($advertenciasStock));
        }

        $this->info("\nVerificación completada.");
        return 0;
    }

    /**
     * Envía notificación a administradores
     */
    protected function enviarNotificacion($notification)
    {
        try {
            // Obtener email del administrador desde config o primer usuario
            $adminEmail = config('mail.admin_email', 'admin@example.com');
            
            // Alternativa: Notificar a todos los usuarios con rol admin
            // $admins = User::role('admin')->get();
            // Notification::send($admins, $notification);
            
            // Por ahora usar email directo
            Notification::route('mail', $adminEmail)->notify($notification);
            
            $this->info("📧 Notificación enviada a {$adminEmail}");
        } catch (\Exception $e) {
            $this->warn("⚠ No se pudo enviar notificación: {$e->getMessage()}");
            Log::error("Error enviando notificación", ['error' => $e->getMessage()]);
        }
    }
}
