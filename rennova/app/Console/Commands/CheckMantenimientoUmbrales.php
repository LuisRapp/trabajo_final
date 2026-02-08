<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maquinaria;
use App\Models\Mantenimiento;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Insumo;
use App\Models\User;
use App\Models\NotificacionSistema;
use App\Models\MantenimientoPurchaseProposal;
use App\Models\MantenimientoPurchaseProposalInsumo;
use App\Models\TipoMantenimiento;
use App\Notifications\MantenimientoCreado;
use App\Notifications\StockInsuficiente;
use App\Notifications\OrdenCompraMantenimientoNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckMantenimientoUmbrales extends Command
{
    protected $signature = 'mantenimiento:check-umbrales {--maquinaria=} {--simular}';
    protected $description = 'Verifica umbrales de toneladas y genera órdenes de mantenimiento preventivo';
    protected float $ultimoEnvioMail = 0.0;

    public function handle()
    {
        // Opciones de simulación para presentaciones
        $maquinariaIdOpt = $this->option('maquinaria');
        $simular = (bool)$this->option('simular');

        if ($maquinariaIdOpt && $simular) {
            try {
                $maq = Maquinaria::find($maquinariaIdOpt);
                if ($maq) {
                    if ($maq->toneladas_acumuladas < $maq->umbral_toneladas) {
                        $maq->toneladas_acumuladas = $maq->umbral_toneladas + 5;
                        $maq->save();
                        $this->info("Simulacion: Maquinaria {$maq->id_maquinaria} supera umbral ({$maq->toneladas_acumuladas}/{$maq->umbral_toneladas})");
                    } else {
                        $this->info("Simulacion: Maquinaria {$maq->id_maquinaria} ya supera el umbral");
                    }
                } else {
                    $this->warn("No se encontro la maquinaria ID {$maquinariaIdOpt} para simular");
                }
            } catch (\Throwable $e) {
                $this->warn("No se pudo simular umbral: {$e->getMessage()}");
            }
        }
        $this->info('Iniciando verificación de umbrales de mantenimiento...');

        // Camino seguro para presentaciones: si se pidió simular con maquinaria específica,
        // generar una orden inmediatamente y notificar.
        $ordenesGeneradas = 0;
        if ($maquinariaIdOpt && $simular) {
            try {
                $maquinaria = Maquinaria::find($maquinariaIdOpt);
                if ($maquinaria) {
                    DB::beginTransaction();
                    // Seleccionar tipo preventivo
                    $tipoPreventivo = TipoMantenimiento::where('nombre', 'Preventivo')->first();
                    if (!$tipoPreventivo) {
                        $tipoPreventivo = TipoMantenimiento::first();
                    }

                    // Crear la orden programada
                    $mantenimiento = Mantenimiento::create([
                        'id_maquinaria' => $maquinaria->id_maquinaria,
                        'id_tipo_mantenimiento' => $tipoPreventivo?->id_tipo_mantenimiento,
                        'fecha_inicio' => now(),
                        'estado' => 'programado',
                        'costo_total' => 0
                    ]);

                    // Notificación por email
                    $this->enviarNotificacion(new \App\Notifications\MantenimientoCreado($mantenimiento));

                    // Notificación interna
                    $this->crearNotificacionInterna(
                        mantenimiento: $mantenimiento,
                        maquinaria: $maquinaria,
                        toneladasDesdeUltimo: ($maquinaria->toneladas_acumuladas ?? 0)
                    );

                    DB::commit();
                    $ordenesGeneradas++;
                    $this->info("✓ Orden creada por simulación (ID: {$mantenimiento->id_mantenimiento})");
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->warn("No se pudo crear orden por simulación: {$e->getMessage()}");
            }
        }
        
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
                $tipoPreventivo = \App\Models\TipoMantenimiento::where('nombre', 'ILIKE', '%preventivo%')
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

                    // Obtener kit de insumos preventivos (prioriza kit por maquinaria)
                    $kit = KitMantenimientoPreventivo::where('id_maquinaria', $maquinaria->id_maquinaria)
                        ->whereNull('deleted_at')
                        ->with('insumo')
                        ->get();

                    if ($kit->isEmpty()) {
                        $kit = KitMantenimientoPreventivo::where('id_tipo_maquinaria', $maquinaria->id_tipo_maquinaria)
                            ->whereNull('deleted_at')
                            ->with('insumo')
                            ->get();
                    }

                    $faltaStock = false;
                    $insumosConProblema = [];

                    foreach ($kit as $item) {
                        $insumo = $item->insumo;
                        $stockDisponible = $insumo?->stock ?? 0;

                        if ($stockDisponible < $item->cantidad_requerida) {
                            $faltaStock = true;
                            $insumosConProblema[] = [
                                'id_insumo' => $insumo->id_insumo,
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

                    // Enviar notificacion por email
                    $notification = $faltaStock
                        ? new MantenimientoCreado($mantenimiento, $insumosConProblema)
                        : new MantenimientoCreado($mantenimiento);
                    $this->enviarNotificacion($notification);

                    // Crear notificación interna del sistema con plazo de 7 días
                    $this->crearNotificacionInterna(
                        mantenimiento: $mantenimiento,
                        maquinaria: $maquinaria,
                        toneladasDesdeUltimo: $toneladasDesdeUltimo
                    );

                    if ($faltaStock) {
                        $advertenciasStock[] = [
                            'maquinaria' => $maquinaria->id_maquinaria,
                            'orden' => $mantenimiento->id_mantenimiento,
                            'insumos' => $insumosConProblema
                        ];
                        $this->crearPropuestaCompraMantenimiento($mantenimiento, $insumosConProblema);
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
            
            $this->enviarNotificacion(new StockInsuficiente($advertenciasStock));
        }

        $this->info("\nVerificación completada.");
        return 0;
    }

    /**
     * Envía notificación a usuarios configurados
     */
    protected function enviarNotificacion($notification)
    {
        try {
            // Obtener usuarios configurados según tipo de notificación
            $tipoNotificacion = $notification instanceof \App\Notifications\StockInsuficiente ? 'stock' : 'umbral';
            
            $userIds = DB::table('configuracion_notificaciones_mantenimiento')
                ->where('tipo_notificacion', $tipoNotificacion)
                ->pluck('user_id');
            
            if ($userIds->isEmpty()) {
                // Fallback: enviar a admin_email configurado
                $adminEmail = config('mail.admin_email', 'admin@example.com');
                $this->enviarConReintento(function () use ($adminEmail, $notification) {
                    $this->esperarParaEnviarMail();
                    Notification::route('mail', $adminEmail)->notify($notification);
                });
                $this->info("Notificacion enviada a {$adminEmail} (fallback)");
            } else {
                $emails = User::whereIn('id', $userIds)->pluck('email')->filter()->values()->all();
                if (empty($emails)) {
                    $adminEmail = config('mail.admin_email', 'admin@example.com');
                    $this->enviarConReintento(function () use ($adminEmail, $notification) {
                        $this->esperarParaEnviarMail();
                        Notification::route('mail', $adminEmail)->notify($notification);
                    });
                    $this->info("Notificacion enviada a {$adminEmail} (fallback)");
                } else {
                    $this->enviarConReintento(function () use ($emails, $notification) {
                        $this->esperarParaEnviarMail();
                        Notification::route('mail', $emails)->notify($notification);
                    });
                    $this->info("Notificacion enviada a " . count($emails) . " usuario(s)");
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠ No se pudo enviar notificación: {$e->getMessage()}");
            Log::error("Error enviando notificación", ['error' => $e->getMessage()]);
        }
    }

    protected function crearPropuestaCompraMantenimiento(Mantenimiento $mantenimiento, array $insumosConProblema): void
    {
        if (empty($insumosConProblema)) {
            return;
        }

        $proposal = MantenimientoPurchaseProposal::firstOrCreate(
            ['id_mantenimiento' => $mantenimiento->id_mantenimiento],
            [
                'id_maquinaria' => $mantenimiento->id_maquinaria,
                'status' => 'pending',
            ]
        );

        if ($proposal->id_maquinaria !== $mantenimiento->id_maquinaria) {
            $proposal->id_maquinaria = $mantenimiento->id_maquinaria;
        }

        MantenimientoPurchaseProposalInsumo::where('id_mantenimiento_purchase_proposal', $proposal->id_mantenimiento_purchase_proposal)
            ->delete();

        foreach ($insumosConProblema as $ins) {
            if (empty($ins['id_insumo'])) {
                continue;
            }
            MantenimientoPurchaseProposalInsumo::create([
                'id_mantenimiento_purchase_proposal' => $proposal->id_mantenimiento_purchase_proposal,
                'id_insumo' => $ins['id_insumo'],
                'cantidad_requerida' => (float) ($ins['requerido'] ?? 0),
                'stock_disponible' => (float) ($ins['disponible'] ?? 0),
                'faltante' => (float) ($ins['faltante'] ?? 0),
            ]);
        }

        $meta = $proposal->meta ?? [];
        if (!empty($meta['purchase_order']['sent_at'] ?? null)) {
            $proposal->save();
            return;
        }

        $emails = array_values(array_filter((array) config('mail.purchase_order_emails', [])));
        if (empty($emails)) {
            $emails = [config('mail.admin_email', 'admin@example.com')];
        }

        $this->enviarConReintento(function () use ($emails, $proposal) {
            $this->esperarParaEnviarMail();
            Notification::route('mail', $emails)->notify(new OrdenCompraMantenimientoNotification($proposal));
        });

        $meta['purchase_order'] = [
            'sent_at' => now()->toISOString(),
            'recipients' => $emails,
        ];
        $proposal->meta = $meta;
        $proposal->status = 'sent';
        $proposal->save();
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

    /**
     * Crea notificación interna en el sistema para usuarios configurados
     */
    protected function crearNotificacionInterna(Mantenimiento $mantenimiento, Maquinaria $maquinaria, float $toneladasDesdeUltimo)
    {
        try {
            // Obtener usuarios configurados para notificaciones de umbral
            $userIds = DB::table('configuracion_notificaciones_mantenimiento')
                ->where('tipo_notificacion', 'umbral')
                ->pluck('user_id');
            
            if ($userIds->isEmpty()) {
                $this->warn("⚠ No hay usuarios configurados para recibir notificaciones de umbral");
                return;
            }

            $fechaLimite = now()->addDays(7)->toDateString();
            $titulo = "Mantenimiento Preventivo Requerido - {$maquinaria->id_maquinaria}";
            $mensaje = "La maquinaria {$maquinaria->id_maquinaria} ha alcanzado {$toneladasDesdeUltimo} toneladas " .
                      "(umbral: {$maquinaria->umbral_toneladas}). " .
                      "Se ha generado la orden de mantenimiento #{$mantenimiento->id_mantenimiento}. " .
                      "Por favor, programe la fecha de inicio dentro de los próximos 7 días.";

            foreach ($userIds as $userId) {
                NotificacionSistema::create([
                    'user_id' => $userId,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'umbral_alcanzado',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => $fechaLimite,
                ]);
            }

            $this->info("🔔 Notificación interna creada para {$userIds->count()} usuario(s) (límite: {$fechaLimite})");

        } catch (\Exception $e) {
            $this->warn("⚠ Error creando notificación interna: {$e->getMessage()}");
            Log::error("Error en crearNotificacionInterna", ['error' => $e->getMessage()]);
        }
    }
}
