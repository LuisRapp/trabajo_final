<?php

use App\Http\Livewire\Insumos;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'crear-insumos', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'editar-insumos', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/insumos')
        ->assertRedirect('/login');
});

test('usuario autenticado puede renderizar componente', function () {
    $user = Usuario::factory()->create();

    Livewire::actingAs($user)
        ->test(Insumos::class)
        ->assertStatus(200);
});

test('puede crear un insumo', function () {
    $user = Usuario::factory()->create();
    $unidad = UnidadMedida::factory()->create();
    $proveedor = Proveedor::factory()->create();

    Livewire::actingAs($user)
        ->test(Insumos::class)
        ->set('nombre', 'Aceite de prueba')
        ->set('descripcion', 'Descripción de prueba')
        ->set('id_unidad_medida', $unidad->id_unidad_medida)
        ->set('id_proveedor', $proveedor->id_proveedor)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Insumo::where('nombre', 'Aceite de prueba')->exists())->toBeTrue();
});

test('puede editar un insumo', function () {
    $user = Usuario::factory()->create();
    $proveedor = Proveedor::factory()->create();
    $insumo = Insumo::factory()->create([
        'nombre' => 'Viejo Nombre',
        'id_proveedor' => $proveedor->id_proveedor,
    ]);

    Livewire::actingAs($user)
        ->test(Insumos::class)
        ->call('editar', $insumo->id_insumo)
        ->set('nombre', 'Nuevo Nombre')
        ->set('descripcion', $insumo->descripcion)
        ->set('id_unidad_medida', $insumo->id_unidad_medida)
        ->set('id_proveedor', $insumo->id_proveedor)
        ->call('guardar')
        ->assertHasNoErrors();

    expect($insumo->fresh()->nombre)->toBe('Nuevo Nombre');
});

test('puede eliminar un insumo', function () {
    $user = Usuario::factory()->create();
    $insumo = Insumo::factory()->create();

    Livewire::actingAs($user)
        ->test(Insumos::class)
        ->call('eliminar', $insumo->id_insumo)
        ->assertHasNoErrors();

    expect($insumo->fresh()->trashed())->toBeTrue();
});
