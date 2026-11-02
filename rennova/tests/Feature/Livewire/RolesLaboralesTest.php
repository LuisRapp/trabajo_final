<?php

use App\Http\Livewire\RolesLaborales;
use App\Models\RolLaboral;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(RolesLaborales::class)
        ->assertStatus(200);
});

test('puede crear un rol laboral', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(RolesLaborales::class)
        ->set('nombre', 'Operario')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(RolLaboral::where('nombre', 'Operario')->exists())->toBeTrue();
});

test('puede editar un rol laboral', function () {
    $rol = RolLaboral::factory()->create(['nombre' => 'Viejo']);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(RolesLaborales::class)
        ->call('editar', $rol->id_rol_laboral)
        ->set('nombre', 'Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($rol->fresh()->nombre)->toBe('Nuevo');
});

test('puede eliminar un rol laboral (soft delete)', function () {
    $rol = RolLaboral::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(RolesLaborales::class)
        ->call('eliminar', $rol->id_rol_laboral);

    expect($rol->fresh()->trashed())->toBeTrue();
});

test('valida nombre obligatorio', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(RolesLaborales::class)
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);
});
