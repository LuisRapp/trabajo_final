<?php

use App\Models\Lote;
use App\Services\CargaService;

test('el formulario de cargas solo ofrece lotes activos o en proceso', function () {
    $activo = Lote::factory()->create(['propietario' => 'Activo SA', 'estado' => 'activo']);
    $proceso = Lote::factory()->create(['propietario' => 'Proceso SA', 'estado' => 'en_proceso']);
    Lote::factory()->create(['propietario' => 'Cerrado SA', 'estado' => 'cerrado']);
    Lote::factory()->create(['propietario' => 'Inactivo SA', 'estado' => 'inactivo']);

    $ids = app(CargaService::class)->listarLotes()->pluck('id_lote')->all();

    expect($ids)->toContain($activo->id_lote)
        ->and($ids)->toContain($proceso->id_lote)
        ->and($ids)->toHaveCount(2);
});
