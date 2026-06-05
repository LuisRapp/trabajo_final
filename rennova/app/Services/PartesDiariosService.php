<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\LoteTarea;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;
use Illuminate\Support\Facades\DB;

class PartesDiariosService
{
    /**
     * Create or update a ParteDiario record.
     *
     * @param  array  $data  Must contain: id_lote, id_lote_tarea, fecha, es_dia_caido,
     *                       observaciones, clima_override_confirmado, clima_override_motivo,
     *                       and optionally parte_id for updates.
     *
     * @throws \InvalidArgumentException If tarea doesn't belong to lote
     */
    public static function crearOActualizar(array $data): ParteDiario
    {
        $tarea = LoteTarea::find($data['id_lote_tarea']);

        if (! $tarea || (int) $tarea->id_lote !== (int) $data['id_lote']) {
            throw new \InvalidArgumentException('La tarea seleccionada no corresponde al lote.');
        }

        $overrideAplicado = ! (bool) $data['es_dia_caido'] && (bool) ($data['clima_override_confirmado'] ?? false);
        $overrideMotivo = $overrideAplicado ? trim((string) ($data['clima_override_motivo'] ?? '')) : null;

        $parteExistente = isset($data['parte_id']) && $data['parte_id']
            ? ParteDiario::find($data['parte_id'])
            : null;

        $overrideConfirmadoPor = $overrideAplicado
            ? ($parteExistente?->clima_override_confirmado_por ?? auth()->id())
            : null;
        $overrideConfirmadoAt = $overrideAplicado
            ? ($parteExistente?->clima_override_confirmado_at ?? now())
            : null;

        return ParteDiario::updateOrCreate(
            ['id_parte_diario' => $data['parte_id'] ?? null],
            [
                'id_lote' => $data['id_lote'],
                'id_lote_tarea' => $tarea->id_lote_tarea,
                'fecha' => $data['fecha'],
                'tipo_tarea' => (string) $tarea->tipo_tarea,
                'es_dia_caido' => (bool) $data['es_dia_caido'],
                'clima_override' => $overrideAplicado,
                'clima_override_motivo' => $overrideMotivo,
                'clima_override_confirmado_por' => $overrideConfirmadoPor,
                'clima_override_confirmado_at' => $overrideConfirmadoAt,
                'observaciones' => $data['observaciones'] ?? null,
            ]
        );
    }

    /**
     * Register cargas for a ParteDiario: deletes previous cargas (if editing),
     * creates new Carga records, and syncs empleados/maquinarias.
     *
     * @param  int  $parteDiarioId  The ParteDiario ID
     * @param  array  $cargas  Array of carga data with keys: id_categoria_madera, ticket,
     *                         peso_bruto, tara, peso_neto, id_chofer, destino, empleados, maquinarias
     * @param  int  $loteId  The Lote ID
     * @param  string  $fecha  The cargo date (Y-m-d)
     * @param  int|null  $parteId  Original parte ID for deletion of previous cargas (null = new)
     * @return array{eventos: array<int, array{Carga, int, float}>}
     */
    public static function registrarCargas(int $parteDiarioId, array $cargas, int $loteId, string $fecha, ?int $parteId = null): array
    {
        // Si estamos en modo edición, eliminar cargas anteriores
        if ($parteId) {
            $cargasAnteriores = Carga::where('id_parte_diario', $parteDiarioId)->get();

            foreach ($cargasAnteriores as $cAnterior) {
                $cAnterior->delete();
            }
        }

        $eventos = [];

        foreach ($cargas as $cargaData) {
            $cliente = Cliente::find($cargaData['destino']);
            $nombreDestino = $cliente ? $cliente->razon_social : 'Cliente no encontrado';

            $carga = Carga::create([
                'id_parte_diario' => $parteDiarioId,
                'id_lote' => $loteId,
                'id_categoria_madera' => $cargaData['id_categoria_madera'],
                'id_chofer' => $cargaData['id_chofer'],
                'ticket' => $cargaData['ticket'],
                'peso_bruto' => $cargaData['peso_bruto'],
                'tara' => $cargaData['tara'],
                'peso_neto' => $cargaData['peso_neto'],
                'destino' => $nombreDestino,
                'fecha_carga' => $fecha,
            ]);

            $carga->empleados()->sync($cargaData['empleados']);
            $carga->maquinarias()->sync($cargaData['maquinarias'] ?? []);

            $maqs = $cargaData['maquinarias'] ?? [];
            if (! empty($maqs)) {
                $valorIngresado = (float) ($cargaData['peso_neto'] ?? 0);
                $toneladasTotales = $valorIngresado > 1000 ? ($valorIngresado / 1000.0) : $valorIngresado;
                $porMaquinaria = count($maqs) > 0 ? $toneladasTotales / count($maqs) : 0;

                foreach ($maqs as $maqId) {
                    $eventos[] = [$carga, $maqId, $porMaquinaria];
                }
            }
        }

        return ['eventos' => $eventos];
    }

