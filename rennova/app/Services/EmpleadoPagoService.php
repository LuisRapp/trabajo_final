<?php

namespace App\Services;

use App\Models\Adelanto;
use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\ParteDiario;
use App\Models\Recibo;
use Illuminate\Support\Facades\DB;

class EmpleadoPagoService
{
    /**
     * Calcula el costo laboral de un empleado para un día específico.
     *
     * Para días caídos: devuelve el jornal diario.
     * Para días de producción: devuelve toneladas procesadas × tarifa por tonelada.
     * Usa el registro salarial vigente de HistoricoRolLaboral para la fecha dada.
     *
     * @param  \App\Models\Empleado  $empleado  Empleado cuyo costo se calcula
     * @param  string|\DateTimeInterface  $fecha  Fecha del cálculo
     * @param  bool  $esDiaCaido  Si el día es no productivo (caído)
     * @param  \Illuminate\Support\Collection|null  $cargasDelDia  Cargas del día precargadas (con relación empleados)
     * @return float Costo redondeado (2 decimales)
     */
    public static function calcularCostoDia(Empleado $empleado, $fecha, bool $esDiaCaido, $cargasDelDia = null): float
    {
        $valorJornal = 0;
        $tarifaPorTonelada = 0;

        $rolId = $empleado->id_rol_laboral ?? $empleado->rolLaboral->id_rol_laboral ?? null;

        if ($rolId) {
            $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                ->vigenteEnFecha($fecha)
                ->first();

            if ($hist) {
                $valorJornal = (float) ($hist->jornal_diario ?? 0);
                $tarifaPorTonelada = (float) ($hist->precio_tonelada ?? 0);
            } elseif ($empleado->rolLaboral) {
                $valorJornal = (float) ($empleado->rolLaboral->jornal_diario ?? $empleado->rolLaboral->costo_diario ?? 0);
                $tarifaPorTonelada = (float) ($empleado->rolLaboral->precio_tonelada ?? 0);
            }
        }

        if ($esDiaCaido) {
            return round($valorJornal, 2);
        }

        if (! $cargasDelDia || $cargasDelDia->isEmpty()) {
            return 0.0;
        }

        $totalToneladasEmpleado = 0.0;

        foreach ($cargasDelDia as $carga) {
            $cantidadEmpleados = $carga->empleados->count();

            if ($cantidadEmpleados > 0) {
                $pesoAsignadoKg = $carga->peso_neto / $cantidadEmpleados;
                $pesoAsignadoTon = $pesoAsignadoKg / 1000;
                $totalToneladasEmpleado += $pesoAsignadoTon;
            }
        }

        return round($totalToneladasEmpleado * $tarifaPorTonelada, 2);
    }

