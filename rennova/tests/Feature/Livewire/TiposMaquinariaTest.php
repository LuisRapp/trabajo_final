<?php

use App\Http\Livewire\TiposMaquinaria;
use App\Models\TipoMaquinaria;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-maquinaria', 'editar-tipos-maquinaria', 'eliminar-tipos-maquinaria']))
        ->test(TiposMaquinaria::class)
        ->assertStatus(200);
});

test('puede crear un tipo de maquinaria', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-maquinaria', 'editar-tipos-maquinaria', 'eliminar-tipos-maquinaria']))
        ->test(TiposMaquinaria::class)
        ->set('nombre', 'Motosierra')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(TipoMaquinaria::where('nombre', 'Motosierra')->exists())->toBeTrue();
});

test('puede editar un tipo de maquinaria', function () {
    $tipo = TipoMaquinaria::factory()->create(['nombre' => 'Viejo']);

    Livewire::actingAs(usuarioConPermisos(['crear-tipos-maquinaria', 'editar-tipos-maquinaria', 'eliminar-tipos-maquinaria']))
        ->test(TiposMaquinaria::class)
        ->call('editar', $tipo->id_tipo_maquinaria)
        ->set('nombre', 'Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($tipo->fresh()->nombre)->toBe('Nuevo');
});

test('puede eliminar un tipo de maquinaria (soft delete)', function () {
    $tipo = TipoMaquinaria::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-tipos-maquinaria', 'editar-tipos-maquinaria', 'eliminar-tipos-maquinaria']))
        ->test(TiposMaquinaria::class)
        ->call('eliminar', $tipo->id_tipo_maquinaria);

    expect($tipo->fresh()->trashed())->toBeTrue();
});

test('valida nombre obligatorio', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-maquinaria', 'editar-tipos-maquinaria', 'eliminar-tipos-maquinaria']))
        ->test(TiposMaquinaria::class)
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);
});
