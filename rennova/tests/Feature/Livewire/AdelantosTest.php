<?php

use App\Http\Livewire\Adelantos;
use App\Models\Adelanto;
use App\Models\Empleado;
use App\Models\RolLaboral;
use App\Models\Usuario;
use Livewire\Livewire;

function crearEmpleadoValido(): Empleado
{
    $rol = RolLaboral::factory()->create();

    return Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);
}

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Adelantos::class)
        ->assertStatus(200);
});

test('puede crear un adelanto', function () {
    $empleado = crearEmpleadoValido();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 1500)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Adelanto::where('id_empleado', $empleado->id_empleado)->exists())->toBeTrue();
});

test('puede editar un adelanto', function () {
    $empleado = crearEmpleadoValido();
    $adelanto = Adelanto::factory()->create([
        'id_empleado' => $empleado->id_empleado,
        'monto' => 1000,
        'fecha_emision' => '2026-01-15',
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Adelantos::class)
        ->call('editar', $adelanto->id_adelanto)
        ->set('monto', 2500)
        ->call('guardar')
        ->assertHasNoErrors();

    expect((float) $adelanto->fresh()->monto)->toBe(2500.0);
});

test('puede eliminar un adelanto (soft delete)', function () {
    $adelanto = Adelanto::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Adelantos::class)
        ->call('eliminar', $adelanto->id_adelanto);

    expect($adelanto->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Adelantos::class)
        ->call('guardar')
        ->assertHasErrors(['id_empleado', 'monto', 'fecha_emision']);
});
