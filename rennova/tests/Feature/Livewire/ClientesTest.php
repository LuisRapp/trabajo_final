<?php

use App\Http\Livewire\Clientes;
use App\Models\Cliente;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'crear-clientes', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'editar-clientes', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/clientes')
        ->assertRedirect('/login');
});

test('usuario autenticado puede renderizar componente', function () {
    $user = Usuario::factory()->create();

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->assertStatus(200);
});

test('puede crear un cliente', function () {
    $user = Usuario::factory()->create();

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->set('razon_social', 'Acme SRL')
        ->set('cuit', '30112233445')
        ->set('direccion', 'Av. Siempre Viva 123')
        ->set('contacto', 'Juan Pérez')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Cliente::where('cuit', '30112233445')->exists())->toBeTrue();
});

test('puede editar un cliente manteniendo el cuit', function () {
    $user = Usuario::factory()->create();
    $cliente = Cliente::factory()->create(['razon_social' => 'Viejo Nombre']);

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->call('editar', $cliente->id_cliente)
        ->set('razon_social', 'Nuevo Nombre')
        ->set('cuit', $cliente->cuit)
        ->set('direccion', $cliente->direccion)
        ->set('contacto', $cliente->contacto)
        ->call('guardar')
        ->assertHasNoErrors();

    expect($cliente->fresh()->razon_social)->toBe('Nuevo Nombre');
});

test('puede eliminar un cliente', function () {
    $user = Usuario::factory()->create();
    $cliente = Cliente::factory()->create();

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->call('eliminar', $cliente->id_cliente)
        ->assertHasNoErrors();

    expect($cliente->fresh()->trashed())->toBeTrue();
});
