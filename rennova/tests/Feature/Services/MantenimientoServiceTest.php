<?php

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\PropuestaCompraMantenimiento;
use App\Models\PropuestaCompraMantenimientoInsumo;
use App\Models\TipoMantenimiento;
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

test('aprobar mantenimiento genera orden de compra solo para insumos obligatorios faltantes', function () {
    $tipo = TipoMantenimiento::factory()->preventivo()->create();
    $maquinaria = Maquinaria::factory()->create();
    $mantenimiento = Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
    ]);

    $obligatorio = Insumo::factory()->create();
    $opcional = Insumo::factory()->create();

    KitMantenimientoPreventivo::factory()->create([
        'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
        'id_insumo' => $obligatorio->id_insumo,
        'cantidad_requerida' => 10,
        'es_obligatorio' => true,
    ]);
    KitMantenimientoPreventivo::factory()->create([
        'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
        'id_insumo' => $opcional->id_insumo,
        'cantidad_requerida' => 5,
        'es_obligatorio' => false,
    ]);

    $service = app(MantenimientoService::class);
    $resultado = $service->aprobarMantenimiento($mantenimiento->id_mantenimiento);

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['compra_generada'])->toBeTrue()
        ->and($mantenimiento->fresh()->estado)->toBe('en curso');

    $propuesta = PropuestaCompraMantenimiento::query()
        ->where('id_mantenimiento', $mantenimiento->id_mantenimiento)
        ->first();

    expect($propuesta)->not->toBeNull();

    $insumosComprados = PropuestaCompraMantenimientoInsumo::query()
        ->where('id_propuesta_compra_mantenimiento', $propuesta->id_propuesta_compra_mantenimiento)
        ->get();

    expect($insumosComprados)->toHaveCount(1)
        ->and($insumosComprados->first()->id_insumo)->toBe($obligatorio->id_insumo);
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

test('aprobar un correctivo no verifica el kit ni genera propuesta de compra', function () {
    $tipo = TipoMantenimiento::factory()->correctivo()->create();
    $maquinaria = Maquinaria::factory()->create();
    $mantenimiento = Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
    ]);

    $insumo = Insumo::factory()->create();

    KitMantenimientoPreventivo::factory()->create([
        'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
        'id_insumo' => $insumo->id_insumo,
        'cantidad_requerida' => 10,
        'es_obligatorio' => true,
    ]);

    $resultado = app(MantenimientoService::class)->aprobarMantenimiento($mantenimiento->id_mantenimiento);

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['compra_generada'])->toBeFalse()
        ->and($mantenimiento->fresh()->estado)->toBe('en curso')
        ->and(PropuestaCompraMantenimiento::query()->count())->toBe(0);
});

test('no se puede completar una orden programada', function () {
    $mantenimiento = Mantenimiento::factory()->programado()->create();

    $resultado = app(MantenimientoService::class)
        ->completarMantenimiento($mantenimiento->id_mantenimiento, [], 0);

    expect($resultado['success'])->toBeFalse()
        ->and($resultado['message'])->toContain('en curso')
        ->and($mantenimiento->fresh()->estado)->toBe('programado');
});
