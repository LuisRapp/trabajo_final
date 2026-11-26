<?php

use App\Models\Lote;
use App\Services\CargaService;

test('el formulario de cargas solo ofrece lotes en proceso', function () {
    $activo = Lote::factory()->create(['propietario' => 'Activo SA', 'estado' => 'activo']);
    $proceso = Lote::factory()->create(['propietario' => 'Proceso SA', 'estado' => 'en_proceso']);
    Lote::factory()->create(['propietario' => 'Cerrado SA', 'estado' => 'cerrado']);
    Lote::factory()->create(['propietario' => 'Inactivo SA', 'estado' => 'inactivo']);

    $ids = app(CargaService::class)->listarLotes()->pluck('id_lote')->all();

    expect($ids)->toBe([$proceso->id_lote])
        ->and($ids)->not->toContain($activo->id_lote);
});

test('al editar se incluye el lote actual aunque no este en proceso', function () {
    $activo = Lote::factory()->create(['estado' => 'activo']);
    Lote::factory()->create(['estado' => 'en_proceso']);

    $ids = app(CargaService::class)->listarLotes($activo->id_lote)->pluck('id_lote')->all();

    expect($ids)->toContain($activo->id_lote);
});
