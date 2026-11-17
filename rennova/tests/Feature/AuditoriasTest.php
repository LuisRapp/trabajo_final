<?php

use App\Http\Livewire\Auditorias;

test('los invitados son redirigidos al login desde auditorias', function () {
    $response = $this->get(route('auditorias.index'));
    $response->assertRedirect(route('login'));
});

test('un usuario autenticado puede visitar auditorias', function () {
    $user = usuarioConPermisos(['ver-auditoria']);
    $this->actingAs($user);

    $response = $this->get(route('auditorias.index'));
    $response->assertStatus(200);
    $response->assertSeeLivewire(Auditorias::class);
});
