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

        $partes = ParteDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

        // Pre-load all cargas for the date range with empleados to avoid N+1
        $cargasDelRango = Carga::whereBetween('fecha_carga', [$fechaInicio, $fechaFin])
            ->with('empleados')
            ->get()
            ->groupBy(function ($carga) {
                return \Carbon\Carbon::parse($carga->fecha_carga)->format('Y-m-d');
            });

        foreach ($partes as $parte) {
            $fechaStr = \Carbon\Carbon::parse($parte->fecha)->format('Y-m-d');

            if ($parte->es_dia_caido) {
                $trabajoEseDia = DB::table('parte_diario_empleado')
                    ->where('id_parte_diario', $parte->id_parte_diario)
                    ->where('id_empleado', $empleado->id_empleado)
                    ->exists();

                if ($trabajoEseDia) {
                    $cantidad_dias_caidos += 1;
                }
            } else {
                $cargasDelDia = collect($cargasDelRango[$fechaStr] ?? [])
                    ->filter(function ($carga) use ($empleado) {
                        return $carga->empleados->contains('id_empleado', $empleado->id_empleado);
                    });

                foreach ($cargasDelDia as $carga) {
                    $cantidadEmpleados = $carga->empleados->count();
                    $pesoAsignado = $cantidadEmpleados > 0 ? $carga->peso_neto / $cantidadEmpleados : 0;
                    $total_peso_neto += $pesoAsignado;
                }
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
    public static function generarRecibo(int $idEmpleado, float $montoBruto, float $descuentos, float $montoNeto, string $observaciones, $adelantosPendientes): Recibo
    {
        DB::beginTransaction();

        try {
            $recibo = Recibo::create([
                'id_empleado' => $idEmpleado,
                'fecha_emision' => now(),
                'monto_bruto' => $montoBruto,
                'descuentos' => $descuentos,
                'monto' => $montoNeto,
                'observaciones' => $observaciones,
            ]);

            if ($adelantosPendientes && count($adelantosPendientes) > 0) {
                foreach ($adelantosPendientes as $adelanto) {
                    $adelanto->estado = 'pagado';
                    $adelanto->save();
                }
            }

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
                    ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
                    ->get();

                $totalAdelantos = $adelantosPendientes->sum('monto');
                $montoBruto = $calculo['total_pagar_final'];
                $descuentos = $totalAdelantos;
                $montoNeto = max(0, $montoBruto - $descuentos);

                $obsBase = sprintf(
                    'Liquidación período %s a %s - %d días caídos + %.2f ton',
                    \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y'),
                    \Carbon\Carbon::parse($fechaFin)->format('d/m/Y'),
                    $calculo['cantidad_dias_caidos'],
                    $calculo['total_peso_toneladas'] ?? 0
                );

                if ($totalAdelantos > 0) {
                    $obsBase .= sprintf(' | Adelantos: $%.2f', $totalAdelantos);
                }

                $recibo = Recibo::create([
                    'id_empleado' => $empleado->id_empleado,
                    'fecha_emision' => now(),
                    'monto_bruto' => $montoBruto,
                    'descuentos' => $descuentos,
                    'monto' => $montoNeto,
                    'observaciones' => $obsBase,
                ]);

                if ($adelantosPendientes->isNotEmpty()) {
                    foreach ($adelantosPendientes as $adelanto) {
                        $adelanto->estado = 'pagado';
                        $adelanto->save();
                    }
                }

                $recibos[] = [
                    'recibo' => $recibo,
                    'empleado' => $empleado,
                    'adelantos_descontados' => $adelantosPendientes->count(),
                ];
            }

            DB::commit();

            return ['recibos' => $recibos];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
