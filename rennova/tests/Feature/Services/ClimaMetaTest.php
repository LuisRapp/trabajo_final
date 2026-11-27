<?php

use App\Models\Carga;
use App\Models\Lote;
use App\Models\ParteDiario;
use App\Services\ClimaAnalisisService;

function parteConPeso(Lote $lote, float $kg, string $fecha): void
{
    $parte = ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => $fecha,
        'es_dia_caido' => false,
    ]);

    Carga::factory()->create([
        'id_parte_diario' => $parte->id_parte_diario,
        'id_lote' => $lote->id_lote,
        'fecha_carga' => $fecha,
        'peso_neto' => $kg,
    ]);
}

it('calcula la meta diaria con la produccion de cada lote', function () {
    $loteA = Lote::factory()->create();
    $loteB = Lote::factory()->create();
    $fecha = now()->subDays(3)->toDateString();

    parteConPeso($loteA, 1000, $fecha);
    parteConPeso($loteB, 5000, $fecha);

    $servicio = app(ClimaAnalisisService::class);

    expect($servicio->calcularMetaDiaria($loteA)['valor'])->toBe(1.0)
        ->and($servicio->calcularMetaDiaria($loteB)['valor'])->toBe(5.0)
        ->and($servicio->calcularMetaDiaria()['valor'])->toBe(3.0);
});

it('devuelve sin_datos cuando el lote no tiene produccion', function () {
    $lote = Lote::factory()->create();

    $meta = app(ClimaAnalisisService::class)->calcularMetaDiaria($lote);

    expect($meta['valor'])->toBe(0.0)
        ->and($meta['fuente'])->toBe('sin_datos');
});
