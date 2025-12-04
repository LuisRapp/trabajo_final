<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lote;
use App\Services\ClimaDecisionService;

class AnalizarDecisionesClimaticas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clima:decisiones {--lote= : ID del lote a analizar (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza el clima y genera recomendaciones operativas inteligentes (Anticipación/Reacción)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🌦️  Sistema de Decisiones Climáticas Inteligentes");
        $this->info("═══════════════════════════════════════════════════");
        $this->newLine();

        $climaService = new ClimaDecisionService();

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
            'errores' => 0,
        ];

        foreach ($lotes as $lote) {
            $this->line("🌲 <fg=cyan>{$lote->propietario}</> - {$lote->ubicacion}");
            $this->newLine();

            $resultado = $climaService->analizarYRecomendar($lote);

            if (!$resultado['success']) {
                $this->error("   ❌ {$resultado['error']}");
                if (isset($resultado['sugerencia'])) {
                    $this->line("   💡 {$resultado['sugerencia']}");
                }
                $resultados['errores']++;
            } else {
                // Mostrar recomendación
                $this->renderRecomendacion($resultado);
                
                // Contabilizar estrategia
                if ($resultado['estrategia'] === 'ANTICIPACION') {
                    $resultados['anticipacion']++;
                } else {
                    $resultados['reaccion']++;
                }
            }

            $this->newLine();
            $this->line("───────────────────────────────────────────────────");
            $this->newLine();
        }

        // Resumen final
        $this->renderResumen($resultados, $lotes->count());

        return Command::SUCCESS;
    }

    /**
     * Renderiza la recomendación con formato colorizado
     */
    private function renderRecomendacion(array $resultado): void
    {
        // Color según nivel de urgencia
        $colorUrgencia = match($resultado['nivel_urgencia'] ?? 'MEDIA') {
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
        if ($resultado['estrategia'] === 'ANTICIPACION' && isset($resultado['datos_calculados'])) {
            $this->newLine();
            $datos = $resultado['datos_calculados'];
            
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Días hasta lluvia', $datos['dias_hasta_lluvia'] ?? 'N/A'],
                    ['Primer día de lluvia', $datos['dia_cero'] ?? 'N/A'],
                    ['Días operativos previos', $datos['dias_operativos_previos'] ?? 'N/A'],
                    ['Volumen en riesgo', ($datos['volumen_riesgo'] ?? 0) . ' ton'],
                    ['Aumento necesario', round($datos['aumento_necesario_pct'] ?? 0, 1) . '%'],
                    ['¿Viable 100%?', ($datos['es_viable_100'] ?? false) ? '✅ SÍ' : '⚠️  NO'],
                ]
            );
        }
    }

    /**
     * Renderiza resumen final del análisis
     */
    private function renderResumen(array $resultados, int $totalLotes): void
    {
        $this->info("═══════════════════════════════════════════════════");
        $this->info("📊 RESUMEN DE ANÁLISIS");
        $this->info("═══════════════════════════════════════════════════");
        
        $this->line("   Total de lotes analizados: <fg=cyan>{$totalLotes}</>");
        $this->line("   Estrategias de Anticipación: <fg=yellow>{$resultados['anticipacion']}</>");
        $this->line("   Estrategias de Reacción: <fg=red>{$resultados['reaccion']}</>");
        
        if ($resultados['errores'] > 0) {
            $this->line("   Errores: <fg=red>{$resultados['errores']}</>");
        }

        $this->newLine();

        if ($resultados['anticipacion'] > 0) {
            $this->warn("💡 ACCIÓN REQUERIDA: Revisar alertas de anticipación y ajustar producción HOY.");
        }

        if ($resultados['reaccion'] > 0) {
            $this->error("🚨 ATENCIÓN: Hay lotes con lluvia activa/inminente. Ejecutar estrategias de reacción.");
        }
    }
}