    /**
     * Calcula el pago total de un empleado en un rango de fechas.
     *
     * Combina dos componentes de pago:
     * - Días caídos: cantidad de días × jornal diario
     * - Días de producción: total de toneladas procesadas × tarifa por tonelada
     *
     * @param  \App\Models\Empleado  $empleado  Empleado cuyo pago se calcula
     * @param  string  $fechaInicio  Fecha de inicio (Y-m-d)
     * @param  string  $fechaFin  Fecha de fin (Y-m-d)
     * @return array{
     *     cantidad_dias_caidos: int,
     *     total_peso_neto: float,
     *     total_peso_toneladas: float,
     *     valor_jornal: float,
     *     tarifa_fija_por_tonelada: float,
     *     total_pagar_jornales: float,
     *     total_pagar_produccion: float,
     *     total_pagar_final: float
     * }
     */
    public static function calcularPagoRango(Empleado $empleado, $fechaInicio, $fechaFin): array
    {
        $cantidad_dias_caidos = 0;
        $total_peso_neto = 0.0;

        $valorJornal = 0;
        $tarifaFija = 0;

        $rolId = null;
        if ($empleado->relationLoaded('rolLaboral') || $empleado->rolLaboral) {
            $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        } else {
            $rolId = $empleado->id_rol_laboral ?? null;
        }

        if ($rolId) {
            $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                ->vigenteEnFecha($fechaFin)
                ->first();

            if (! $hist) {
                $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                    ->vigenteEnFecha($fechaInicio)
                    ->first();
            }

            if ($hist) {
                $valorJornal = (float) ($hist->jornal_diario ?? 0);
                $tarifaFija = (float) ($hist->precio_tonelada ?? 0);
            } else {
                if ($empleado->rolLaboral) {
                    $valorJornal = (float) ($empleado->rolLaboral->jornal_diario ?? $empleado->rolLaboral->costo_diario ?? 0);
                    $tarifaFija = (float) ($empleado->rolLaboral->precio_tonelada ?? 0);
                }
            }
        }

        // Dias caidos trabajados y aun no liquidados.
        $cantidad_dias_caidos = DB::table('parte_diario_empleado as pde')
            ->join('parte_diarios as pd', 'pd.id_parte_diario', '=', 'pde.id_parte_diario')
            ->where('pde.id_empleado', $empleado->id_empleado)
            ->whereNull('pde.id_recibo')
            ->where('pd.es_dia_caido', true)
            ->whereBetween('pd.fecha', [$fechaInicio, $fechaFin])
            ->distinct()
            ->count('pd.id_parte_diario');

        // Produccion: cargas de dias operativos, del empleado y aun no liquidadas.
        $diasOperativos = ParteDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('es_dia_caido', false)
            ->pluck('fecha')
            ->map(fn ($fecha) => \Carbon\Carbon::parse($fecha)->format('Y-m-d'))
            ->unique()
            ->flip();

        if ($diasOperativos->isNotEmpty()) {
            $cargas = Carga::whereBetween('fecha_carga', [$fechaInicio, $fechaFin])
                ->whereHas('empleados', function ($query) use ($empleado) {
                    $query->where('empleados.id_empleado', $empleado->id_empleado);
                })
                ->with('empleados')
                ->get()
                ->filter(function ($carga) use ($empleado, $diasOperativos) {
                    $dia = \Carbon\Carbon::parse($carga->fecha_carga)->format('Y-m-d');

                    if (! $diasOperativos->has($dia)) {
                        return false;
                    }

                    $vinculo = $carga->empleados->firstWhere('id_empleado', $empleado->id_empleado);

                    return $vinculo !== null && $vinculo->pivot->id_recibo === null;
                });

            foreach ($cargas as $carga) {
                $cantidadEmpleados = $carga->empleados->count();
                $total_peso_neto += $cantidadEmpleados > 0 ? $carga->peso_neto / $cantidadEmpleados : 0;
            }
        }

        $total_peso_toneladas = round($total_peso_neto / 1000, 2);

        $total_pagar_jornales = $cantidad_dias_caidos * (float) $valorJornal;
        $total_pagar_produccion = $total_peso_toneladas * (float) $tarifaFija;
        $total_pagar_final = $total_pagar_jornales + $total_pagar_produccion;

        return [
            'cantidad_dias_caidos' => $cantidad_dias_caidos,
            'total_peso_neto' => round($total_peso_neto, 2),
            'total_peso_toneladas' => round($total_peso_toneladas, 2),
            'valor_jornal' => (float) $valorJornal,
            'tarifa_fija_por_tonelada' => (float) $tarifaFija,
            'total_pagar_jornales' => round($total_pagar_jornales, 2),
            'total_pagar_produccion' => round($total_pagar_produccion, 2),
            'total_pagar_final' => round($total_pagar_final, 2),
        ];
    }

