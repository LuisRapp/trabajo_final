<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Mantenimiento;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;

class ParteDiarioCostoService
{
    public static function calcularYGuardarCostos(ParteDiario $parteDiario)
    {
        $costoManoObra = 0.0;
        $costoInsumos = 0.0;
        $costoMaquinaria = 0.0;
        $totalToneladas = 0.0;

        $empleados = $parteDiario->empleados()->with('rolLaboral')->get();

        if (! $empleados->isEmpty()) {
            foreach ($empleados as $empleado) {
                $cargasDelEmpleado = Carga::whereDate('fecha_carga', $parteDiario->fecha)
                    ->whereHas('empleados', function ($q) use ($empleado) {
                        $q->where('empleados.id_empleado', $empleado->id_empleado);
                    })
                    ->with('empleados')
                    ->get();

                $costoEmpleado = $empleado->calcularCostoDia(
                    $parteDiario->fecha,
                    $parteDiario->es_dia_caido,
                    $cargasDelEmpleado
                );

                $costoManoObra += $costoEmpleado;
            }
        }

        $movimientos = MovimientoStock::where('tipo', 'salida')
            ->where(function ($query) use ($parteDiario) {
                $query->where('id_parte_diario', $parteDiario->id_parte_diario)
                    ->orWhere(function ($fallback) use ($parteDiario) {
                        $fallback->whereNull('id_parte_diario')
                            ->whereDate('fecha', $parteDiario->fecha)
                            ->where('motivo', 'LIKE', 'Parte Diario #'.$parteDiario->id_parte_diario.'%');
                    });
            })
            ->get();

        foreach ($movimientos as $mov) {
            if ($mov->costo_total_movimiento) {
                $costoInsumos += (float) $mov->costo_total_movimiento;
            } else {
                $costoInsumos += (float) ($mov->cantidad * ($mov->precio_unitario ?? 0));
            }
        }

        $cargasDelParte = $parteDiario->cargas()->with('maquinarias')->get();

        foreach ($cargasDelParte as $carga) {
            $totalToneladas += ($carga->peso_neto / 1000);
        }

        $maquinariasUsadas = collect();
        foreach ($cargasDelParte as $carga) {
            foreach ($carga->maquinarias as $maq) {
                if (! $maquinariasUsadas->contains('id_maquinaria', $maq->id_maquinaria)) {
                    $maquinariasUsadas->push($maq);
                }
            }
        }

        foreach ($maquinariasUsadas as $maq) {
            if ($maq->es_alquilada && $maq->tipoMaquinaria) {
                $precioAlquilerPorTon = (float) ($maq->tipoMaquinaria->precio_alquiler_destajo ?? 0);
                $costoMaquinaria += $totalToneladas * $precioAlquilerPorTon;
            }
        }

        $idsMaquinarias = $maquinariasUsadas->pluck('id_maquinaria')->toArray();

        if (! empty($idsMaquinarias)) {
            $mantenimientos = Mantenimiento::whereIn('id_maquinaria', $idsMaquinarias)
                ->where('estado', 'completado')
                ->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', $parteDiario->fecha)
                ->get();

            foreach ($mantenimientos as $mant) {
                $costoMaquinaria += (float) ($mant->costo_total ?? 0);
            }
        }

        $costoTotalDia = $costoManoObra + $costoInsumos + $costoMaquinaria;

        $costoUnitario = null;
        if (! $parteDiario->es_dia_caido && $totalToneladas > 0) {
            $costoUnitario = round($costoTotalDia / $totalToneladas, 2);
        }

        $parteDiario->updateQuietly([
            'costo_mano_obra' => round($costoManoObra, 2),
            'costo_insumos' => round($costoInsumos, 2),
            'costo_maquinaria' => round($costoMaquinaria, 2),
            'costo_total_dia' => round($costoTotalDia, 2),
            'costo_unitario_calculado' => $costoUnitario,
        ]);
    }
}
