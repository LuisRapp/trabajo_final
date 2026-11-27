<?php

use App\Models\TipoMantenimiento;
use Database\Seeders\CatalogoMantenimientoSeeder;

test('el catalogo de mantenimiento crea los tipos con su comportamiento', function () {
    $this->seed(CatalogoMantenimientoSeeder::class);

    $tipos = TipoMantenimiento::query()->orderBy('nombre')->get();

    expect($tipos->pluck('nombre')->all())->toBe(['Correctivo', 'Preventivo'])
        ->and($tipos->map(fn ($tipo) => $tipo->comportamiento->value)->all())
        ->toBe(['correctivo', 'preventivo']);
});

test('el catalogo de mantenimiento es idempotente', function () {
    $this->seed(CatalogoMantenimientoSeeder::class);
    $this->seed(CatalogoMantenimientoSeeder::class);

    expect(TipoMantenimiento::query()->count())->toBe(2);
});

test('el catalogo de mantenimiento restaura un tipo dado de baja sin cambiar su comportamiento', function () {
    $tipo = TipoMantenimiento::factory()->create([
        'nombre' => 'Preventivo',
        'comportamiento' => 'preventivo',
    ]);
    $tipo->delete();

    $this->seed(CatalogoMantenimientoSeeder::class);

    $restaurado = TipoMantenimiento::query()->where('nombre', 'Preventivo')->first();

    expect($restaurado)->not->toBeNull()
        ->and($restaurado->comportamiento->value)->toBe('preventivo')
        ->and(TipoMantenimiento::query()->count())->toBe(2);
});

test('el catalogo no duplica un tipo preventivo renombrado', function () {
    $preventivo = TipoMantenimiento::factory()->preventivo()->create(['nombre' => 'Service 500 hs']);

    $this->seed(CatalogoMantenimientoSeeder::class);

    expect(TipoMantenimiento::query()->where('comportamiento', 'preventivo')->count())->toBe(1)
        ->and(TipoMantenimiento::query()->where('nombre', 'Preventivo')->exists())->toBeFalse()
        ->and($preventivo->fresh()->nombre)->toBe('Service 500 hs')
        ->and(TipoMantenimiento::query()->count())->toBe(2);
});
