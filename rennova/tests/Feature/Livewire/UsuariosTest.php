<?php

use App\Http\Livewire\Usuarios;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'gestionar-usuarios', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'crear-usuarios', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/usuarios')
        ->assertRedirect('/login');
});

test('usuario sin permiso no puede acceder', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user)
        ->get('/usuarios')
        ->assertForbidden();
});

test('usuario con permiso puede renderizar componente', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-usuarios');

    Livewire::actingAs($user)
        ->test(Usuarios::class)
        ->assertStatus(200);
});

test('puede crear un usuario', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-usuarios');

    Livewire::actingAs($user)
        ->test(Usuarios::class)
        ->set('nombre', 'Juan')
        ->set('apellido', 'Pérez')
        ->set('email', 'juan.perez@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('telefono', '123456789')
        ->set('activo', true)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Usuario::where('email', 'juan.perez@example.com')->exists())->toBeTrue();
});

test('puede editar un usuario', function () {
    $admin = Usuario::factory()->create();
    $admin->givePermissionTo('gestionar-usuarios');
    $target = Usuario::factory()->create(['nombre' => 'Ana', 'apellido' => 'García']);

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('editar', $target->id)
        ->set('nombre', 'Ana María')
        ->set('apellido', 'García')
        ->set('email', $target->email)
        ->set('activo', true)
        ->call('guardar')
        ->assertHasNoErrors();

    expect($target->fresh()->nombre)->toBe('Ana María');
});

test('puede eliminar un usuario', function () {
    $admin = Usuario::factory()->create();
    $admin->givePermissionTo('gestionar-usuarios');
    $target = Usuario::factory()->create();

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('eliminar', $target->id)
        ->assertHasNoErrors();

    expect($target->fresh()->trashed())->toBeTrue();
});
