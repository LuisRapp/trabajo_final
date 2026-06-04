<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use Carbon\Carbon;

/**
 * Servicio de Análisis Climático
 *
 * Responsable de procesar datos climáticos y calcular métricas operativas
 */
class ClimaAnalisisService
{
    const UMBRAL_LLUVIA = 10; // mm

    const UMBRAL_NUBOSIDAD = 60; // %

    /**
     * Mapeo de Días Inactivos
     *
     * Reglas clave para reducir falsos positivos:
     * 1) Granularidad horaria: si precipitación diaria > 10mm, solo es INACTIVO
     *    si la lluvia acumulada entre 06:00 y 18:00 supera 5mm.
     * 2) Saturación de suelo: Saturacion_Index = Lluvia_Hoy + (Lluvia_Ayer * 0.5)
     *    Riesgo si > 12mm.
     * 3) Factor de secado: si viento_max > 15 km/h O ET0 > 4mm, puede ser OPERATIVO
     *    aunque esté nublado. Solo INACTIVO si saturación alta + nubosidad alta + poco viento.
     *
     * @return array [
     *               'dias_detalle' => array de cada día con estado,
     *               'total_dias_perdidos' => int,
     *               'volumen_riesgo' => float,
     *               'dia_cero_index' => int|null (primer día INACTIVO por clima),
     *               'dias_operativos_previos' => int (días operativos antes del Día Cero)
     *               ]
     */
    public function mapearDiasInactivos(array $pronostico, bool $ignorarFinDeSemana = false): array
    {
        $fechas = $pronostico['daily']['time'] ?? [];
        $precipitaciones = $pronostico['daily']['precipitation_sum'] ?? [];
        $nubosidades = $pronostico['daily']['cloudcover_mean']
            ?? $pronostico['daily']['cloud_cover_mean']
            ?? [];
        $vientosMax = $pronostico['daily']['wind_speed_10m_max'] ?? [];
        $et0s = $pronostico['daily']['et0_fao_evapotranspiration'] ?? [];

        $horas = $pronostico['hourly']['time'] ?? [];
        $precipitacionHoraria = $pronostico['hourly']['precipitation'] ?? [];

        $diasDetalle = [];
        $diaCeroIndex = null;
        $totalDiasPerdidos = 0;

        // Pre-procesar lluvia por franjas para evitar O(n^2)
        $lluviaPorFecha = [];
        foreach ($horas as $hIndex => $horaStr) {
            $fechaKey = substr((string) $horaStr, 0, 10);
            $hour = (int) substr((string) $horaStr, 11, 2);
            $mm = $precipitacionHoraria[$hIndex] ?? 0;

            if (! isset($lluviaPorFecha[$fechaKey])) {
                $lluviaPorFecha[$fechaKey] = ['madrugada' => 0, 'diurna' => 0, 'nocturna' => 0];
            }

            if ($hour >= 0 && $hour < 6) {
                $lluviaPorFecha[$fechaKey]['madrugada'] += $mm;
            } elseif ($hour >= 6 && $hour < 18) {
                $lluviaPorFecha[$fechaKey]['diurna'] += $mm;
            } else {
                $lluviaPorFecha[$fechaKey]['nocturna'] += $mm;
            }
        }

        foreach ($fechas as $index => $fechaStr) {
            $fecha = Carbon::parse($fechaStr);
            $mm = $precipitaciones[$index] ?? 0;
            $cloudCover = $nubosidades[$index] ?? 0;
            $vientoMax = $vientosMax[$index] ?? 0;
            $et0 = $et0s[$index] ?? 0;
            $esHoy = $fecha->isToday();
            $esFinDeSemana = $fecha->isWeekend(); // Sábado (6) o Domingo (0)

            $fechaKey = $fecha->toDateString();
            $madrugada = $lluviaPorFecha[$fechaKey]['madrugada'] ?? null;
            $lluviaDiurna = $lluviaPorFecha[$fechaKey]['diurna'] ?? null;
            $lluviaNocturna = $lluviaPorFecha[$fechaKey]['nocturna'] ?? null;
            $hasHourly = array_key_exists($fechaKey, $lluviaPorFecha);
            if (! $hasHourly) {
                // Fallback conservador si falta data horaria
                $madrugada = 0;
                $lluviaDiurna = $mm;
                $lluviaNocturna = 0;
            }
            $lluviaAyer = ($index > 0) ? ($precipitaciones[$index - 1] ?? 0) : 0;
            $saturacionIndex = $mm + ($lluviaAyer * 0.5);
            $saturacionAlta = $saturacionIndex > 12;
            $secadoActivo = ($vientoMax > 15) || ($et0 > 4);
            $ayerKey = $index > 0 ? Carbon::parse($fechas[$index - 1])->toDateString() : null;
            $madrugadaAyer = $ayerKey ? ($lluviaPorFecha[$ayerKey]['madrugada'] ?? 0) : 0;
            $lluviaDiurnaAyer = $ayerKey ? ($lluviaPorFecha[$ayerKey]['diurna'] ?? 0) : 0;
            $lluviaRealAyer = ($lluviaAyer >= self::UMBRAL_LLUVIA) && ($madrugadaAyer > 2 || $lluviaDiurnaAyer > 5);
            $lluviaRealHoy = ($mm >= self::UMBRAL_LLUVIA) && ($madrugada > 2 || $lluviaDiurna > 5);

            // Analizar estado del día
            $estado = 'OPERATIVO';
            $razon = null;

            // 0. Verificar si es fin de semana (NO cuenta como día perdido, solo es no laboral)
            if ($esFinDeSemana && ! $ignorarFinDeSemana) {
                $estado = 'INACTIVO';
                $razon = 'Fin de semana (no laboral)';
                // NO incrementar totalDiasPerdidos - los fines de semana no generan déficit
            }
            // 1. Lluvia real en ventana operativa
            elseif ($lluviaRealHoy) {
                $estado = 'INACTIVO';
                $razon = ($madrugada > 2)
                    ? "Barro por lluvia de madrugada ({$madrugada} mm)"
                    : 'Lluvia diurna activa';

                if ($diaCeroIndex === null) {
                    $diaCeroIndex = $index;
                }

                $totalDiasPerdidos++;
            } else {
                // 2. Lluvia nocturna (operativo condicional)
                if ($mm >= self::UMBRAL_LLUVIA && $lluviaDiurna <= 5) {
                    $estado = 'OPERATIVO_CONDICIONAL';
                    $razon = 'Lluvia nocturna';
                }
                // 3. Barro por saturación + nubosidad + poco viento (solo si ayer hubo lluvia real)
                elseif ($saturacionAlta && $cloudCover > self::UMBRAL_NUBOSIDAD && ! $secadoActivo && $lluviaRealAyer) {
                    $estado = 'INACTIVO';
                    $razon = 'Saturación alta + nubosidad + poco viento';

                    if ($diaCeroIndex === null) {
                        $diaCeroIndex = $index;
                    }

                    $totalDiasPerdidos++;
                }
            }

            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'dia_semana' => $fecha->isoFormat('dddd'),
                'es_hoy' => $esHoy,
                'precipitacion_mm' => round($mm, 1),
                'nubosidad' => round($cloudCover, 0),
                'viento_max' => round($vientoMax, 1),
                'et0' => round($et0, 1),
                'lluvia_madrugada_mm' => round((float) $madrugada, 1),
                'lluvia_diurna_mm' => round((float) $lluviaDiurna, 1),
                'lluvia_nocturna_mm' => round((float) $lluviaNocturna, 1),
                'saturacion_index' => round($saturacionIndex, 2),
                'estado' => $estado,
                'razon' => $razon,
                'index' => $index,
            ];
        }

