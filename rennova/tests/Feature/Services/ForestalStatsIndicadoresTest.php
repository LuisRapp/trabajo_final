<?php

use App\Models\Carga;
use App\Models\Lote;
use App\Services\ForestalStatsService;

function crearCarga(int $loteId, string $fecha, float $pesoNetoKg): Carga
{
    return Carga::factory()->create([
        'id_lote' => $loteId,
        'fecha_carga' => $fecha,
        'peso_neto' => $pesoNetoKg,
    ]);
}

it('calcula tn por dia operativo contando dias distintos con carga', function () {
    $lote = Lote::factory()->create();
    $desde = '2026-10-01';
    $hasta = '2026-10-31';

    crearCarga($lote->id_lote, '2026-10-15 08:00:00', 40000); // 40 tn
    crearCarga($lote->id_lote, '2026-10-15 15:00:00', 20000); // 20 tn, mismo dia
    crearCarga($lote->id_lote, '2026-10-16 09:00:00', 60000); // 60 tn

    // Total 120 tn en 2 dias operativos (15 y 16 de octubre)
    $service = new ForestalStatsService;
    $tnDia = $service->getTnDiaOperativo($lote, $desde, $hasta);

    expect($tnDia)->toBe(60.0);
});

it('devuelve 0 tn por dia operativo cuando no hay cargas', function () {
    $lote = Lote::factory()->create();

    $service = new ForestalStatsService;
    $tnDia = $service->getTnDiaOperativo($lote, '2026-10-01', '2026-10-31');

    expect($tnDia)->toBe(0.0);
});

it('calcula tn por dia operativo global con dias distintos entre lotes', function () {
    $loteA = Lote::factory()->create();
    $loteB = Lote::factory()->create();
    $desde = '2026-10-01';
    $hasta = '2026-10-31';

    // Lote A: 50 tn el 15; Lote B: 30 tn el 15 y 20 tn el 16
    crearCarga($loteA->id_lote, '2026-10-15 08:00:00', 50000);
    crearCarga($loteB->id_lote, '2026-10-15 09:00:00', 30000);
    crearCarga($loteB->id_lote, '2026-10-16 09:00:00', 20000);

    $service = new ForestalStatsService;
    $tnDia = $service->getTnDiaOperativoGlobal(
        [$loteA->id_lote, $loteB->id_lote],
        $desde,
        $hasta
    );

    // 100 tn en 2 dias operativos (el 15 y el 16), no 3
    expect($tnDia)->toBe(50.0);
});

it('devuelve 0 tn por dia operativo global sin lotes', function () {
    $service = new ForestalStatsService;

    expect($service->getTnDiaOperativoGlobal([], '2026-10-01', '2026-10-31'))->toBe(0.0);
});

it('construye la tendencia mensual comparando solo meses cerrados', function () {
    $lote = Lote::factory()->create();

    $mesAnterior = now()->subMonths(2)->startOfMonth();
    $mesPasado = now()->subMonths(1)->startOfMonth();
    $mesActual = now()->startOfMonth();

    crearCarga($lote->id_lote, $mesAnterior->copy()->addDays(5)->toDateTimeString(), 100000); // 100 tn
    crearCarga($lote->id_lote, $mesPasado->copy()->addDays(5)->toDateTimeString(), 50000); // 50 tn
    crearCarga($lote->id_lote, $mesActual->copy()->addDays(2)->toDateTimeString(), 20000); // 20 tn

    $service = new ForestalStatsService;
    $tendencia = $service->getTendenciaProduccionMensual(
        [$lote->id_lote],
        $mesAnterior->toDateString(),
        now()->toDateString()
    );

    expect($tendencia)->toHaveCount(3);

    // Primer mes (cerrado): sin variacion por no haber mes anterior
    expect($tendencia[0]['parcial'])->toBeFalse()
        ->and($tendencia[0]['toneladas'])->toBe(100.0)
        ->and($tendencia[0]['variacion_pct'])->toBeNull();

    // Segundo mes (cerrado): -50% respecto al anterior
    expect($tendencia[1]['parcial'])->toBeFalse()
        ->and($tendencia[1]['toneladas'])->toBe(50.0)
        ->and($tendencia[1]['variacion_pct'])->toBe(-50.0);

    // Mes actual (parcial): se marca, no se compara contra un mes completo
    expect($tendencia[2]['parcial'])->toBeTrue()
        ->and($tendencia[2]['toneladas'])->toBe(20.0)
        ->and($tendencia[2]['variacion_pct'])->toBeNull();
});

it('no compara un mes cerrado contra un mes anterior sin produccion', function () {
    $lote = Lote::factory()->create();

    $mesAnterior = now()->subMonths(2)->startOfMonth();
    $mesPasado = now()->subMonths(1)->startOfMonth();

    // Mes anterior sin cargas; mes pasado con 30 tn
    crearCarga($lote->id_lote, $mesPasado->copy()->addDays(3)->toDateTimeString(), 30000);

    $service = new ForestalStatsService;
    $tendencia = $service->getTendenciaProduccionMensual(
        [$lote->id_lote],
        $mesAnterior->toDateString(),
        $mesPasado->copy()->endOfMonth()->toDateString()
    );

    expect($tendencia[1]['variacion_pct'])->toBeNull()
        ->and($tendencia[1]['parcial'])->toBeFalse();
});
