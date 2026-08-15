<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\HistoricoRolLaboral;
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
     *                         peso_bruto, tara, peso_neto, id_chofer, destino (id_cliente), empleados, maquinarias
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
            $carga = Carga::create([
                'id_parte_diario' => $parteDiarioId,
                'id_lote' => $loteId,
                'id_categoria_madera' => $cargaData['id_categoria_madera'],
                'id_chofer' => $cargaData['id_chofer'],
                'id_cliente' => $cargaData['destino'],
                'ticket' => $cargaData['ticket'],
                'peso_bruto' => $cargaData['peso_bruto'],
                'tara' => $cargaData['tara'],
                'peso_neto' => $cargaData['peso_neto'],
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

    /**
     * Load a ParteDiario with all related data for editing.
     *
     * Builds structured arrays for cargas, jornales, and movimientos
     * so the component can simply assign them to its properties.
     *
     * @param  int  $id  The ParteDiario ID
     * @return array{parte_id: int, id_lote: int, id_lote_tarea: int, fecha: string, es_dia_caido: bool, observaciones: ?string, clima_override_confirmado: bool, clima_override_motivo: ?string, cargas: array, jornales: array, movimientos: array, total_toneladas: float}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function cargarParaEdicion(int $id): array
    {
        $parte = ParteDiario::with(['empleados.rolLaboral'])->findOrFail($id);

        $esDiaCaido = (bool) $parte->es_dia_caido;

        // Build cargas array (production mode)
        $cargas = [];
        $totalToneladas = 0.0;
        if (! $esDiaCaido) {
            $cargasModels = Carga::with(['empleados', 'maquinarias', 'cliente'])
                ->where('id_parte_diario', $parte->id_parte_diario)
                ->get();

            foreach ($cargasModels as $c) {
                $cargas[] = [
                    'id_categoria_madera' => $c->id_categoria_madera,
                    'ticket' => $c->ticket,
                    'peso_bruto' => (float) $c->peso_bruto,
                    'tara' => (float) $c->tara,
                    'peso_neto' => (float) $c->peso_neto,
                    'id_chofer' => $c->id_chofer,
                    'destino' => $c->id_cliente,
                    'destino_nombre' => $c->cliente->razon_social ?? 'Cliente no encontrado',
                    'empleados' => $c->empleados->pluck('id_empleado')->all(),
                    'maquinarias' => $c->maquinarias->pluck('id_maquinaria')->all(),
                ];
                $totalToneladas += (float) $c->peso_neto;
            }
        }

        // Build jornales array (día caído mode)
        $jornales = [];
        if ($esDiaCaido) {
            foreach ($parte->empleados as $emp) {
                $jornalVig = self::buscarJornalVigente($emp, $parte->fecha);
                $jornales[] = [
                    'id_empleado' => $emp->id_empleado,
                    'nombre_completo' => $emp->apellido.', '.$emp->nombre,
                    'rol' => $emp->rolLaboral->nombre ?? 'N/A',
                    'jornal_diario' => $jornalVig,
                    'observaciones' => null,
                ];
            }
        }

        // Build movimientos array with FIFO dedup grouping
        $movimientos = [];
        $movs = MovimientoStock::delParteDiario($parte->id_parte_diario, $parte->fecha)->get();

        if ($movs->isNotEmpty()) {
            $insumos = InventarioService::queryInsumosConStockYPrecio()
                ->with('unidadMedida')
                ->orderBy('nombre')
                ->get();

            $movimientosAgrupados = [];
            foreach ($movs as $m) {
                $motivoTexto = $m->motivo;
                $sinPrefijo = preg_replace('/^Parte Diario #'.preg_quote($parte->id_parte_diario, '/').' - /', '', $motivoTexto);
                $partesMotivo = explode(' - ', $sinPrefijo, 2);
                $motivoEnum = $partesMotivo[0] ?? 'Producción';
                $obs = $partesMotivo[1] ?? null;

                $insumo = $insumos->firstWhere('id_insumo', $m->id_insumo);
                $clave = $m->id_insumo.'_'.$m->tipo.'_'.$motivoEnum;

                if (! isset($movimientosAgrupados[$clave])) {
                    $movimientosAgrupados[$clave] = [
                        'id_insumo' => $m->id_insumo,
                        'nombre_insumo' => $insumo->nombre ?? 'Insumo',
                        'tipo' => $m->tipo,
                        'cantidad' => 0,
                        'motivo' => $motivoEnum,
                        'observaciones' => $obs,
                        'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
                    ];
                }

                $movimientosAgrupados[$clave]['cantidad'] += (float) $m->cantidad;
            }

            $movimientos = array_values($movimientosAgrupados);
        }

        return [
            'parte_id' => $parte->id_parte_diario,
            'id_lote' => $parte->id_lote,
            'id_lote_tarea' => $parte->id_lote_tarea,
            'fecha' => $parte->fecha,
            'es_dia_caido' => $esDiaCaido,
            'observaciones' => $parte->observaciones,
            'clima_override_confirmado' => (bool) ($parte->clima_override ?? false),
            'clima_override_motivo' => $parte->clima_override_motivo,
            'cargas' => $cargas,
            'jornales' => $jornales,
            'movimientos' => $movimientos,
            'total_toneladas' => $totalToneladas,
        ];
    }

    /**
     * Look up the active jornal for an employee on a given date.
     */
    private static function buscarJornalVigente($empleado, string $fecha): float
    {
        if (! $empleado->rolLaboral) {
            return 0;
        }

        $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        if (! $rolId) {
            return 0;
        }

        $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
            ->vigenteEnFecha($fecha)
            ->first();

        if ($hist) {
            return (float) ($hist->jornal_diario ?? 0);
        }

        return (float) ($empleado->rolLaboral->jornal_diario ?? 0);
    }
}
