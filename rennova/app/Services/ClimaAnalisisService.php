<?php

namespace App\Services;

use App\Models\HistoricoCostosMaquinaria;
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
    public function mapearDiasInactivos(array $pronostico, bool $ignorarFinDeSemana = false, ?Lote $lote = null): array
    {
        $fechas = $pronostico['daily']['time'] ?? [];
        $precipitaciones = $pronostico['daily']['precipitation_sum'] ?? [];
        $nubosidades = $pronostico['daily']['cloudcover_mean']
            ?? $pronostico['daily']['cloud_cover_mean']
            ?? [];
        $vientosMax = $pronostico['daily']['wind_speed_10m_max'] ?? [];
        $et0s = $pronostico['daily']['et0_fao_evapotranspiration'] ?? [];

        $climaConfig = app(ClimaConfiguracionService::class);
        $umbralLluvia = (float) $climaConfig->valor('umbral_lluvia_mm', self::UMBRAL_LLUVIA);
        $umbralNubosidad = (float) $climaConfig->valor('umbral_nubosidad_pct', self::UMBRAL_NUBOSIDAD);
        $saturacionMm = (float) $climaConfig->valor('saturacion_mm', 12);
        $secadoViento = (float) $climaConfig->valor('secado_viento_kmh', 15);
        $secadoEt0 = (float) $climaConfig->valor('secado_et0_mm', 4);
        $umbralMadrugada = (float) $climaConfig->valor('lluvia_madrugada_mm', 2);
        $umbralDiurna = (float) $climaConfig->valor('lluvia_diurna_mm', 5);

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
            $saturacionAlta = $saturacionIndex > $saturacionMm;
            $secadoActivo = ($vientoMax > $secadoViento) || ($et0 > $secadoEt0);
            $ayerKey = $index > 0 ? Carbon::parse($fechas[$index - 1])->toDateString() : null;
            $madrugadaAyer = $ayerKey ? ($lluviaPorFecha[$ayerKey]['madrugada'] ?? 0) : 0;
            $lluviaDiurnaAyer = $ayerKey ? ($lluviaPorFecha[$ayerKey]['diurna'] ?? 0) : 0;
            $lluviaRealAyer = ($lluviaAyer >= $umbralLluvia) && ($madrugadaAyer > $umbralMadrugada || $lluviaDiurnaAyer > $umbralDiurna);
            $lluviaRealHoy = ($mm >= $umbralLluvia) && ($madrugada > $umbralMadrugada || $lluviaDiurna > $umbralDiurna);

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
                if ($mm >= $umbralLluvia && $lluviaDiurna <= $umbralDiurna) {
                    $estado = 'OPERATIVO_CONDICIONAL';
                    $razon = 'Lluvia nocturna';
                }
                // 3. Barro por saturación + nubosidad + poco viento (solo si ayer hubo lluvia real)
                elseif ($saturacionAlta && $cloudCover > $umbralNubosidad && ! $secadoActivo && $lluviaRealAyer) {
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

        // Calcular volumen de riesgo (días perdidos × meta diaria DEL LOTE)
        $meta = $this->calcularMetaDiaria($lote);
        $metaDiaria = $meta['valor'];
        $volumenRiesgo = $totalDiasPerdidos * $metaDiaria;

        return [
            'dias_detalle' => $diasDetalle,
            'total_dias_perdidos' => $totalDiasPerdidos,
            'volumen_riesgo' => round($volumenRiesgo, 2),
            'meta_diaria' => $metaDiaria,
            'meta_fuente' => $meta['fuente'],
            'dia_cero_index' => $diaCeroIndex,
            'dias_operativos_previos' => $diasOperativosPrevios,
            'dias_operativos_posterior' => $diasOperativosPosterior,
            'total_dias_operativos' => $totalDiasOperativos,
        ];
    }

    /**
     * Calcula la meta diaria estimada basada en partes diarios históricos
     */
    /**
     * Produccion diaria promedio del lote (o global si no se indica lote).
     *
     * Se usan los ultimos 30 dias operativos; si no alcanza, todo el historico
     * del lote. Sin produccion devuelve 0 y fuente 'sin_datos': no se inventa
     * un deficit con una meta global.
     *
     * @return array{valor: float, fuente: string}
     */
    public function calcularMetaDiaria(?Lote $lote = null): array
    {
        $base = ParteDiario::query()->whereHas('cargas');

        if ($lote) {
            $base->where('id_lote', $lote->id_lote);
        }

        $reciente = (clone $base)
            ->where('fecha', '>=', Carbon::now()->subDays(30))
            ->withSum('cargas', 'peso_neto')
            ->get()
            ->avg('cargas_sum_peso_neto');

        if ($reciente) {
            return [
                'valor' => round($reciente / 1000.0, 2),
                'fuente' => $lote ? 'lote_30d' : 'global_30d',
            ];
        }

        $historico = $base->withSum('cargas', 'peso_neto')->get()->avg('cargas_sum_peso_neto');

        if ($historico) {
            return [
                'valor' => round($historico / 1000.0, 2),
                'fuente' => $lote ? 'lote_historico' : 'global_historico',
            ];
        }

        return ['valor' => 0.0, 'fuente' => 'sin_datos'];
    }

    /**
     * Calcula costo estructural diario (mano de obra + maquinaria)
     */
    public function calcularCostoEstructuralDiario(Lote $lote): float
    {
        $costo = 0.0;

        // Mano de obra: solo los empleados asignados al lote (jornal del dia).
        foreach ($lote->empleados()->with('rolLaboral')->get() as $empleado) {
            try {
                $costo += EmpleadoPagoService::calcularCostoDia($empleado, now()->toDateString(), true, null);
            } catch (\Exception $e) {
                // Sin historico de rol: se omite ese empleado.
            }
        }

        // Alquiler: la produccion diaria esperada del lote por el costo por tonelada.
        $metaDiaria = $this->calcularMetaDiaria($lote)['valor'];

        foreach ($lote->maquinarias()->where('es_alquilada', true)->get() as $maquinaria) {
            $precio = (float) (HistoricoCostosMaquinaria::query()
                ->where('id_maquinaria', $maquinaria->id_maquinaria)
                ->vigenteEnFecha(now())
                ->value('costo_por_tonelada') ?? 0);

            $costo += $metaDiaria * $precio;
        }

        return round($costo, 2);
    }

    /**
     * Extrae solo días inactivos del análisis
     */
    public function extraerDiasInactivos(array $diasDetalle): array
    {
        return array_filter($diasDetalle, fn ($dia) => $dia['estado'] === 'INACTIVO');
    }
}