    /**
     * Sync jornales (empleados) for a día caído ParteDiario.
     *
     * @param  \App\Models\ParteDiario  $parteDiario  The ParteDiario to sync
     * @param  array  $jornales  Array with id_empleado keys
     */
    public static function sincronizarJornales(ParteDiario $parteDiario, array $jornales): void
    {
        $empleadosIds = array_column($jornales, 'id_empleado');
        $parteDiario->empleados()->sync($empleadosIds);
    }

    /**
     * Register stock movements for a ParteDiario.
     *
     * Deletes previous movements (if editing) and registers FIFO exits
     * via InventarioService::registrarSalida().
     *
     * @param  int  $parteDiarioId  The ParteDiario ID
     * @param  array  $movimientos  Array of movimiento data with keys: id_insumo, tipo, cantidad, motivo, observaciones
     * @param  string  $fecha  The movement date (Y-m-d)
     * @param  int|null  $parteId  Original parte ID for deletion of previous movements (null = new)
     * @return array{resultados: array<int, array>}
     *
     * @throws \Exception If stock is insufficient
     */
    public static function registrarMovimientos(int $parteDiarioId, array $movimientos, string $fecha, ?int $parteId = null): array
    {
        // Si estamos en edición, eliminar movimientos previos
        if ($parteId) {
            MovimientoStock::delParteDiario($parteDiarioId)->delete();
        }

        $resultados = [];

        foreach ($movimientos as $movData) {
            $motivo = 'Parte Diario #'.$parteDiarioId.' - '.$movData['motivo'].
                     ($movData['observaciones'] ? ' - '.$movData['observaciones'] : '');

            if ($movData['tipo'] === 'salida') {
                $resultado = InventarioService::registrarSalida(
                    $movData['id_insumo'],
                    $movData['cantidad'],
                    $motivo,
                    $fecha,
                    $parteDiarioId
                );

                $resultados[] = [
                    'insumo_id' => $movData['id_insumo'],
                    'cantidad' => $movData['cantidad'],
                    'costo_total' => $resultado['costo_total'],
                    'lotes_consumidos' => count($resultado['lotes_consumidos']),
                ];
            }
        }

        return ['resultados' => $resultados];
    }

    /**
     * Guardar un ParteDiario completo con sus cargas/jornales y movimientos.
     *
     * Orchestrates the creation/update of a ParteDiario and all its sub-entities
     * within a single database transaction.
     *
     * @param  array  $data  Complete parte data (see component fields)
     * @return array{parte_diario: ParteDiario, eventos_carga: array, es_nuevo: bool}
     *
     * @throws \Exception If any error occurs (transaction rolled back)
     */
    public static function guardar(array $data): array
    {
        DB::beginTransaction();

        try {
            // Enforce: 1 Parte Diario per (lote, fecha)
            if (empty($data['parte_id'])) {
                $existente = ParteDiario::where('id_lote', $data['id_lote'])
                    ->whereDate('fecha', $data['fecha'])
                    ->orderByDesc('id_parte_diario')
                    ->first();

                if ($existente) {
                    $data['parte_id'] = $existente->id_parte_diario;
                }
            }

            $esNuevo = empty($data['parte_id']);

            $parteDiario = self::crearOActualizar($data);
            $parteDiarioId = $parteDiario->id_parte_diario;

            $eventosCarga = [];

            if (! (bool) ($data['es_dia_caido'] ?? false)) {
                $resultadoCargas = self::registrarCargas(
                    $parteDiarioId,
                    $data['cargas'] ?? [],
                    $data['id_lote'],
                    $data['fecha'],
                    $data['parte_id'] ?? null
                );
                $eventosCarga = $resultadoCargas['eventos'];
            } else {
                self::sincronizarJornales($parteDiario, $data['jornales'] ?? []);
            }

            self::registrarMovimientos(
                $parteDiarioId,
                $data['movimientos'] ?? [],
                $data['fecha'],
                $data['parte_id'] ?? null
            );

            DB::commit();

            return [
                'parte_diario' => $parteDiario,
                'eventos_carga' => $eventosCarga,
                'es_nuevo' => $esNuevo,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
