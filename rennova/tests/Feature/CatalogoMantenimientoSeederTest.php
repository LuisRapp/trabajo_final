<?php

use App\Models\TipoMantenimiento;
use Database\Seeders\CatalogoMantenimientoSeeder;

test('el catalogo de mantenimiento crea los tipos preventivo y correctivo', function () {
    $this->seed(CatalogoMantenimientoSeeder::class);

    expect(
        TipoMantenimiento::query()->orderBy('nombre')->pluck('nombre')->all()
    )->toBe(['Correctivo', 'Preventivo']);
});

test('el catalogo de mantenimiento es idempotente', function () {
    $this->seed(CatalogoMantenimientoSeeder::class);
    $this->seed(CatalogoMantenimientoSeeder::class);

    expect(TipoMantenimiento::query()->count())->toBe(2);
});

test('el catalogo de mantenimiento restaura un tipo dado de baja', function () {
    $tipo = TipoMantenimiento::factory()->create(['nombre' => 'Preventivo']);
    $tipo->delete();

    $this->seed(CatalogoMantenimientoSeeder::class);

    expect(TipoMantenimiento::query()->where('nombre', 'Preventivo')->exists())->toBeTrue()
        ->and(TipoMantenimiento::query()->count())->toBe(2);
});
