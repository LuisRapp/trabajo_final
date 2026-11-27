<?php

use App\Models\Lote;
use App\Services\ClimaEstrategiaService;

function analisisConLluviaHoy(int $diasOperativos, float $riesgo, int $diasPerdidos = 3): array
{
    return [
        'volumen_riesgo' => $riesgo,
        'total_dias_perdidos' => $diasPerdidos,
        'dias_operativos_previos' => 0,
        'dias_operativos_posterior' => $diasOperativos,
        'total_dias_operativos' => $diasOperativos,
        'meta_diaria' => 100.0,
        'dia_cero_index' => 0,
        'dias_detalle' => [
            ['fecha' => '2026-11-27', 'fecha_str' => '27/11/2026', 'dia_semana' => 'viernes', 'estado' => 'INACTIVO', 'razon' => 'Lluvia diurna activa'],
            ['fecha' => '2026-11-30', 'fecha_str' => '30/11/2026', 'dia_semana' => 'lunes', 'estado' => 'OPERATIVO', 'razon' => null],
        ],
    ];
}

it('planifica la recuperacion si la lluvia es hoy pero quedan dias operativos', function () {
    $lote = Lote::factory()->create();

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(4, 300));

    expect($estrategia['nivel_urgencia'])->not->toBe('CRITICA')
        ->and($estrategia['estrategia'])->toStartWith('ANTICIPACION')
        ->and($estrategia['datos_calculados']['dias_perdidos'])->toBe(3);
});

it('solo cae en reaccion cuando no hay ningun dia operativo', function () {
    $lote = Lote::factory()->create();

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(0, 300));

    expect($estrategia['nivel_urgencia'])->toBe('CRITICA')
        ->and($estrategia['estrategia'])->toBe('REACCION');
});
