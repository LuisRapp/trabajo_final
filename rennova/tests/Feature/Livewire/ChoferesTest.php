<?php

use App\Http\Livewire\Choferes;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Choferes::class)
        ->assertStatus(200);
});

test('puede crear un chofer', function () {
    $cliente = Cliente::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Choferes::class)
        ->set('id_cliente', $cliente->id_cliente)
        ->set('apellido', 'Perez')
        ->set('nombre', 'Juan')
        ->set('dni', '12345678')
        ->set('telefono', '11-1234-5678')
        ->set('direccion', 'Calle Falsa 123')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Chofer::where('dni', '12345678')->exists())->toBeTrue();
});

test('puede editar un chofer', function () {
    $cliente = Cliente::factory()->create();
    $chofer = Chofer::create([
        'id_cliente' => $cliente->id_cliente,
        'apellido' => 'Perez',
        'nombre' => 'Juan',
        'dni' => '12345678',
        'estado' => true,
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Choferes::class)
        ->call('editar', $chofer->id_chofer)
        ->set('apellido', 'Gomez')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($chofer->fresh()->apellido)->toBe('Gomez');
});

test('puede eliminar un chofer (soft delete)', function () {
    $cliente = Cliente::factory()->create();
    $chofer = Chofer::create([
        'id_cliente' => $cliente->id_cliente,
        'apellido' => 'Perez',
        'nombre' => 'Juan',
        'dni' => '87654321',
        'estado' => true,
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Choferes::class)
        ->call('eliminar', $chofer->id_chofer);

    expect($chofer->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Choferes::class)
        ->call('guardar')
        ->assertHasErrors(['id_cliente', 'apellido', 'nombre', 'dni']);
});