    /**
     * Genera un recibo de pago para un empleado y marca los adelantos asociados como pagados.
     *
     * Ejecuta dentro de una transacción. El llamador es responsable de los
     * efectos post-commit (p. ej. generación de PDF, email).
     *
     * @param  int  $idEmpleado  Identificador del empleado del recibo
     * @param  float  $montoBruto  Monto bruto del pago
     * @param  float  $descuentos  Descuentos por adelantos
     * @param  float  $montoNeto  Pago neto después de descuentos
     * @param  string  $observaciones  Observaciones del recibo
     * @param  \Illuminate\Support\Collection  $adelantosPendientes  Adelantos a marcar como pagados
     * @return \App\Models\Recibo Recibo creado
     *
     * @throws \Exception Si ocurre un error de base de datos (transacción revertida)
     */
    public static function generarRecibo(int $idEmpleado, float $montoBruto, float $otrosDescuentos, string $observaciones, $adelantosPendientes, string $fechaInicio, string $fechaFin): Recibo
    {
        $otros = max(0, round($otrosDescuentos, 2));

        if ($otros > $montoBruto) {
            throw new \DomainException('Los descuentos no pueden superar el monto bruto de la liquidación.');
        }

        DB::beginTransaction();

        try {
            $disponible = min(self::topeAdelanto($montoBruto), max(0, $montoBruto - $otros));
            $aplicado = self::aplicarAdelantos($adelantosPendientes ?? collect(), $disponible)['aplicado'];
            $descuentos = round($aplicado + $otros, 2);
            $montoNeto = max(0, round($montoBruto - $descuentos, 2));

            $recibo = Recibo::create([
                'id_empleado' => $idEmpleado,
                'fecha_emision' => now(),
                'monto_bruto' => $montoBruto,
                'descuentos' => $descuentos,
                'monto' => $montoNeto,
                'observaciones' => $observaciones,
            ]);

            self::marcarPivotesPagados($recibo->id_recibo, $idEmpleado, $fechaInicio, $fechaFin);

            DB::commit();

            return $recibo;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Genera recibos de pago para todos los empleados activos en un rango de fechas.
     *
     * Por cada empleado activo: calcula el pago vía calcularPagoRango(),
     * aplica los descuentos por adelantos pendientes, crea el recibo y marca
     * los adelantos como pagados. Ejecuta dentro de una transacción.
     *
     * El llamador es responsable del PDF, el email y el feedback de la interfaz.
     *
     * @param  string  $fechaInicio  Fecha de inicio (Y-m-d)
     * @param  string  $fechaFin  Fecha de fin (Y-m-d)
     * @return array{recibos: array<int, array{recibo: Recibo, empleado: Empleado, adelantos_descontados: int}>}
     *
     * @throws \Exception Si ocurre un error de base de datos
     */
    public static function liquidarTodos(string $fechaInicio, string $fechaFin): array
    {
        $empleados = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();

        if ($empleados->isEmpty()) {
            return ['recibos' => []];
        }

        DB::beginTransaction();

        try {
            $recibos = [];

            foreach ($empleados as $empleado) {
                $calculo = self::calcularPagoRango($empleado, $fechaInicio, $fechaFin);

                $adelantosPendientes = Adelanto::where('id_empleado', $empleado->id_empleado)
                    ->where('estado', 'pendiente')
                    ->orderBy('fecha_emision')
                    ->get();

                $montoBruto = $calculo['total_pagar_final'];
                $aplicado = self::aplicarAdelantos($adelantosPendientes, self::topeAdelanto($montoBruto));
                $descuentos = $aplicado['aplicado'];
                $montoNeto = max(0, round($montoBruto - $descuentos, 2));

                $obsBase = sprintf(
                    'Liquidación período %s a %s - %d días caídos + %.2f ton',
                    \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y'),
                    \Carbon\Carbon::parse($fechaFin)->format('d/m/Y'),
                    $calculo['cantidad_dias_caidos'],
                    $calculo['total_peso_toneladas'] ?? 0
                );

                if ($descuentos > 0) {
                    $obsBase .= sprintf(' | Adelantos: $%.2f', $descuentos);
                }

                $recibo = Recibo::create([
                    'id_empleado' => $empleado->id_empleado,
                    'fecha_emision' => now(),
                    'monto_bruto' => $montoBruto,
                    'descuentos' => $descuentos,
                    'monto' => $montoNeto,
                    'observaciones' => $obsBase,
                ]);

                self::marcarPivotesPagados($recibo->id_recibo, $empleado->id_empleado, $fechaInicio, $fechaFin);

                $recibos[] = [
                    'recibo' => $recibo,
                    'empleado' => $empleado,
                    'adelantos_descontados' => $aplicado['cantidad'],
                ];
            }

            DB::commit();

            return ['recibos' => $recibos];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Promedio de liquidaciones (recibos) de un empleado.
     *
     * Base del tope de adelanto: la ley argentina (LCT art. 130) no permite
     * adelantar más de la mitad de la remuneración del período. Como no hay un
     * campo de sueldo fijo, el tope se calcula sobre el promedio de lo que el
     * empleado cobró (recibos emitidos).
     */
    public static function promedioLiquidaciones(Empleado $empleado): float
    {
        // Sobre el bruto: el adelanto no debe reducir el promedio que habilita
        // nuevos adelantos (LCT art. 130 apunta al salario, no al neto ya descontado).
        return round((float) Recibo::query()
            ->where('id_empleado', $empleado->id_empleado)
            ->avg('monto_bruto'), 2);
    }

    /**
     * Suma de adelantos pendientes de un empleado (estado 'pendiente').
     *
     * @param  int|null  $excluirId  Adelanto a excluir (al editar, para no
     *                               contar el propio monto contra sí mismo)
     */
    public static function adelantosPendientes(Empleado $empleado, ?int $excluirId = null): float
    {
        $query = Adelanto::query()
            ->where('id_empleado', $empleado->id_empleado)
            ->where('estado', 'pendiente');

        if ($excluirId !== null) {
            $query->where('id_adelanto', '!=', $excluirId);
        }

        return round((float) $query->get()->sum(fn ($adelanto) => $adelanto->saldoPendiente()), 2);
    }

    /**
     * Marca como pagadas (por empleado) las cargas y los jornales del periodo.
     * Asi una segunda liquidacion no vuelve a contar la misma produccion.
     */
    private static function marcarPivotesPagados(int $idRecibo, int $idEmpleado, string $fechaInicio, string $fechaFin): void
    {
        DB::table('carga_empleado')
            ->where('id_empleado', $idEmpleado)
            ->whereNull('id_recibo')
            ->whereIn('id_carga', function ($query) use ($fechaInicio, $fechaFin) {
                $query->select('id_carga')
                    ->from('cargas')
                    ->whereBetween('fecha_carga', [$fechaInicio, $fechaFin]);
            })
            ->update(['id_recibo' => $idRecibo]);

        DB::table('parte_diario_empleado')
            ->where('id_empleado', $idEmpleado)
            ->whereNull('id_recibo')
            ->whereIn('id_parte_diario', function ($query) use ($fechaInicio, $fechaFin) {
                $query->select('id_parte_diario')
                    ->from('parte_diarios')
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->update(['id_recibo' => $idRecibo]);
    }

    /**
     * Tope de descuento por liquidacion: 50% del monto bruto.
     */
    private static function topeAdelanto(float $montoBruto): float
    {
        return round(max(0, $montoBruto) * 0.5, 2);
    }

    /**
     * Aplica el saldo disponible sobre los adelantos pendientes, del mas
     * antiguo al mas nuevo. Un adelanto solo pasa a 'pagado' cuando su saldo
     * queda en cero; si no alcanza, el resto se arrastra a la proxima.
     *
     * @param  \Illuminate\Support\Collection<int, Adelanto>  $adelantos
     * @return array{aplicado: float, cantidad: int}
     */
    private static function aplicarAdelantos($adelantos, float $disponible): array
    {
        $aplicado = 0.0;
        $cantidad = 0;

        foreach ($adelantos as $adelanto) {
            if ($disponible <= 0) {
                break;
            }

            $saldo = $adelanto->saldoPendiente();

            if ($saldo <= 0) {
                $adelanto->estado = 'pagado';
                $adelanto->save();

                continue;
            }

            $tomar = min($saldo, $disponible);
            $adelanto->monto_descontado = round((float) $adelanto->monto_descontado + $tomar, 2);
            $adelanto->estado = $adelanto->saldoPendiente() <= 0.009 ? 'pagado' : 'pendiente';
            $adelanto->save();

            $aplicado += $tomar;
            $cantidad++;
            $disponible -= $tomar;
        }

        return ['aplicado' => round($aplicado, 2), 'cantidad' => $cantidad];
    }

    /**
     * Tope de adelanto: 50% del promedio de liquidaciones del empleado.
     *
     * Es el monto máximo que puede pedir de nuevo, ya descontados los adelantos
     * pendientes: (nuevo adelanto + pendientes) no puede superar el 50% del
     * promedio. Sin recibos el promedio es 0, así que el tope es 0.
     *
     * @param  int|null  $excluirId  Adelanto a excluir (al editar)
     */
    public static function montoMaximoAdelanto(Empleado $empleado, ?int $excluirId = null): float
    {
        $disponible = round(self::promedioLiquidaciones($empleado) * 0.5, 2)
            - self::adelantosPendientes($empleado, $excluirId);

        return max(0, round($disponible, 2));
    }
}
