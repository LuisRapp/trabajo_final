<?php

namespace App\Services;

use App\Models\ClimaDiaLote;
use App\Models\DecisionClima;
use App\Models\Lote;
use Cron\CronExpression;
use Illuminate\Support\Facades\DB;

/**
 * Consulta el estado operativo de los procesos automatizados del sistema.
 *
 * Concentra las lecturas de monitoreo (clima por lote y cola de trabajos)
 * fuera de los componentes Livewire, según la arquitectura de capas.
 */
class EstadoProcesosService
{
    /**
     * Catálogo de los procesos automatizados del sistema.
     *
     * @return array<int, array{nombre: string, descripcion: string, disparador: string, frecuencia: string, comando: string}>
     */
    public function procesos(): array
    {
        $cronUmbrales = (string) (ConfiguracionService::obtener('mantenimiento_hora_verificacion_umbrales', '30 6 * * *') ?: '30 6 * * *');
        $cronProgramados = (string) (ConfiguracionService::obtener('mantenimiento_cron_programados', '0 */4 * * *') ?: '0 */4 * * *');
        $horaRecordatorio = (string) (ConfiguracionService::obtener('mantenimiento_hora_recordatorio', '08:00') ?: '08:00');

        $filas = [
            [
                'nombre' => 'Verificación de umbrales de mantenimiento',
                'descripcion' => 'Acumula toneladas por maquinaria y genera la orden preventiva al superar el umbral.',
                'comando' => 'mantenimiento:check-umbrales',
                'cron' => $cronUmbrales,
                'proximo' => $this->proximaEjecucion($cronUmbrales),
                'editable' => true,
            ],
            [
                'nombre' => 'Recordatorio y vencimiento de órdenes',
                'descripcion' => 'Marca vencidas las órdenes no confirmadas y envía el recordatorio diario.',
                'comando' => 'mantenimiento:check-programados',
                'cron' => $cronProgramados,
                'proximo' => $this->proximaEjecucion($cronProgramados),
                'detalle' => 'Recordatorio diario a partir de las '.$horaRecordatorio,
                'editable' => true,
            ],
            [
                'nombre' => 'Decisiones climáticas',
                'descripcion' => 'Analiza el pronóstico por lote y persiste la estrategia (compensar, priorizar o reprogramar).',
                'comando' => 'clima:decisiones',
                'cron' => '0 */6 * * *',
                'proximo' => $this->proximaEjecucion('0 */6 * * *'),
                'editable' => false,
            ],
            [
                'nombre' => 'Análisis de riesgo de lluvia',
                'descripcion' => 'Reporte diario de riesgo de lluvia por lote.',
                'comando' => 'clima:analizar',
                'cron' => '0 6 * * *',
                'proximo' => $this->proximaEjecucion('0 6 * * *'),
                'editable' => false,
            ],
            [
                'nombre' => 'Sincronización del clima observado',
                'descripcion' => 'Guarda el clima real del día anterior por lote.',
                'comando' => 'clima:real',
                'cron' => '30 0 * * *',
                'proximo' => $this->proximaEjecucion('30 0 * * *'),
                'editable' => false,
            ],
        ];

        $ejecuciones = app(EjecucionAutomatizacionService::class)->porComando();

        return array_map(function (array $fila) use ($ejecuciones) {
            $ultima = $ejecuciones[$fila['comando']] ?? null;

            return array_merge($fila, [
                'ultima_at' => $ultima?->ultima_ejecucion_at,
                'ultimo_estado' => $ultima?->estado,
                'ultimo_motivo' => $ultima?->motivo_error,
                'duracion_ms' => $ultima?->duracion_ms,
            ]);
        }, $filas);
    }

    private function proximaEjecucion(string $cron): ?string
    {
        try {
            return (new CronExpression($cron))->getNextRunDate(now())->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Estado climático por lote activo: registro del día (estado, fuente, error)
     * y su marca de actualización.
     *
     * @return array<int, array{lote: string, con_coordenadas: bool, estado_operativo: ?string, fuente: ?string, api_error: ?string, actualizado: ?string}>
     */
    public function climaPorLote(): array
    {
        $lotes = Lote::query()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->orderBy('id_lote')
            ->get();

        if ($lotes->isEmpty()) {
            return [];
        }

        $registrosHoy = ClimaDiaLote::query()
            ->whereIn('id_lote', $lotes->pluck('id_lote'))
            ->whereDate('fecha', now()->toDateString())
            ->get()
            ->groupBy('id_lote');

        $decisiones = DecisionClima::query()
            ->whereIn('id_lote', $lotes->pluck('id_lote'))
            ->orderByDesc('fecha')
            ->orderByDesc('id_decision')
            ->get()
            ->groupBy('id_lote')
            ->map->first();

        return $lotes->map(function (Lote $lote) use ($registrosHoy, $decisiones) {
            $registro = $registrosHoy->get($lote->id_lote)?->first();
            $decision = $decisiones->get($lote->id_lote);

            $label = match ($decision?->estrategia) {
                'ANTICIPACION_PLANIFICADA' => 'Compensar',
                'ANTICIPACION_MAXIMA' => 'Priorizar',
                'REACCION' => 'Reprogramar',
                'SIN_HISTORIAL' => 'Sin historial',
                'NORMAL' => 'Normal',
                default => null,
            };

            return [
                'lote' => $lote->propietario.' - '.$lote->ubicacion,
                'con_coordenadas' => $lote->latitud !== null && $lote->longitud !== null,
                'estado_operativo' => $registro?->estado_operativo,
                'fuente' => $registro?->fuente,
                'api_error' => $registro?->api_error,
                'actualizado' => $registro?->updated_at?->format('d/m/Y H:i'),
                'decision' => $decision?->estrategia,
                'decision_label' => $label,
                'decision_nivel' => $decision?->nivel,
                'decision_dias' => $decision?->dias_no_operativos,
                'decision_deficit' => $decision?->deficit_tn,
                'decision_residual' => $decision?->residual_tn,
                'decision_actualizada' => $decision?->updated_at?->format('d/m/Y H:i'),
            ];
        })->all();
    }

    /**
     * Estado de la cola de trabajos: pendientes y últimos fallidos.
     *
     * @return array{pendientes: int, fallidos: array<int, array{id: int, uuid: string, nombre: ?string, cola: string, error: ?string, fallido_en: ?string}>}
     */
    public function colaTrabajos(): array
    {
        $pendientes = DB::table('jobs')->count();

        $fallidos = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                $payload = json_decode((string) $job->payload, true);

                return [
                    'id' => (int) $job->id,
                    'uuid' => (string) $job->uuid,
                    'nombre' => $payload['displayName'] ?? null,
                    'cola' => (string) $job->queue,
                    'error' => strtok((string) ($job->exception ?? ''), "\n") ?: null,
                    'fallido_en' => $job->failed_at,
                ];
            })
            ->all();

        return [
            'pendientes' => $pendientes,
            'fallidos' => $fallidos,
        ];
    }
}
