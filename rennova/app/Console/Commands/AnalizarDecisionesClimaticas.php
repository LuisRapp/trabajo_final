<?php

namespace App\Console\Commands;

use App\Models\DecisionClima;
use App\Models\Lote;
use App\Models\NotificacionSistema;
use App\Services\ClimaDecisionService;
use App\Services\NotificacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AnalizarDecisionesClimaticas extends Command
{
    /**
     * Nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'clima:decisiones {--lote= : ID del lote a analizar (opcional)}';

    /**
     * Descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Analiza el clima y genera recomendaciones operativas inteligentes (Anticipación/Reacción)';

    protected ClimaDecisionService $climaService;

    public function __construct(ClimaDecisionService $climaService)
    {
        parent::__construct();
        $this->climaService = $climaService;
    }

    /**
     * Ejecutar el comando de consola.
     */
    public function handle()
    {
        $this->info('🌦️  Sistema de Decisiones Climáticas Inteligentes');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // Filtrar por lote específico o analizar todos
        $loteId = $this->option('lote');

        if ($loteId) {
            $lotes = Lote::where('id_lote', $loteId)
                ->whereIn('estado', ['activo', 'en_proceso'])
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get();
        } else {
            $lotes = Lote::whereIn('estado', ['activo', 'en_proceso'])
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get();
        }

        if ($lotes->isEmpty()) {
            $this->warn('⚠️  No hay lotes activos con coordenadas GPS configuradas.');
            $this->line('💡 Agregue coordenadas a los lotes desde el menú de gestión.');

            return Command::SUCCESS;
        }

        $this->line("📍 Analizando {$lotes->count()} lote(s)...");
        $this->newLine();

        $resultados = [
            'anticipacion' => 0,
            'reaccion' => 0,
            'normal' => 0,
            'errores' => 0,
        ];

        foreach ($lotes as $lote) {
            $this->line("🌲 <fg=cyan>{$lote->propietario}</> - {$lote->ubicacion}");
            $this->newLine();

            $resultado = $this->climaService->analizarYRecomendar($lote, true);

            if (! $resultado['success']) {
                $this->error("   ❌ {$resultado['error']}");
                if (isset($resultado['sugerencia'])) {
                    $this->line("   💡 {$resultado['sugerencia']}");
                }
                $resultados['errores']++;
            } else {
                // Mostrar recomendación
                $this->renderRecomendacion($resultado);

                $this->guardarDecision($lote, $resultado);

                // Contabilizar estrategia
                if (str_starts_with($resultado['estrategia'], 'ANTICIPACION')) {
                    $resultados['anticipacion']++;
                } elseif ($resultado['estrategia'] === 'REACCION') {
                    $resultados['reaccion']++;
                } else {
                    $resultados['normal']++;
                }
            }

            $this->newLine();
            $this->line('───────────────────────────────────────────────────');
            $this->newLine();
        }

        // Resumen final
        $this->renderResumen($resultados, $lotes->count());

        return Command::SUCCESS;
    }

    /**
     * Persiste la decisión del día por lote y notifica si cambió la estrategia.
     */
    private function guardarDecision(Lote $lote, array $resultado): void
    {
        try {
            $datos = $resultado['datos_calculados'] ?? [];
            $estrategia = (string) ($resultado['estrategia'] ?? 'NORMAL');

            $existente = DecisionClima::query()
                ->where('id_lote', $lote->id_lote)
                ->whereDate('fecha', now()->toDateString())
                ->first();

            $cambio = ! $existente || $existente->estrategia !== $estrategia;

            $decision = DecisionClima::updateOrCreate(
                ['id_lote' => $lote->id_lote, 'fecha' => now()->toDateString()],
                [
                    'estrategia' => $estrategia,
                    'nivel' => (string) ($resultado['nivel_urgencia'] ?? 'BAJA'),
                    'dias_no_operativos' => (int) ($datos['dias_perdidos'] ?? $datos['dias_perdidos_proyectados'] ?? 0),
                    'deficit_tn' => (float) ($datos['volumen_riesgo'] ?? 0),
                    'residual_tn' => (float) ($datos['deficit_residual'] ?? 0),
                    'meta_diaria' => (float) ($datos['meta_diaria'] ?? 0),
                    'mensaje' => (string) ($resultado['recomendacion'] ?? ''),
                    'payload' => $resultado,
                ]
            );

            if ($cambio) {
                $this->notificarDecision($lote, $decision);
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo guardar la decision climatica', [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Aviso interno a los usuarios configurados para recordatorios.
     * Best-effort: la decision ya quedo guardada.
     */
    private function notificarDecision(Lote $lote, DecisionClima $decision): void
    {
        try {
            $usuarios = app(NotificacionService::class)->cargarConfiguracionMantenimiento()['recordatorio'];

            if (empty($usuarios)) {
                return;
            }

            $mensaje = "Estrategia: {$decision->estrategia}. Días no operativos: {$decision->dias_no_operativos}. "
                .'Déficit: '.number_format((float) $decision->deficit_tn, 2, ',', '.').' tn.';

            if ((float) $decision->residual_tn > 0) {
                $mensaje .= ' Sin cubrir: '.number_format((float) $decision->residual_tn, 2, ',', '.').' tn.';
            }

            foreach ($usuarios as $idUsuario) {
                NotificacionSistema::create([
                    'user_id' => $idUsuario,
                    'mantenimiento_id' => null,
                    'tipo' => 'clima_decision',
                    'titulo' => 'Clima - '.($lote->propietario ?? 'Lote').': '.$decision->estrategia,
                    'mensaje' => $mensaje,
                    'fecha_limite' => now()->addDay()->toDateString(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo notificar la decision climatica', [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Renderiza la recomendación con formato colorizado
     */
    private function renderRecomendacion(array $resultado): void
    {
        // Color según nivel de urgencia
        $colorUrgencia = match ($resultado['nivel_urgencia'] ?? 'MEDIA') {
            'ALTA', 'INMEDIATA' => 'red',
            'MEDIA' => 'yellow',
            default => 'green',
        };

        $this->line("<fg={$colorUrgencia};options=bold>   📋 {$resultado['estrategia']}</>  <fg=white;bg={$colorUrgencia}> {$resultado['nivel_urgencia']} </>");
        $this->newLine();

        // Mostrar recomendación línea por línea
        $lineas = explode("\n", $resultado['recomendacion']);
        foreach ($lineas as $linea) {
            $this->line("   {$linea}");
        }

        // Mostrar datos calculados en formato tabla (solo si es anticipación)
        if (str_starts_with($resultado['estrategia'], 'ANTICIPACION') && isset($resultado['datos_calculados'])) {
            $this->newLine();
            $datos = $resultado['datos_calculados'];

            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Días no operativos', $datos['dias_perdidos'] ?? 0],
                    ['Días operativos disponibles', $datos['dias_operativos_disponibles'] ?? 0],
                    ['Volumen en riesgo', round((float) ($datos['volumen_riesgo'] ?? 0), 2).' ton'],
                    ['Aumento necesario', round((float) ($datos['aumento_necesario_pct'] ?? 0), 1).'%'],
                    ['Déficit residual', round((float) ($datos['deficit_residual'] ?? 0), 2).' ton'],
                    ['Meta diaria del lote', round((float) ($datos['meta_diaria'] ?? 0), 2).' ton'],
                ]
            );
        }
    }

    /**
     * Renderiza resumen final del análisis
     */
    private function renderResumen(array $resultados, int $totalLotes): void
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('📊 RESUMEN DE ANÁLISIS');
        $this->info('═══════════════════════════════════════════════════');

        $this->line("   Total de lotes analizados: <fg=cyan>{$totalLotes}</>");
        $this->line("   Estrategias de Anticipación: <fg=yellow>{$resultados['anticipacion']}</>");
        $this->line("   Estrategias de Reacción: <fg=red>{$resultados['reaccion']}</>");
        $this->line("   Operación Normal: <fg=green>{$resultados['normal']}</>");

        if ($resultados['errores'] > 0) {
            $this->line("   Errores: <fg=red>{$resultados['errores']}</>");
        }

        $this->newLine();

        if ($resultados['anticipacion'] > 0) {
            $this->warn('💡 ACCIÓN REQUERIDA: Revisar alertas de anticipación y ajustar producción HOY.');
        }

        if ($resultados['reaccion'] > 0) {
            $this->error('🚨 ATENCIÓN: Hay lotes con lluvia activa/inminente. Ejecutar estrategias de reacción.');
        }
    }
}
