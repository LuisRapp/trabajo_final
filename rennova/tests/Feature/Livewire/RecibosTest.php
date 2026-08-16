<?php

use App\Http\Livewire\Recibos;
use App\Models\Empleado;
use App\Models\Recibo;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'crear-recibos', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'editar-recibos', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/recibos')
        ->assertRedirect('/login');
});

test('usuario autenticado puede renderizar componente', function () {
    $user = Usuario::factory()->create();

    Livewire::actingAs($user)
        ->test(Recibos::class)
        ->assertStatus(200);
});

test('puede crear un recibo', function () {
    $user = Usuario::factory()->create();
    $empleado = Empleado::factory()->create();

    Livewire::actingAs($user)
        ->test(Recibos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('fecha_emision', now()->toDateString())
        ->set('monto_bruto', 10000)
        ->set('descuentos', 1000)
        ->set('observaciones', 'Observación de prueba')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Recibo::where('id_empleado', $empleado->id_empleado)->exists())->toBeTrue();
});

test('puede editar un recibo', function () {
    $user = Usuario::factory()->create();
    $recibo = Recibo::factory()->create(['monto_bruto' => 5000, 'descuentos' => 0]);

    Livewire::actingAs($user)
        ->test(Recibos::class)
        ->call('editar', $recibo->id_recibo)
        ->set('monto_bruto', 8000)
        ->set('descuentos', 500)
        ->set('fecha_emision', $recibo->fecha_emision->toDateString())
        ->set('id_empleado', $recibo->id_empleado)
        ->call('guardar')
        ->assertHasNoErrors();

    $recibo = $recibo->fresh();
    expect((float) $recibo->monto_bruto)->toBe(8000.0);
    expect((float) $recibo->monto)->toBe(7500.0);
});

test('puede eliminar un recibo', function () {
    $user = Usuario::factory()->create();
    $recibo = Recibo::factory()->create();

    Livewire::actingAs($user)
        ->test(Recibos::class)
        ->call('eliminar', $recibo->id_recibo)
        ->assertHasNoErrors();

    expect($recibo->fresh()->trashed())->toBeTrue();
});
