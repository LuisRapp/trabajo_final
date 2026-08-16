<?php

use App\Http\Livewire\Auditorias;
use App\Models\Usuario;

test('los invitados son redirigidos al login desde auditorias', function () {
    $response = $this->get(route('auditorias.index'));
    $response->assertRedirect(route('login'));
});

test('un usuario autenticado puede visitar auditorias', function () {
    $user = Usuario::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('auditorias.index'));
    $response->assertStatus(200);
    $response->assertSeeLivewire(Auditorias::class);
});
