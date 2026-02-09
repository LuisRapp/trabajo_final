<?php

namespace App\Console\Commands;

use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\KitMantenimientoPreventivo;
use App\Models\KitMantenimientoInsumo;
use App\Models\Mantenimiento;
use App\Models\TipoMantenimiento;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use App\Notifications\MantenimientoCreado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DemoMantenimiento extends Command
{
    protected $signature = 'demo:mantenimiento';
    protected $description = 'Simulación: Crea orden de mantenimiento completa con máquina, kit e notificaciones';

    public function handle()
    {
        $this->info('');
        $this->line('╔════════════════════════════════════════════════════════════════╗');
        $this->line('║          SIMULACIÓN: ORDEN DE MANTENIMIENTO                    ║');
        $this->line('╚════════════════════════════════════════════════════════════════╝');
        $this->info('');

        try {
            DB::beginTransaction();

            // PASO 1: Obtener o crear tipo de máquinaria
            $this->info('📋 PASO 1: Configurando tipo de máquinaria...');
            $tipoMaquinaria = TipoMaquinaria::first();
            if (!$tipoMaquinaria) {
                $tipoMaquinaria = TipoMaquinaria::create([
                    'nombre' => 'Excavadora',
                    'costo_mantenimiento_estimado' => 5000,
                ]);
                $this->line('   ✓ Tipo creado: ' . $tipoMaquinaria->nombre);
            } else {
                $this->line('   ✓ Usando tipo existente: ' . $tipoMaquinaria->nombre);
            }

            // PASO 2: Crear una máquinaria de prueba
            $this->info("\n🏗️  PASO 2: Creando máquinaria...");
            $maquinaria = Maquinaria::create([
                'id_tipo_maquinaria' => $tipoMaquinaria->id_tipo_maquinaria,
                'modelo' => 'CAT 320 - Simulación ' . now()->format('Y-m-d H:i:s'),
                'estado' => 'operativa',
                'es_alquilada' => false,
                'fecha_inicio_actividades' => now()->subMonths(6),
                'toneladas_acumuladas' => 500,
                'umbral_toneladas' => 400,
            ]);
            $this->line('   ✓ Máquinaria creada: ID ' . $maquinaria->id_maquinaria . ' - ' . $maquinaria->modelo);
            $this->line('   • Toneladas acumuladas: ' . $maquinaria->toneladas_acumuladas);
            $this->line('   • Umbral: ' . $maquinaria->umbral_toneladas);

            // PASO 3: Crear o usar kit de mantenimiento
            $this->info("\n🔧 PASO 3: Configurando kit de mantenimiento...");
            
            // Obtener insumos existentes o crear nuevos
            $insumos = Insumo::limit(2)->get();
            
            if ($insumos->count() == 0) {
                // Si no hay insumos, crear algunos
                $unidad = UnidadMedida::first() ?? UnidadMedida::create(['nombre' => 'Unitario', 'abreviatura' => 'u']);
                
                $insumo1 = Insumo::create([
                    'nombre' => 'Aceite Hidráulico',
                    'id_unidad_medida' => $unidad->id_unidad_medida,
                    'descripcion' => 'Aceite de calidad premium'
                ]);
                $insumos = collect([$insumo1]);
            }
            
            $this->line('   ✓ Se utilizarán ' . $insumos->count() . ' insumos existentes');
            foreach ($insumos as $insumo) {
                $this->line('     - ' . $insumo->nombre);
            }

            // PASO 4: Obtener tipo de mantenimiento
            $this->info("\n📝 PASO 4: Seleccionando tipo de mantenimiento...");
            $tipoMantenimiento = TipoMantenimiento::where('nombre', 'Preventivo')->first();
            if (!$tipoMantenimiento) {
                $tipoMantenimiento = TipoMantenimiento::create(['nombre' => 'Preventivo']);
            }
            $this->line('   ✓ Tipo: ' . $tipoMantenimiento->nombre);

            // PASO 5: Crear orden de mantenimiento
            $this->info("\n🔨 PASO 5: Creando orden de mantenimiento...");
            $mantenimiento = Mantenimiento::create([
                'id_maquinaria' => $maquinaria->id_maquinaria,
                'id_tipo_mantenimiento' => $tipoMantenimiento->id_tipo_mantenimiento,
                'fecha_inicio' => now(),
                'fecha_programada' => now()->addDays(3),
                'estado' => 'programado',
                'toneladas_snapshot' => $maquinaria->toneladas_acumuladas,
                'costo_total' => 0,
            ]);
            $this->line('   ✓ Orden creada: ID ' . $mantenimiento->id_mantenimiento);
            $this->line('   • Estado: ' . $mantenimiento->estado);
            $this->line('   • Fecha programada: ' . $mantenimiento->fecha_programada->format('d/m/Y H:i'));

            // PASO 6: Enviar notificación por email
            $this->info("\n📧 PASO 6: Enviando notificación por email...");
            try {
                $admin = Usuario::where('email', 'admin@rennova.local')->first();
                if (!$admin) {
                    $admin = Usuario::first();
                }
                
                if ($admin) {
                    Notification::send($admin, new MantenimientoCreado($mantenimiento));
                    $this->line('   ✓ Email enviado a: ' . $admin->email);
                } else {
                    $this->warn('   ⚠ No hay usuarios para enviar notificación');
                }
            } catch (\Exception $e) {
                $this->warn('   ⚠ Error al enviar email: ' . $e->getMessage());
            }

            // PASO 7: Crear notificación interna del sistema
            $this->info("\n🔔 PASO 7: Creando notificación interna...");
            
            $usuarios = Usuario::where('email', '!=', null)->limit(3)->get();
            foreach ($usuarios as $usuario) {
                NotificacionSistema::create([
                    'user_id' => $usuario->id,
                    'tipo' => 'recordatorio_programado',
                    'titulo' => 'Nueva Orden de Mantenimiento',
                    'mensaje' => "Se ha generado una nueva orden de mantenimiento para la máquina {$maquinaria->modelo}. Maquinaria ha acumulado {$maquinaria->toneladas_acumuladas} toneladas.",
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'fecha_limite' => now()->addDays(3),
                    'leida' => false,
                ]);
                $this->line('   ✓ Notificación enviada a: ' . $usuario->email);
            }

            DB::commit();

            $this->info('');
            $this->line(str_repeat('═', 66));
            $this->info('✅ SIMULACIÓN COMPLETADA EXITOSAMENTE');
            $this->line(str_repeat('═', 66));
            $this->info('');
            $this->line('RESUMEN:');
            $this->line('  • Máquinaria: ' . $maquinaria->modelo);
            $this->line('  • Orden ID: ' . $mantenimiento->id_mantenimiento);
            $this->line('  • Insumos utilizados: ' . $insumos->count());
            $this->line('  • Emails enviados: 1');
            $this->line('  • Notificaciones internas: ' . count($usuarios));
            $this->info('');
            $this->line('Puedes revisar:');
            $this->line('  1. El email enviado en MailHog (http://localhost:8025)');
            $this->line('  2. Las notificaciones en el dashboard del sistema');
            $this->line('  3. El registro de auditoría');
            $this->info('');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('');
            $this->error('❌ ERROR DURANTE LA SIMULACIÓN:');
            $this->error('   ' . $e->getMessage());
            $this->error('   En: ' . $e->getFile() . ':' . $e->getLine());
            $this->error('');
            return 1;
        }

        return 0;
    }
}
