<?php

use App\Http\Livewire\TipoMantenimiento;
use App\Models\TipoMantenimiento as TipoMantenimientoModel;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(TipoMantenimiento::class)
        ->assertStatus(200);
});

test('puede crear un tipo de mantenimiento', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(TipoMantenimiento::class)
        ->set('nombre', 'Preventivo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(TipoMantenimientoModel::where('nombre', 'Preventivo')->exists())->toBeTrue();
});

test('puede editar un tipo de mantenimiento', function () {
    $tipo = TipoMantenimientoModel::factory()->create(['nombre' => 'Viejo']);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(TipoMantenimiento::class)
        ->call('editar', $tipo->id_tipo_mantenimiento)
        ->set('nombre', 'Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($tipo->fresh()->nombre)->toBe('Nuevo');
});

test('puede eliminar un tipo de mantenimiento (soft delete)', function () {
    $tipo = TipoMantenimientoModel::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(TipoMantenimiento::class)
        ->call('eliminar', $tipo->id_tipo_mantenimiento);

    expect($tipo->fresh()->trashed())->toBeTrue();
});

test('valida nombre obligatorio', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(TipoMantenimiento::class)
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);
});
