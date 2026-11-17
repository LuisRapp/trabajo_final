<?php

use App\Http\Livewire\Proveedores;
use App\Models\Proveedor;
use Livewire\Livewire;

test('invitado es redirigido al login', function () {
    $this->get('/proveedores')
        ->assertRedirect('/login');
});

test('usuario autenticado puede renderizar componente', function () {
    $user = usuarioConPermisos(['crear-proveedores', 'editar-proveedores', 'eliminar-proveedores']);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->assertStatus(200);
});

test('puede crear un proveedor', function () {
    $user = usuarioConPermisos(['crear-proveedores', 'editar-proveedores', 'eliminar-proveedores']);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->set('razon_social', 'Proveedor Test')
        ->set('cuit', '30112233445')
        ->set('direccion', 'Calle Falsa 123')
        ->set('telefono', '11-1234-5678')
        ->set('email', 'test@proveedor.com')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Proveedor::where('cuit', '30112233445')->exists())->toBeTrue();
});

test('puede editar un proveedor manteniendo el cuit', function () {
    $user = usuarioConPermisos(['crear-proveedores', 'editar-proveedores', 'eliminar-proveedores']);
    $proveedor = Proveedor::factory()->create(['razon_social' => 'Viejo Nombre']);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('editar', $proveedor->id_proveedor)
        ->set('razon_social', 'Nuevo Nombre')
        ->set('cuit', $proveedor->cuit)
        ->set('direccion', $proveedor->direccion)
        ->set('telefono', $proveedor->telefono)
        ->set('email', $proveedor->email)
        ->call('guardar')
        ->assertHasNoErrors();

    expect($proveedor->fresh()->razon_social)->toBe('Nuevo Nombre');
});

test('puede eliminar un proveedor', function () {
    $user = usuarioConPermisos(['crear-proveedores', 'editar-proveedores', 'eliminar-proveedores']);
    $proveedor = Proveedor::factory()->create();

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('eliminar', $proveedor->id_proveedor)
        ->assertHasNoErrors();

    expect($proveedor->fresh()->trashed())->toBeTrue();
});
