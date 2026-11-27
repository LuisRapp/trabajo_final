<?php

use App\Services\ClimaAnalisisService;
use App\Services\ClimaConfiguracionService;

beforeEach(function () {
    ClimaConfiguracionService::limpiarCache();
});

/**
 * @param  float  $mmDiario  Acumulado diario (mm)
 * @param  array<int, array{0: string, 1: float}>  $horas  [hora ISO, mm]
 */
function pronosticoUnDia(float $mmDiario, array $horas): array
{
    return [
        'daily' => [
            'time' => ['2026-11-27'],
            'precipitation_sum' => [$mmDiario],
            'cloudcover_mean' => [70],
            'wind_speed_10m_max' => [5],
            'et0_fao_evapotranspiration' => [1],
        ],
        'hourly' => [
            'time' => array_map(fn ($h) => $h[0], $horas),
            'precipitation' => array_map(fn ($h) => $h[1], $horas),
        ],
    ];
}

it('marca inactivo un dia con lluvia diurna aunque no llegue a 10 mm', function () {
    // Hoy: 8,9 mm en total, pero 5,6 mm entre 06 y 18.
    $analisis = app(ClimaAnalisisService::class)->mapearDiasInactivos(
        pronosticoUnDia(8.9, [
            ['2026-11-27T14:00', 1.9],
            ['2026-11-27T15:00', 2.9],
            ['2026-11-27T16:00', 0.8],
        ]),
        false,
        null,
        0
    );

    expect($analisis['dias_detalle'][0]['estado'])->toBe('INACTIVO')
        ->and($analisis['total_dias_perdidos'])->toBe(1);
});

it('mantiene operativo condicional un dia que solo llueve de noche', function () {
    $analisis = app(ClimaAnalisisService::class)->mapearDiasInactivos(
        pronosticoUnDia(12, [
            ['2026-11-27T20:00', 6],
            ['2026-11-27T21:00', 6],
        ]),
        false,
        null,
        0
    );

    expect($analisis['dias_detalle'][0]['estado'])->toBe('OPERATIVO_CONDICIONAL');
});

it('marca inactivo por lluvia de madrugada cuando el dia trae lluvia significativa', function () {
    $analisis = app(ClimaAnalisisService::class)->mapearDiasInactivos(
        pronosticoUnDia(12, [
            ['2026-11-27T03:00', 4],
        ]),
        false,
        null,
        0
    );

    expect($analisis['dias_detalle'][0]['estado'])->toBe('INACTIVO');
});
