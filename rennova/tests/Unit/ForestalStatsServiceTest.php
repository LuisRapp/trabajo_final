<?php

use App\Services\ForestalStatsService;
use Illuminate\Support\Carbon;

it('cuenta solo días hábiles (lun-vie) del mes', function () {
    $service = new ForestalStatsService;

    // Noviembre 2026: 30 días, 9 de fin de semana → 21 hábiles
    expect($service->diasHabilesDelMes(Carbon::parse('2026-11-15')))->toBe(21);
});

it('proyecta el mes y detecta déficit cuando no alcanza el objetivo', function () {
    $service = new ForestalStatsService;

    $resultado = $service->calcularProyeccionMensual(100.0, 90.0, 21);

    expect($resultado['objetivo_mensual_tn'])->toBe(2100.0);
    expect($resultado['proyeccion_mensual_tn'])->toBe(1890.0);
    expect($resultado['deficit_tn'])->toBe(210.0);
    expect($resultado['dias_habiles_mes'])->toBe(21);
    expect($resultado['cumple_objetivo'])->toBeFalse();
});

it('marca cumplimiento cuando la proyección supera el objetivo', function () {
    $service = new ForestalStatsService;

    $resultado = $service->calcularProyeccionMensual(80.0, 90.0, 21);

    expect($resultado['deficit_tn'])->toBe(-210.0);
    expect($resultado['cumple_objetivo'])->toBeTrue();
});
