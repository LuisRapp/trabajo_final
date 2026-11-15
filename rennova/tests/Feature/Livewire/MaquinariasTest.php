<?php

use App\Http\Livewire\Maquinarias;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Maquinarias::class)
        ->assertStatus(200);
});

test('puede crear una maquinaria', function () {
    $tipo = TipoMaquinaria::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Maquinarias::class)
        ->set('id_tipo_maquinaria', $tipo->id_tipo_maquinaria)
        ->set('modelo', 'JD-5500')
        ->set('estado', 'disponible')
        ->set('es_alquilada', false)
        ->set('fecha_inicio_actividades', '2020-01-01')
        ->set('umbral_toneladas', 10000)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Maquinaria::where('modelo', 'JD-5500')->exists())->toBeTrue();
});

test('puede editar una maquinaria', function () {
    $maquinaria = Maquinaria::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Maquinarias::class)
        ->call('editar', $maquinaria->id_maquinaria)
        ->set('modelo', 'JD-9999')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($maquinaria->fresh()->modelo)->toBe('JD-9999');
});

test('puede eliminar una maquinaria (soft delete)', function () {
    $maquinaria = Maquinaria::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Maquinarias::class)
        ->call('eliminar', $maquinaria->id_maquinaria);

    expect($maquinaria->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Maquinarias::class)
        ->call('guardar')
        ->assertHasErrors(['id_tipo_maquinaria', 'modelo', 'estado']);
});