        // Calcular días operativos previos al Día Cero
        $diasOperativosPrevios = 0;
        $diasOperativosPosterior = 0;
        $totalDiasOperativos = 0;

        if ($diaCeroIndex !== null) {
            // Contar días operativos ANTES del primer día de lluvia
            for ($i = 0; $i < $diaCeroIndex; $i++) {
                if (in_array($diasDetalle[$i]['estado'], ['OPERATIVO', 'OPERATIVO_CONDICIONAL'], true)) {
                    $diasOperativosPrevios++;
                }
            }

            // Contar días operativos DESPUÉS de la ventana de lluvia
            for ($i = $diaCeroIndex + 1; $i < count($diasDetalle); $i++) {
                if (in_array($diasDetalle[$i]['estado'], ['OPERATIVO', 'OPERATIVO_CONDICIONAL'], true)) {
                    $diasOperativosPosterior++;
                }
            }
        }

        // Contar TODOS los días operativos en la ventana de 7 días
        foreach ($diasDetalle as $dia) {
            if (in_array($dia['estado'], ['OPERATIVO', 'OPERATIVO_CONDICIONAL'], true)) {
                $totalDiasOperativos++;
            }
        }

        // Calcular volumen de riesgo (días perdidos × meta diaria estimada)
        $metaDiaria = $this->calcularMetaDiaria();
        $volumenRiesgo = $totalDiasPerdidos * $metaDiaria;

