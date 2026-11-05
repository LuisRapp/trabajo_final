<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\ParteDiario;
use Illuminate\Support\Facades\DB;

class PanelControlService
{
    /**
     * Obtiene todas las métricas del panel de control para el mes en curso.
     */
    public function obtenerMetricas(): array
    {
        $mes = now()->month;
        $anio = now()->year;

        return [
            'hayLotesActivos' => $this->hayLotesActivos(),
            'toneladasExtraidas' => $this->toneladasExtraidas($mes, $anio),
            'cargasMes' => $this->cargasDelMes($mes, $anio),
            'diasOperativos' => $this->diasOperativos($mes, $anio),
            'costoPromedioTn' => $this->costoPromedioPorTonelada($mes, $anio),
            'precioVentaPromedioTn' => $this->precioVentaPromedioPorTonelada($mes, $anio),
            'mantenimientosPendientes' => $this->mantenimientosPendientes(),
            'proyeccion' => $this->proyeccionVsEquilibrio(),
        ];
    }

    private function hayLotesActivos(): bool
    {
        return Lote::where('estado', 'activo')->exists();
    }

    private function toneladasExtraidas(int $mes, int $anio): float
    {
        return Carga::whereMonth('fecha_carga', $mes)
            ->whereYear('fecha_carga', $anio)
            ->sum('peso_neto') / 1000;
    }

    private function cargasDelMes(int $mes, int $anio): int
    {
        return Carga::whereMonth('fecha_carga', $mes)
            ->whereYear('fecha_carga', $anio)
            ->count();
    }

    private function diasOperativos(int $mes, int $anio): int
    {
        return ParteDiario::whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->where('es_dia_caido', false)
            ->distinct('fecha')
            ->count('fecha');
    }

    private function costoPromedioPorTonelada(int $mes, int $anio): float
    {
        $toneladas = $this->toneladasExtraidas($mes, $anio);

        if ($toneladas <= 0) {
            return 0;
        }

        $costoTotal = ParteDiario::whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('costo_total_dia');

        return $costoTotal / $toneladas;
    }

    private function precioVentaPromedioPorTonelada(int $mes, int $anio): float
    {
        // Promedio ponderado por toneladas (consistente con ForestalStatsService)
        $row = DB::table('venta_cargas as vc')
            ->join('ventas', 'vc.id_venta', '=', 'ventas.id_recibo')
            ->whereNull('ventas.deleted_at')
            ->whereMonth('ventas.fecha_emision', $mes)
            ->whereYear('ventas.fecha_emision', $anio)
            ->selectRaw('SUM(COALESCE(vc.subtotal, vc.precio_unitario * vc.peso_toneladas)) as monto_total')
            ->selectRaw('SUM(vc.peso_toneladas) as ton_total')
            ->first();

        $montoTotal = (float) ($row->monto_total ?? 0);
        $tonTotal = (float) ($row->ton_total ?? 0);

        if ($tonTotal <= 0) {
            return 0.0;
        }

        return round($montoTotal / $tonTotal, 2);
    }

    private function mantenimientosPendientes(): int
    {
        return Mantenimiento::where('estado', 'pendiente')->count();
    }

    private function proyeccionVsEquilibrio(): array
    {
        return app(ForestalStatsService::class)->getProyeccionVsEquilibrio();
    }
}
