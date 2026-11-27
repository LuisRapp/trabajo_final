<?php

use App\Models\Lote;
use App\Services\ClimaAnalisisService;
use App\Services\ClimaApiService;
use App\Services\ClimaConfiguracionService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    ClimaConfiguracionService::limpiarCache();
});

/**
 * Pronostico de 3 dias: domingo (auxiliar), lunes y martes.
 * El domingo llueve 30 mm todo de noche (20-22 h): deja barro el lunes.
 */
function pronosticoLluviaNocturnaDomingo(): array
{
    return [
        'daily' => [
            'time' => ['2026-11-29', '2026-11-30', '2026-12-01'],
            'precipitation_sum' => [30, 0, 0],
            'cloudcover_mean' => [50, 85, 10],
            'wind_speed_10m_max' => [5, 5, 5],
            'et0_fao_evapotranspiration' => [1, 1, 5],
        ],
        'hourly' => [
            'time' => [
                '2026-11-29T20:00', '2026-11-29T21:00', '2026-11-29T22:00',
                '2026-11-30T10:00', '2026-12-01T10:00',
            ],
            'precipitation' => [10, 10, 10, 0, 0],
        ],
    ];
}

it('marca barro el lunes cuando el domingo llovio de noche', function () {
    $analisis = app(ClimaAnalisisService::class)
        ->mapearDiasInactivos(pronosticoLluviaNocturnaDomingo(), false, null, 1);

    // El domingo es auxiliar: solo se emiten lunes y martes.
    expect($analisis['dias_detalle'])->toHaveCount(2)
        ->and($analisis['dias_detalle'][0]['fecha_str'])->toBe('30/11/2026')
        ->and($analisis['dias_detalle'][0]['estado'])->toBe('INACTIVO')
        ->and($analisis['dias_detalle'][0]['saturacion_index'])->toBe(15.0)
        ->and($analisis['dias_detalle'][0]['index'])->toBe(0)
        ->and($analisis['dia_cero_index'])->toBe(0)
        ->and($analisis['total_dias_perdidos'])->toBe(1);
});

it('no emite el dia auxiliar y conserva el indice de la posicion', function () {
    $analisis = app(ClimaAnalisisService::class)
        ->mapearDiasInactivos(pronosticoLluviaNocturnaDomingo(), false, null, 1);

    $fechas = array_map(fn ($d) => $d['fecha_str'], $analisis['dias_detalle']);

    expect($fechas)->toBe(['30/11/2026', '01/12/2026']);
});

it('sin dia auxiliar sigue emitiendo todos los dias del historico', function () {
    $analisis = app(ClimaAnalisisService::class)
        ->mapearDiasInactivos(pronosticoLluviaNocturnaDomingo(), true, null, 0);

    expect($analisis['dias_detalle'])->toHaveCount(3);
});

it('pide el dia anterior a Open-Meteo para calcular el barro', function () {
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => [], 'hourly' => []]),
    ]);

    $lote = Lote::factory()->create(['latitud' => -34.6, 'longitud' => -58.4]);

    app(ClimaApiService::class)->obtenerPronosticoCompleto($lote);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'past_days=1'));
});
