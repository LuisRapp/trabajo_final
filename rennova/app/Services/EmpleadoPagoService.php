<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\ParteDiario;
use Illuminate\Support\Facades\DB;

class EmpleadoPagoService
{
    /**
     * Calcular el costo de mano de obra de un empleado para un día específico.
     *
     * @param  string|\DateTimeInterface  $fecha
     * @param  \Illuminate\Support\Collection|null  $cargasDelDia
     */
    public static function calcularCostoDia(Empleado $empleado, $fecha, bool $esDiaCaido, $cargasDelDia = null): float
    {
        $valorJornal = 0;
        $tarifaPorTonelada = 0;

        $rolId = $empleado->id_rol_laboral ?? $empleado->rolLaboral->id_rol_laboral ?? null;

        if ($rolId) {
            $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                ->whereDate('fecha_inicio', '<=', $fecha)
                ->where(function ($q) use ($fecha) {
                    $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha);
                })
                ->orderBy('fecha_inicio', 'desc')
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

    public static function calcularPagoRango(Empleado $empleado, $fechaInicio, $fechaFin)
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
                ->whereDate('fecha_inicio', '<=', $fechaFin)
                ->where(function ($q) use ($fechaFin) {
                    $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fechaFin);
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if (! $hist) {
                $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                    ->whereDate('fecha_inicio', '<=', $fechaInicio)
                    ->where(function ($q) use ($fechaInicio) {
                        $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fechaInicio);
                    })
                    ->orderBy('fecha_inicio', 'desc')
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

        foreach ($partes as $parte) {
            if ($parte->es_dia_caido) {
                $trabajoEseDia = DB::table('parte_diario_empleado')
                    ->where('id_parte_diario', $parte->id_parte_diario)
                    ->where('id_empleado', $empleado->id_empleado)
                    ->exists();

                if ($trabajoEseDia) {
                    $cantidad_dias_caidos += 1;
                }
            } else {
                $cargasDelDia = Carga::whereDate('fecha_carga', $parte->fecha)
                    ->whereHas('empleados', function ($query) use ($empleado) {
                        $query->where('empleados.id_empleado', $empleado->id_empleado);
                    })
                    ->with('empleados')
                    ->get();

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
}
