<?php

use App\Http\Livewire\Empleados;
use App\Models\Empleado;
use App\Models\RolLaboral;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Empleados::class)
        ->assertStatus(200);
});

test('puede crear un empleado', function () {
    $rol = RolLaboral::factory()->create();

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Empleados::class)
        ->set('id_rol_laboral', $rol->id_rol_laboral)
        ->set('dni', '12345678')
        ->set('apellido', 'Perez')
        ->set('nombre', 'Juan')
        ->set('fecha_nacimiento', '1990-05-15')
        ->set('fecha_inicio_actividades', '2020-03-01')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Empleado::where('dni', '12345678')->exists())->toBeTrue();
});

test('puede editar un empleado', function () {
    $rol = RolLaboral::factory()->create();
    $empleado = Empleado::factory()->create([
        'id_rol_laboral' => $rol->id_rol_laboral,
        'dni' => '12345678',
        'fecha_nacimiento' => '1990-05-15',
        'fecha_inicio_actividades' => '2020-03-01',
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->set('apellido', 'Gomez')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($empleado->fresh()->apellido)->toBe('Gomez');
});

test('puede eliminar un empleado (soft delete)', function () {
    $rol = RolLaboral::factory()->create();
    $empleado = Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(Empleados::class)
        ->call('eliminar', $empleado->id_empleado);

    expect($empleado->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(Empleados::class)
        ->call('guardar')
        ->assertHasErrors(['id_rol_laboral', 'dni', 'apellido', 'nombre']);
});
