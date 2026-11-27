<?php

use App\Http\Livewire\TipoMantenimiento;
use App\Models\TipoMantenimiento as TipoMantenimientoModel;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento', 'eliminar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->assertStatus(200);
});

test('puede crear un tipo de mantenimiento', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento', 'eliminar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->set('nombre', 'Preventivo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(TipoMantenimientoModel::where('nombre', 'Preventivo')->exists())->toBeTrue();
});

test('puede editar un tipo de mantenimiento', function () {
    $tipo = TipoMantenimientoModel::factory()->create(['nombre' => 'Viejo']);

    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento', 'eliminar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->call('editar', $tipo->id_tipo_mantenimiento)
        ->set('nombre', 'Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($tipo->fresh()->nombre)->toBe('Nuevo');
});

test('puede eliminar un tipo de mantenimiento (soft delete)', function () {
    $tipo = TipoMantenimientoModel::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento', 'eliminar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->call('eliminar', $tipo->id_tipo_mantenimiento);

    expect($tipo->fresh()->trashed())->toBeTrue();
});

test('valida nombre obligatorio', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento', 'eliminar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);
});

test('la pagina de tipos de mantenimiento carga con permiso y monta el componente', function () {
    $this->actingAs(usuarioConPermisos(['ver-tipos-mantenimiento']))
        ->get(route('tipos-mantenimiento.index'))
        ->assertOk()
        ->assertSeeLivewire(TipoMantenimiento::class);
});

test('la pagina de tipos de mantenimiento exige el permiso ver', function () {
    $this->actingAs(usuarioConPermisos(['ver-insumos']))
        ->get(route('tipos-mantenimiento.index'))
        ->assertForbidden();
});

test('crea un tipo con comportamiento preventivo', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->set('nombre', 'Service 500 hs')
        ->set('comportamiento', 'preventivo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(
        TipoMantenimientoModel::where('nombre', 'Service 500 hs')->first()->comportamiento->value
    )->toBe('preventivo');
});

test('editar el comportamiento de un tipo lo actualiza', function () {
    $tipo = TipoMantenimientoModel::factory()->create([
        'nombre' => 'Cambio de aceite',
        'comportamiento' => 'otro',
    ]);

    Livewire::actingAs(usuarioConPermisos(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento']))
        ->test(TipoMantenimiento::class)
        ->call('editar', $tipo->id_tipo_mantenimiento)
        ->set('comportamiento', 'preventivo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($tipo->fresh()->comportamiento->value)->toBe('preventivo');
});

test('el sidebar enlaza tipos de mantenimiento con el permiso', function () {
    $this->actingAs(usuarioConPermisos(['ver-tipos-mantenimiento']))
        ->get(route('tipos-mantenimiento.index'))
        ->assertOk()
        ->assertSee('Tipos de mantenimiento')
        ->assertSee(route('tipos-mantenimiento.index'), false);
});

test('el sidebar no enlaza tipos de mantenimiento sin el permiso', function () {
    $this->actingAs(usuarioConPermisos(['ver-insumos']))
        ->get(route('insumos.index'))
        ->assertOk()
        ->assertDontSee(route('tipos-mantenimiento.index'), false);
});
