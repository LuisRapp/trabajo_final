<?php

use App\Http\Livewire\NotificacionesSistema;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(NotificacionesSistema::class)
        ->assertStatus(200);
});

test('puede marcar una notificación como leída', function () {
    $user = Usuario::factory()->create();
    $notif = NotificacionSistema::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(NotificacionesSistema::class)
        ->call('marcarComoLeida', $notif->id);

    expect($notif->fresh()->leida)->toBeTrue();
});

test('puede marcar una notificación como accionada', function () {
    $user = Usuario::factory()->create();
    $notif = NotificacionSistema::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(NotificacionesSistema::class)
        ->call('marcarComoAccionada', $notif->id);

    expect($notif->fresh()->accionada)->toBeTrue();
});
