<?php

use App\Http\Livewire\CategoriasMadera;
use App\Models\CategoriaMadera;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(CategoriasMadera::class)
        ->assertStatus(200);
});

test('puede crear una categoría de madera', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(CategoriasMadera::class)
        ->set('nombre', 'Pino')
        ->set('descripcion', 'Madera de pino')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(CategoriaMadera::where('nombre', 'Pino')->exists())->toBeTrue();
});

test('puede editar una categoría de madera', function () {
    $categoria = CategoriaMadera::factory()->create(['nombre' => 'Viejo Nombre']);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(CategoriasMadera::class)
        ->call('editar', $categoria->id_categoria_madera)
        ->set('nombre', 'Nuevo Nombre')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($categoria->fresh()->nombre)->toBe('Nuevo Nombre');
});

test('puede eliminar una categoría de madera (soft delete)', function () {
    $categoria = CategoriaMadera::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(CategoriasMadera::class)
        ->call('eliminar', $categoria->id_categoria_madera);

    expect($categoria->fresh()->trashed())->toBeTrue();
});

test('valida nombre obligatorio al crear', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(CategoriasMadera::class)
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);
});
