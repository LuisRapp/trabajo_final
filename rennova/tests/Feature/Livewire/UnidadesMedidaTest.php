<?php

use App\Http\Livewire\UnidadesMedida;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(UnidadesMedida::class)
        ->assertStatus(200);
});

test('puede crear una unidad de medida', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(UnidadesMedida::class)
        ->set('nombre', 'Metro cúbico')
        ->set('abreviatura', 'm3')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(UnidadMedida::where('nombre', 'Metro cúbico')->exists())->toBeTrue();
});

test('puede editar una unidad de medida', function () {
    $unidad = UnidadMedida::factory()->create(['nombre' => 'Viejo']);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(UnidadesMedida::class)
        ->call('editar', $unidad->id_unidad_medida)
        ->set('nombre', 'Nuevo')
        ->set('abreviatura', 'NN')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($unidad->fresh()->nombre)->toBe('Nuevo');
});

test('puede eliminar una unidad de medida (soft delete)', function () {
    $unidad = UnidadMedida::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(UnidadesMedida::class)
        ->call('eliminar', $unidad->id_unidad_medida);

    expect($unidad->fresh()->trashed())->toBeTrue();
});

test('valida nombre y abreviatura obligatorios', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(UnidadesMedida::class)
        ->set('nombre', '')
        ->set('abreviatura', '')
        ->call('guardar')
        ->assertHasErrors(['nombre', 'abreviatura']);
});
