<?php

namespace App\Services;

use App\Models\ClimaDiaLote;
use App\Models\Lote;
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
        return [
            [
                'nombre' => 'Mantenimiento preventivo por umbral',
                'descripcion' => 'Acumula toneladas por maquinaria con cada carga registrada y, al superar el umbral, genera la orden de mantenimiento con personal, avisos internos y email.',
                'disparador' => 'Tiempo y evento',
                'frecuencia' => 'Umbrales: diario 06:30 · Programados: cada 4 horas · Odómetro: tiempo real',
                'comando' => 'mantenimiento:check-umbrales / mantenimiento:check-programados',
            ],
            [
                'nombre' => 'Análisis climático operativo',
                'descripcion' => 'Sincroniza pronóstico e histórico por lote contra la API externa, mapea días operativos/inactivos y recomienda estrategia (anticipación/reacción).',
                'disparador' => 'Tiempo',
                'frecuencia' => 'Decisiones: cada 6 horas · Riesgo: diario 06:00 · Clima real: diario 00:30',
                'comando' => 'clima:decisiones / clima:analizar / clima:real',
            ],
            [
                'nombre' => 'Propuestas de asignación automática',
                'descripcion' => 'Al cambiar el estado de un lote o guardar su planificación, genera propuestas de empleados, maquinarias e insumos basadas en el histórico de desempeño.',
                'disparador' => 'Cambio de estado / planificación',
                'frecuencia' => 'Bajo demanda (jobs encolados, únicos e idempotentes)',
                'comando' => 'Jobs: GenerateAllocationProposalsForLote / ProcessAllocationProposal',
            ],
        ];
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

        return $lotes->map(function (Lote $lote) use ($registrosHoy) {
            $registro = $registrosHoy->get($lote->id_lote)?->first();

            return [
                'lote' => $lote->propietario.' - '.$lote->ubicacion,
                'con_coordenadas' => $lote->latitud !== null && $lote->longitud !== null,
                'estado_operativo' => $registro?->estado_operativo,
                'fuente' => $registro?->fuente,
                'api_error' => $registro?->api_error,
                'actualizado' => $registro?->updated_at?->format('d/m/Y H:i'),
            ];
        })->all();
    }

    /**
     * Estado de la cola de trabajos: pendientes y últimos fallidos.
     *
     * @return array{pendientes: int, fallidos: array<int, array{id: int, nombre: ?string, cola: string, error: ?string, fallido_en: ?string}>}
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
