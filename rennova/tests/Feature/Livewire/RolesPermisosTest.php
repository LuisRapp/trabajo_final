<?php

use App\Http\Livewire\RolesPermisos;
use App\Models\Usuario;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'gestionar-permisos', 'guard_name' => 'web']);
});

test('invitado es redirigido al login', function () {
    $this->get('/roles-permisos')
        ->assertRedirect('/login');
});

test('usuario sin permiso no puede acceder', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user)
        ->get('/roles-permisos')
        ->assertForbidden();
});

test('usuario con permiso puede renderizar componente', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-permisos');

    Livewire::actingAs($user)
        ->test(RolesPermisos::class)
        ->assertStatus(200);
});

test('puede crear un rol', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-permisos');

    Livewire::actingAs($user)
        ->test(RolesPermisos::class)
        ->set('newRoleName', 'Rol de Prueba')
        ->call('createRole')
        ->assertHasNoErrors();

    expect(Role::where('name', 'Rol de Prueba')->exists())->toBeTrue();
});

test('puede sincronizar permisos de un rol', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-permisos');
    $role = Role::create(['name' => 'Rol Test', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'ver-mantenimientos', 'guard_name' => 'web']);

    Livewire::actingAs($user)
        ->test(RolesPermisos::class)
        ->set('selectedRole', $role->id)
        ->set('rolePermissions', [$permission->name])
        ->call('updateRolePermissions')
        ->assertHasNoErrors();

    expect($role->fresh()->permissions->pluck('name'))->toContain('ver-mantenimientos');
});

test('puede eliminar un rol sin usuarios', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-permisos');
    $role = Role::create(['name' => 'Rol Eliminable', 'guard_name' => 'web']);

    Livewire::actingAs($user)
        ->test(RolesPermisos::class)
        ->call('deleteRole', $role->id)
        ->assertHasNoErrors();

    expect(Role::where('name', 'Rol Eliminable')->exists())->toBeFalse();
});

test('no puede eliminar el rol administrador', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-permisos');
    $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);

    Livewire::actingAs($user)
        ->test(RolesPermisos::class)
        ->call('deleteRole', $adminRole->id)
        ->assertHasNoErrors();

    expect(Role::where('name', 'Administrador')->exists())->toBeTrue();
});

test('puede sincronizar roles de un usuario', function () {
    $admin = Usuario::factory()->create();
    $admin->givePermissionTo('gestionar-permisos');
    $targetUser = Usuario::factory()->create();
    $role = Role::create(['name' => 'Rol Asignable', 'guard_name' => 'web']);

    Livewire::actingAs($admin)
        ->test(RolesPermisos::class)
        ->set('selectedUser', $targetUser->id)
        ->set('userRoles', [$role->name])
        ->call('updateUserRoles')
        ->assertHasNoErrors();

    expect($targetUser->fresh()->roles->pluck('name'))->toContain('Rol Asignable');
});