        return [
            'dias_detalle' => $diasDetalle,
            'total_dias_perdidos' => $totalDiasPerdidos,
            'volumen_riesgo' => round($volumenRiesgo, 2),
            'meta_diaria' => $metaDiaria,
            'dia_cero_index' => $diaCeroIndex,
            'dias_operativos_previos' => $diasOperativosPrevios,
            'dias_operativos_posterior' => $diasOperativosPosterior,
            'total_dias_operativos' => $totalDiasOperativos,
        ];
    }

    /**
     * Calcula la meta diaria estimada basada en partes diarios históricos
     */
    public function calcularMetaDiaria(): float
    {
        // Calcular promedio de los últimos 30 días operativos
        $promedioHistorico = ParteDiario::where('fecha', '>=', Carbon::now()->subDays(30))
            ->whereHas('cargas')
            ->withSum('cargas', 'peso_neto')
            ->get()
            ->avg('cargas_sum_peso_neto');

        // Convertir de kilos a toneladas
        $promedioToneladas = $promedioHistorico ? $promedioHistorico / 1000.0 : 50;

        return round($promedioToneladas, 2); // Default 50 ton si no hay histórico
    }

    /**
     * Calcula costo estructural diario (mano de obra + maquinaria)
     */
    public function calcularCostoEstructuralDiario(Lote $lote): float
    {
        $costoTotal = 0;

        // A) Costo de empleados activos (sin fecha_fin_actividades o fecha futura)
        $empleadosActivos = \App\Models\Empleado::where(function ($query) {
            $query->whereNull('fecha_fin_actividades')
                ->orWhere('fecha_fin_actividades', '>', Carbon::today());
        })->get();

        foreach ($empleadosActivos as $empleado) {
            try {
                $costoTotal += EmpleadoPagoService::calcularCostoDia($empleado, Carbon::today(), true, null);
            } catch (\Exception $e) {
                // Si no hay histórico, ignorar este empleado
            }
        }

        // B) Costo de maquinaria en alquiler
        $maquinariasAlquiladas = Maquinaria::where('es_alquilada', true)->get();

        foreach ($maquinariasAlquiladas as $maquinaria) {
            // Estimar costo diario: usar toneladas_acumuladas si existe
            $toneladas = $maquinaria->toneladas_acumuladas ?? 10;
            $precio = $maquinaria->tipoMaquinaria->precio_alquiler_destajo ?? 0;
            $costoTotal += $precio * min($toneladas, 10); // Max 10 ton estimadas
        }

        return round($costoTotal, 2);
    }

    /**
     * Extrae solo días inactivos del análisis
     */
    public function extraerDiasInactivos(array $diasDetalle): array
    {
        return array_filter($diasDetalle, fn ($dia) => $dia['estado'] === 'INACTIVO');
    }
}
