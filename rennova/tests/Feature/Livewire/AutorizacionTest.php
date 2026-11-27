<?php

use App\Http\Livewire\Adelantos;
use App\Http\Livewire\AsignacionesLote;
use App\Http\Livewire\Insumos;
use App\Http\Livewire\Lotes;
use App\Http\Livewire\Mantenimientos;
use App\Http\Livewire\RolesPermisos;
use App\Http\Livewire\Usuarios;
use App\Http\Livewire\Ventas;
use App\Models\Adelanto;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\RolLaboral;
use App\Models\Usuario;
use App\Models\Venta;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('sin permiso crear-adelantos no puede guardar un adelanto', function () {
    $usuario = usuarioConPermisos(['editar-adelantos']);
    $rol = RolLaboral::factory()->create();
    $empleado = Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);

    Livewire::actingAs($usuario)
        ->test(Adelantos::class)
        ->set('id_empleado', $empleado->id_empleado)
        ->set('monto', 1500)
        ->set('fecha_emision', '2026-01-15')
        ->call('guardar')
        ->assertForbidden();

    expect(Adelanto::count())->toBe(0);
});

test('sin permiso crear-ventas no puede guardar una venta', function () {
    $usuario = usuarioConPermisos(['editar-ventas']);

    Livewire::actingAs($usuario)
        ->test(Ventas::class)
        ->call('guardarVenta')
        ->assertForbidden();

    expect(Venta::count())->toBe(0);
});

test('sin permiso crear-lotes no puede guardar un lote', function () {
    $usuario = usuarioConPermisos(['editar-lotes']);

    Livewire::actingAs($usuario)
        ->test(Lotes::class)
        ->call('guardar')
        ->assertForbidden();

    expect(Lote::count())->toBe(0);
});

test('sin permiso crear-asignaciones-lote no puede asignar recursos', function () {
    $usuario = usuarioConPermisos(['editar-asignaciones-lote']);
    $lote = Lote::factory()->create(['estado' => 'activo']);

    Livewire::actingAs($usuario)
        ->test(AsignacionesLote::class)
        ->call('guardar')
        ->assertForbidden();

    expect($lote->empleados()->count())->toBe(0)
        ->and($lote->maquinarias()->count())->toBe(0);
});

test('sin permiso confirmar-mantenimiento no puede aprobar una orden', function () {
    $usuario = usuarioConPermisos(['completar-mantenimiento']);

    Livewire::actingAs($usuario)
        ->test(Mantenimientos::class)
        ->call('aprobarOrden')
        ->assertForbidden();
});

test('sin permiso crear-insumos no puede guardar un insumo', function () {
    $usuario = usuarioConPermisos(['editar-insumos']);

    Livewire::actingAs($usuario)
        ->test(Insumos::class)
        ->call('guardar')
        ->assertForbidden();

    expect(Insumo::count())->toBe(0);
});

test('sin permiso crear-usuarios no puede guardar un usuario', function () {
    $usuario = usuarioConPermisos(['gestionar-usuarios']);

    Livewire::actingAs($usuario)
        ->test(Usuarios::class)
        ->set('nombre', 'Juan')
        ->set('apellido', 'Pérez')
        ->set('email', 'juan.perez@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('activo', true)
        ->call('guardar')
        ->assertForbidden();

    expect(Usuario::where('email', 'juan.perez@example.com')->exists())->toBeFalse();
});

test('sin permiso gestionar-permisos no puede crear un rol', function () {
    $usuario = usuarioConPermisos(['ver-auditoria']);

    Livewire::actingAs($usuario)
        ->test(RolesPermisos::class)
        ->set('newRoleName', 'Auditor')
        ->call('createRole')
        ->assertForbidden();

    expect(Role::where('name', 'Auditor')->exists())->toBeFalse();
});

test('sin permiso ver-adelantos no puede acceder a la ruta de adelantos', function () {
    $usuario = usuarioConPermisos(['ver-empleados']);

    $this->actingAs($usuario)
        ->get('/adelantos')
        ->assertForbidden();
});
