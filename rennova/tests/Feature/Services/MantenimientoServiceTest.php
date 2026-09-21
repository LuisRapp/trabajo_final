<?php

use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Services\InventarioService;
use App\Services\MantenimientoService;

test('aprobar mantenimiento cambia el estado a en curso', function () {
    $mantenimiento = Mantenimiento::factory()->programado()->create();
    $service = app(MantenimientoService::class);

    $resultado = $service->aprobarMantenimiento($mantenimiento->id_mantenimiento);

    expect($resultado['success'])->toBeTrue();
    expect($mantenimiento->fresh()->estado)->toBe('en curso');
});

test('aprobar mantenimiento no programado falla', function () {
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();
    $service = app(MantenimientoService::class);

    $resultado = $service->aprobarMantenimiento($mantenimiento->id_mantenimiento);

    expect($resultado['success'])->toBeFalse();
    expect($mantenimiento->fresh()->estado)->toBe('en curso');
});

test('completar mantenimiento cambia el estado a completado con stock suficiente', function () {
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();
    $insumo = Insumo::factory()->create();
    InventarioService::registrarEntrada($insumo->id_insumo, 100, 50);

    $service = app(MantenimientoService::class);
    $resultado = $service->completarMantenimiento(
        $mantenimiento->id_mantenimiento,
        [
            [
                'id_insumo' => $insumo->id_insumo,
                'cantidad_utilizada' => 10,
                'costo_unitario' => 60,
            ],
        ],
        500
    );

    expect($resultado['success'])->toBeTrue();
    expect($mantenimiento->fresh()->estado)->toBe('completado');
    expect((float) $insumo->fresh()->stock)->toBe(90.0);

    $orden = $mantenimiento->fresh();
    expect((float) $orden->costo_mano_obra)->toBe(500.0)
        ->and((float) $orden->costo_total)->toBe(1000.0)
        ->and($orden->fecha_fin)->not->toBeNull()
        // El snapshot del odómetro resetea el ciclo del proceso de umbrales (PA-01)
        ->and($orden->toneladas_snapshot)->not->toBeNull();
});

test('completar mantenimiento con fecha de cierre personalizada la respeta', function () {
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();
    $fechaCierre = now()->subDay()->toDateString();

    $service = app(MantenimientoService::class);
    $resultado = $service->completarMantenimiento(
        $mantenimiento->id_mantenimiento,
        [],
        100,
        $fechaCierre
    );

    expect($resultado['success'])->toBeTrue()
        ->and($mantenimiento->fresh()->fecha_fin)->toBe($fechaCierre);
});

test('completar mantenimiento sin stock suficiente falla y no genera stock negativo', function () {
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();
    $insumo = Insumo::factory()->create();

    $service = app(MantenimientoService::class);
    $resultado = $service->completarMantenimiento(
        $mantenimiento->id_mantenimiento,
        [
            [
                'id_insumo' => $insumo->id_insumo,
                'cantidad_utilizada' => 10,
                'costo_unitario' => 60,
            ],
        ]
    );

    expect($resultado['success'])->toBeFalse();
    expect($mantenimiento->fresh()->estado)->not->toBe('completado');
    expect((float) $insumo->fresh()->stock)->toBe(0.0);
});

test('completar mantenimiento ya completado falla', function () {
    $mantenimiento = Mantenimiento::factory()->completado()->create();
    $insumo = Insumo::factory()->create();
    InventarioService::registrarEntrada($insumo->id_insumo, 100, 50);

    $service = app(MantenimientoService::class);
    $resultado = $service->completarMantenimiento(
        $mantenimiento->id_mantenimiento,
        [
            [
                'id_insumo' => $insumo->id_insumo,
                'cantidad_utilizada' => 5,
                'costo_unitario' => 60,
            ],
        ]
    );

    expect($resultado['success'])->toBeFalse();
    expect((float) $insumo->fresh()->stock)->toBe(100.0);
});
