<?php

use App\Http\Livewire\ConfiguracionNotificacionesMantenimiento;
use Livewire\Livewire;

test('guardar la configuracion funciona en un segundo request', function () {
    // Regresion: el servicio se inyectaba en mount(), asi que al guardar la
    // propiedad tipada estaba sin inicializar y el request daba 500.
    $usuario = usuarioConPermisos(['configurar-notificaciones-mantenimiento']);

    Livewire::actingAs($usuario)
        ->test(ConfiguracionNotificacionesMantenimiento::class)
        ->assertStatus(200)
        ->call('guardarConfiguracion')
        ->assertHasNoErrors();
});
