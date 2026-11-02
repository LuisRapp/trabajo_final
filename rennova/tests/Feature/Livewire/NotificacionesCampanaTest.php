<?php

use App\Http\Livewire\NotificacionesCampana;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar la campana', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(NotificacionesCampana::class)
        ->assertStatus(200);
});

test('puede marcar todas como leídas', function () {
    $user = Usuario::factory()->create();
    NotificacionSistema::factory()->count(2)->create(['user_id' => $user->id, 'leida' => false]);

    Livewire::actingAs($user)
        ->test(NotificacionesCampana::class)
        ->call('marcarTodasComoLeidas');

    expect(NotificacionSistema::where('user_id', $user->id)->where('leida', false)->count())->toBe(0);
});
