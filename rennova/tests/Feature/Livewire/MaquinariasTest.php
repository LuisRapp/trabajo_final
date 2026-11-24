<?php

use App\Http\Livewire\Maquinarias;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
        ->test(Maquinarias::class)
        ->assertStatus(200);
});

test('puede crear una maquinaria', function () {
    $tipo = TipoMaquinaria::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
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

    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
        ->test(Maquinarias::class)
        ->call('editar', $maquinaria->id_maquinaria)
        ->set('modelo', 'JD-9999')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($maquinaria->fresh()->modelo)->toBe('JD-9999');
});

test('puede eliminar una maquinaria (soft delete)', function () {
    $maquinaria = Maquinaria::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
        ->test(Maquinarias::class)
        ->call('eliminar', $maquinaria->id_maquinaria);

    expect($maquinaria->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
        ->test(Maquinarias::class)
        ->call('guardar')
        ->assertHasErrors(['id_tipo_maquinaria', 'modelo', 'estado']);
});

test('guarda una maquinaria dejando "Es alquilada" en el valor por defecto', function () {
    // Regresion: el select muestra "No" pero la propiedad arrancaba en null,
    // asi que el required fallaba y el form no guardaba ni mostraba el motivo.
    $tipo = TipoMaquinaria::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-maquinarias', 'editar-maquinarias', 'eliminar-maquinarias']))
        ->test(Maquinarias::class)
        ->set('id_tipo_maquinaria', $tipo->id_tipo_maquinaria)
        ->set('modelo', 'JD-6600')
        ->set('estado', 'disponible')
        ->set('fecha_inicio_actividades', '2020-01-01')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('es_alquilada', 0);

    $maquinaria = Maquinaria::where('modelo', 'JD-6600')->first();

    expect($maquinaria)->not->toBeNull()
        ->and((bool) $maquinaria->es_alquilada)->toBeFalse();
});
