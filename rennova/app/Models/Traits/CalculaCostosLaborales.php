<?php

namespace App\Models\Traits;

use App\Models\HistoricoRolLaboral;

trait CalculaCostosLaborales
{
    /**
     * Calcular el costo de mano de obra de este empleado para un día específico.
     *
     * @param  string|\DateTimeInterface  $fecha
     * @param  bool  $esDiaCaido
     * @param  \Illuminate\Support\Collection  $cargasDelDia - Colección de cargas donde participó este empleado
     * @return float
     */
    public function calcularCostoDia($fecha, $esDiaCaido, $cargasDelDia = null)
    {
        // Obtener valores vigentes para la fecha desde histórico o rol actual
        $valorJornal = 0;
        $tarifaPorTonelada = 0;

        $rolId = $this->id_rol_laboral ?? $this->rolLaboral->id_rol_laboral ?? null;

        if ($rolId) {
            // Buscar histórico vigente en la fecha
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
            } else {
                // Fallback a valores actuales del rol
                if ($this->rolLaboral) {
                    $valorJornal = (float) ($this->rolLaboral->jornal_diario ?? $this->rolLaboral->costo_diario ?? 0);
                    $tarifaPorTonelada = (float) ($this->rolLaboral->precio_tonelada ?? 0);
                }
            }
        }

        // CASO 1: Día Caído - retornar jornal diario
        if ($esDiaCaido) {
            return round($valorJornal, 2);
        }

        // CASO 2: Día de Producción - calcular por destajo
        if (!$cargasDelDia || $cargasDelDia->isEmpty()) {
            return 0.0;
        }

        $totalToneladasEmpleado = 0.0;

        foreach ($cargasDelDia as $carga) {
            // Dividir el peso_neto entre los empleados asignados a esta carga
            $cantidadEmpleados = $carga->empleados->count();
            
            if ($cantidadEmpleados > 0) {
                // peso_neto está en kilos, convertir a toneladas
                $pesoAsignadoKg = $carga->peso_neto / $cantidadEmpleados;
                $pesoAsignadoTon = $pesoAsignadoKg / 1000;
                $totalToneladasEmpleado += $pesoAsignadoTon;
            }
        }

        // Calcular costo por destajo
        $costoDestajo = $totalToneladasEmpleado * $tarifaPorTonelada;

        return round($costoDestajo, 2);
    }
}
