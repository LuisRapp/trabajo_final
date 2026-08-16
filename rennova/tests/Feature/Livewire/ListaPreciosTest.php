<?php

use App\Http\Livewire\ListaPrecios;
use App\Models\CategoriaClientePrecio;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'crear-precios', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'editar-precios', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/lista-precios')
        ->assertRedirect('/login');
});

test('usuario autenticado puede renderizar componente', function () {
    $user = Usuario::factory()->create();

    Livewire::actingAs($user)
        ->test(ListaPrecios::class)
        ->assertStatus(200);
});

test('puede crear un precio', function () {
    $user = Usuario::factory()->create();
    $cliente = Cliente::factory()->create();
    $categoria = CategoriaMadera::factory()->create();

    Livewire::actingAs($user)
        ->test(ListaPrecios::class)
        ->set('cliente_id', $cliente->id_cliente)
        ->set('categoria_id', $categoria->id_categoria_madera)
        ->set('precio', 150.50)
        ->set('fecha_desde', now()->toDateString())
        ->set('fecha_hasta', null)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(CategoriaClientePrecio::where('cliente_id', $cliente->id_cliente)
        ->where('categoria_id', $categoria->id_categoria_madera)
        ->exists())->toBeTrue();
});

test('puede editar un precio', function () {
    $user = Usuario::factory()->create();
    $precio = CategoriaClientePrecio::factory()->create(['precio' => 100]);

    Livewire::actingAs($user)
        ->test(ListaPrecios::class)
        ->call('editar', $precio->id)
        ->set('precio', 200)
        ->set('fecha_desde', $precio->fecha_desde->toDateString())
        ->set('cliente_id', $precio->cliente_id)
        ->set('categoria_id', $precio->categoria_id)
        ->call('guardar')
        ->assertHasNoErrors();

    expect((float) $precio->fresh()->precio)->toBe(200.0);
});

test('puede eliminar un precio', function () {
    $user = Usuario::factory()->create();
    $precio = CategoriaClientePrecio::factory()->create();

    Livewire::actingAs($user)
        ->test(ListaPrecios::class)
        ->call('eliminar', $precio->id)
        ->assertHasNoErrors();

    expect($precio->fresh()->trashed())->toBeTrue();
});
