<?php

namespace App\Console\Commands;

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Services\EmpleadoPagoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalizarRiesgoClimatico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clima:analizar {--dias=7 : Cantidad de días a analizar hacia adelante}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza el pronóstico del clima usando Open-Meteo API y calcula el costo de oportunidad perdido por días de lluvia';

    /**
     * Umbral de precipitación en mm para considerar un día caído
     */
    const UMBRAL_LLUVIA = 10;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diasAnalizar = (int) $this->option('dias');

        $this->info("🌦️  Iniciando análisis climático para los próximos {$diasAnalizar} días...");
        $this->newLine();

        // 1. Obtener lotes activos con coordenadas
        $lotes = Lote::whereIn('estado', ['activo', 'en_proceso'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        if ($lotes->isEmpty()) {
            $this->warn('⚠️  No hay lotes activos con coordenadas GPS configuradas.');
            $this->line('💡 Agregue coordenadas a los lotes desde el menú de gestión para habilitar alertas climáticas.');

            return Command::SUCCESS;
        }

        $this->line("📍 Analizando {$lotes->count()} lote(s) con coordenadas GPS...");
        $this->newLine();

        $alertasGeneradas = 0;
        $costosEvitables = 0;

        foreach ($lotes as $lote) {
            $this->line("🌲 Lote: <fg=cyan>{$lote->propietario}</> - {$lote->ubicacion}");

            try {
                // 2. Consultar API Open-Meteo
                $pronostico = $this->obtenerPronosticoLluvia($lote, $diasAnalizar);

                if (! $pronostico) {
                    $this->error('   ❌ Error al obtener pronóstico para este lote');

                    continue;
                }

                // 3. Analizar días con lluvia superior al umbral
                $diasCaidosPronosticados = $this->analizarDiasDeLluvia($pronostico);

                if (empty($diasCaidosPronosticados)) {
                    $this->line('   ✅ Sin riesgo de lluvia significativa (< '.self::UMBRAL_LLUVIA.'mm)');

                    continue;
                }

                // 4. Calcular costo estructural diario
                $costoEstructuralDiario = $this->calcularCostoEstructuralDiario($lote);

                // 5. Generar alertas
                foreach ($diasCaidosPronosticados as $diaLluvia) {
                    $this->generarAlerta($lote, $diaLluvia, $costoEstructuralDiario);
                    $alertasGeneradas++;
                    $costosEvitables += $costoEstructuralDiario;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Error: {$e->getMessage()}");
                Log::error("Error en análisis climático para lote {$lote->id_lote}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $this->newLine();
        }

        // Resumen final
        $this->info('═══════════════════════════════════════════════════');
        $this->info('📊 RESUMEN DEL ANÁLISIS CLIMÁTICO');
        $this->info('═══════════════════════════════════════════════════');
        $this->line("   Lotes analizados: <fg=cyan>{$lotes->count()}</>");
        $this->line("   Alertas generadas: <fg=yellow>{$alertasGeneradas}</>");
        $this->line('   Costo evitable estimado: <fg=green>$'.number_format($costosEvitables, 2).'</>');
        $this->newLine();

        if ($alertasGeneradas > 0) {
            $this->warn('💡 RECOMENDACIÓN: Considere aumentar la producción hoy para compensar las pérdidas estimadas.');
        }

        return Command::SUCCESS;
    }

    /**
     * Obtiene el pronóstico de lluvia desde Open-Meteo API
     */
    private function obtenerPronosticoLluvia(Lote $lote, int $dias): ?array
    {
        $url = 'https://api.open-meteo.com/v1/forecast';

        $params = [
            'latitude' => $lote->latitud,
            'longitude' => $lote->longitud,
            'daily' => 'precipitation_sum',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'forecast_days' => $dias,
        ];

        try {
            $response = Http::timeout(10)->get($url, $params);

            if (! $response->successful()) {
                throw new \Exception("API respondió con status {$response->status()}");
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error al consultar Open-Meteo API', [
                'lote_id' => $lote->id_lote,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Analiza el pronóstico y retorna los días con lluvia significativa
     */
    private function analizarDiasDeLluvia(array $pronostico): array
    {
        $diasCaidos = [];

        if (! isset($pronostico['daily']['time']) || ! isset($pronostico['daily']['precipitation_sum'])) {
            return $diasCaidos;
        }

        $fechas = $pronostico['daily']['time'];
        $precipitaciones = $pronostico['daily']['precipitation_sum'];

        foreach ($fechas as $index => $fecha) {
            $mm = $precipitaciones[$index] ?? 0;

            if ($mm >= self::UMBRAL_LLUVIA) {
                $diasCaidos[] = [
                    'fecha' => Carbon::parse($fecha),
                    'mm' => $mm,
                ];
            }
        }

        return $diasCaidos;
    }

    /**
     * Calcula el costo estructural diario (empleados + maquinaria)
     */
    private function calcularCostoEstructuralDiario(Lote $lote): float
    {
        $costoTotal = 0;

        // A) Costo de empleados asignados al lote (usar jornal diario promedio)
        $empleadosActivos = Empleado::where('estado', 'activo')->get();

        foreach ($empleadosActivos as $empleado) {
            // Usar costo de día caído del trait
            try {
                $costoTotal += EmpleadoPagoService::calcularCostoDia($empleado, Carbon::today(), true, null);
            } catch (\Exception $e) {
                // Si no hay histórico, usar un costo base estimado
                Log::warning("Sin histórico de tarifa para empleado {$empleado->id_empleado}");
            }
        }

        // B) Costo de maquinaria en alquiler (costo fijo estimado)
        $maquinariasAlquiladas = Maquinaria::where('tipo_maquinaria', 'alquiler')->get();

        foreach ($maquinariasAlquiladas as $maquinaria) {
            // Estimar costo diario basado en precio de alquiler
            // Asumimos que el precio_alquiler_destajo es por tonelada, estimamos 10 ton/día
            $costoTotal += ($maquinaria->precio_alquiler_destajo ?? 0) * 10;
        }

        return round($costoTotal, 2);
    }

    /**
     * Genera una alerta/notificación de riesgo climático
     */
    private function generarAlerta(Lote $lote, array $diaLluvia, float $costoEstructural): void
    {
        $fecha = $diaLluvia['fecha'];
        $mm = $diaLluvia['mm'];

        $mensaje = sprintf(
            "⚠️  ALERTA CLIMÁTICA - Lote %s (%s)\n".
            "    📅 Fecha: %s\n".
            "    🌧️  Lluvia pronosticada: %.1f mm\n".
            "    💰 Riesgo de pérdida: $%s\n".
            '    💡 Sugerencia: Aumentar producción hoy para compensar.',
            $lote->propietario,
            $lote->ubicacion,
            $fecha->format('d/m/Y'),
            $mm,
            number_format($costoEstructural, 2)
        );

        // Mostrar en consola
        $this->warn($mensaje);

        // Registrar en log
        Log::warning('Alerta Climática', [
            'lote_id' => $lote->id_lote,
            'lote_nombre' => $lote->propietario,
            'ubicacion' => $lote->ubicacion,
            'fecha_lluvia' => $fecha->toDateString(),
            'precipitacion_mm' => $mm,
            'costo_estimado' => $costoEstructural,
        ]);

        // TODO: En el futuro, enviar notificación interna o email
        // Notification::send($usuarios, new AlertaClimatica($lote, $diaLluvia, $costoEstructural));
    }
}
