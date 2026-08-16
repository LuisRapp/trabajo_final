<?php

use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Models\Usuario;
use App\Services\InventarioService;

test('usuario autenticado sin permiso no puede aprobar mantenimiento', function () {
    $user = Usuario::factory()->create();
    $mantenimiento = Mantenimiento::factory()->programado()->create();

    $this->actingAs($user)
        ->post(route('mantenimientos.aprobar', $mantenimiento->id_mantenimiento))
        ->assertForbidden();
});

test('usuario autenticado sin permiso no puede completar mantenimiento', function () {
    $user = Usuario::factory()->create();
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();

    $this->actingAs($user)
        ->post(route('mantenimientos.completar', $mantenimiento->id_mantenimiento), [
            'insumos' => [],
            'costo_mano_obra' => 0,
        ])
        ->assertForbidden();
});

test('usuario con permiso puede aprobar mantenimiento programado', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('confirmar-mantenimiento');
    $mantenimiento = Mantenimiento::factory()->programado()->create();

    $response = $this->actingAs($user)
        ->post(route('mantenimientos.aprobar', $mantenimiento->id_mantenimiento));

    $response->assertOk();
    $response->assertJson(['success' => true]);
    expect($mantenimiento->fresh()->estado)->toBe('en curso');
});

test('usuario con permiso puede completar mantenimiento con stock suficiente', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('completar-mantenimiento');
    $mantenimiento = Mantenimiento::factory()->enCurso()->create();
    $insumo = Insumo::factory()->create();
    InventarioService::registrarEntrada($insumo->id_insumo, 100, 50);

    $response = $this->actingAs($user)
        ->post(route('mantenimientos.completar', $mantenimiento->id_mantenimiento), [
            'insumos' => [
                [
                    'id_insumo' => $insumo->id_insumo,
                    'cantidad_utilizada' => 10,
                    'costo_unitario' => 60,
                ],
            ],
            'costo_mano_obra' => 500,
        ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
    expect($mantenimiento->fresh()->estado)->toBe('completado');
});
