<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Mantenimiento;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;

class ParteDiarioCostoService
{
    /**
     * Calcula y persiste todos los componentes de costo de un parte diario.
     *
     * Calcula tres componentes:
     * - Mano de obra: suma de costos de empleados vía EmpleadoPagoService
     * - Insumos: suma de costos de movimientos FIFO
     * - Maquinaria: costos de alquiler por tonelada + costos de mantenimientos completados
     *
     * También calcula el costo unitario (costo por tonelada) cuando no es día caído y hay toneladas > 0.
     * Los resultados se guardan directamente en el modelo ParteDiario vía updateQuietly().
     *
     * @param  \App\Models\ParteDiario  $parteDiario  Parte diario cuyo costo se calcula
     */
    public static function calcularYGuardarCostos(ParteDiario $parteDiario): void
    {
        $costoManoObra = 0.0;
        $costoInsumos = 0.0;
        $costoMaquinaria = 0.0;
        $totalToneladas = 0.0;

        $empleados = $parteDiario->empleados()->with('rolLaboral')->get();

        // Pre-load all cargas for this date with empleados to avoid N+1
        $cargasDelDia = Carga::whereDate('fecha_carga', $parteDiario->fecha)
            ->with('empleados')
            ->get();

        if (! $empleados->isEmpty()) {
            foreach ($empleados as $empleado) {
                $cargasDelEmpleado = $cargasDelDia->filter(function ($carga) use ($empleado) {
                    return $carga->empleados->contains('id_empleado', $empleado->id_empleado);
                });

                $costoEmpleado = EmpleadoPagoService::calcularCostoDia(
                    $empleado,
                    $parteDiario->fecha,
                    $parteDiario->es_dia_caido,
                    $cargasDelEmpleado
                );

                $costoManoObra += $costoEmpleado;
            }
        }

        $movimientos = MovimientoStock::delParteDiario($parteDiario->id_parte_diario, $parteDiario->fecha)
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
