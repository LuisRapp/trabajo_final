<?php

namespace App\Http\Livewire\Traits;

use App\Models\Lote;
use App\Services\ClimaOperativoService;
use Carbon\Carbon;

trait ClimaOperativoTrait
{
    /**
     * Resolve climate state for the current lote and date.
     *
     * Updates component properties (clima_estado, clima_razon, clima_fuente,
     * clima_es_fin_de_semana, clima_requiere_override) and returns structured array.
     *
     * @return array{requiere_override: bool, estado: ?string, razon: ?string, fuente: ?string}
     */
    protected function resolverClima(): array
    {
        $this->resetClimaState();

        if (! $this->id_lote || ! $this->fecha) {
            $this->clima_override_confirmado = false;
            $this->clima_override_motivo = '';

            return [
                'requiere_override' => false,
                'estado' => null,
                'razon' => null,
                'fuente' => null,
            ];
        }

        $fecha = Carbon::parse($this->fecha);
        $this->clima_es_fin_de_semana = $fecha->isWeekend();

        $lote = Lote::find($this->id_lote);
        if (! $lote) {
            return [
                'requiere_override' => $this->clima_es_fin_de_semana,
                'estado' => null,
                'razon' => null,
                'fuente' => null,
            ];
        }

        $climaDia = app(ClimaOperativoService::class)->obtenerEstadoDia($lote, $fecha);
        $estado = strtoupper((string) ($climaDia->estado_pronostico ?? $climaDia->estado_operativo ?? 'OPERATIVO'));

        $this->clima_estado = $estado;
        $this->clima_razon = $climaDia->razon_pronostico ?? $climaDia->razon ?? null;
        $this->clima_fuente = $climaDia->fuente_pronostico ?? $climaDia->fuente ?? null;
        $this->clima_requiere_override = $this->clima_es_fin_de_semana || $estado === 'INACTIVO';

        if (! $this->clima_requiere_override) {
            $this->clima_override_confirmado = false;
            $this->clima_override_motivo = '';
        }

        return [
            'requiere_override' => $this->clima_requiere_override,
            'estado' => $estado,
            'razon' => $this->clima_razon,
            'fuente' => $this->clima_fuente,
        ];
    }

    private function resetClimaState(): void
    {
        $this->clima_estado = null;
        $this->clima_razon = null;
        $this->clima_fuente = null;
        $this->clima_es_fin_de_semana = false;
        $this->clima_requiere_override = false;
    }
}
