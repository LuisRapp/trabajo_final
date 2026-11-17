<?php

use App\Http\Livewire\Adelantos;
use App\Models\Adelanto;
use App\Models\Empleado;
use App\Models\Recibo;
use App\Models\RolLaboral;
use Livewire\Livewire;

function crearEmpleadoValido(): Empleado
{
    $rol = RolLaboral::factory()->create();

    return Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);
}

function crearLiquidaciones(Empleado $empleado, array $montos): void
{
    foreach ($montos as $monto) {
        Recibo::factory()->create(['id_empleado' => $empleado->id_empleado, 'monto' => $monto]);
    }
}

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->assertStatus(200);
});

test('puede crear un adelanto', function () {
    $empleado = crearEmpleadoValido();
    crearLiquidaciones($empleado, [5000, 3000]); // promedio 4000, tope 2000

    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
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
    crearLiquidaciones($empleado, [6000]); // promedio 6000, tope 3000
    $adelanto = Adelanto::factory()->create([
        'id_empleado' => $empleado->id_empleado,
        'monto' => 1000,
        'fecha_emision' => '2026-01-15',
    ]);

    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->call('editar', $adelanto->id_adelanto)
        ->set('monto', 2500)
        ->call('guardar')
        ->assertHasNoErrors();

    expect((float) $adelanto->fresh()->monto)->toBe(2500.0);
});

test('rechaza un adelanto cuando el total con los pendientes supera el 50%', function () {
    $empleado = crearEmpleadoValido();
    crearLiquidaciones($empleado, [5000, 3000]); // promedio 4000, tope 2000

    // Ya tiene un adelanto pendiente de 1500
    Adelanto::factory()->create([
        'id_empleado' => $empleado->id_empleado,
        'monto' => 1500,
        'estado' => 'pendiente',
    ]);

    // Pedir 1000 mas -> total 2500 > 2000, se rechaza
    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 1000)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertHasErrors(['monto']);

    expect(Adelanto::where('id_empleado', $empleado->id_empleado)->count())->toBe(1);
});

test('permite un adelanto cuando el total con los pendientes no supera el 50%', function () {
    $empleado = crearEmpleadoValido();
    crearLiquidaciones($empleado, [5000, 3000]); // promedio 4000, tope 2000

    Adelanto::factory()->create([
        'id_empleado' => $empleado->id_empleado,
        'monto' => 1500,
        'estado' => 'pendiente',
    ]);

    // Pedir 400 mas -> total 1900 <= 2000, se acepta
    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 400)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Adelanto::where('id_empleado', $empleado->id_empleado)->count())->toBe(2);
});

test('rechaza un adelanto que supera el 50% del promedio de liquidaciones', function () {
    $empleado = crearEmpleadoValido();
    crearLiquidaciones($empleado, [5000, 3000]); // promedio 4000, tope 2000

    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 2500)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertHasErrors(['monto']);

    expect(Adelanto::where('id_empleado', $empleado->id_empleado)->exists())->toBeFalse();
});

test('rechaza un adelanto cuando el empleado no tiene liquidaciones', function () {
    $empleado = crearEmpleadoValido();

    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 100)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertHasErrors(['monto']);

    expect(Adelanto::where('id_empleado', $empleado->id_empleado)->exists())->toBeFalse();
});

test('puede eliminar un adelanto (soft delete)', function () {
    $adelanto = Adelanto::factory()->create();

    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->call('eliminar', $adelanto->id_adelanto);

    expect($adelanto->fresh()->trashed())->toBeTrue();
});

test('valida campos obligatorios al crear', function () {
    Livewire::actingAs(usuarioConPermisos(['crear-adelantos', 'editar-adelantos', 'eliminar-adelantos']))
        ->test(Adelantos::class)
        ->call('guardar')
        ->assertHasErrors(['id_empleado', 'monto', 'fecha_emision']);
});
