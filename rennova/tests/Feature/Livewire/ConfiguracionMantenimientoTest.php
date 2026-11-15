<?php

use App\Http\Livewire\ConfiguracionMantenimiento;
use App\Models\ConfiguracionSistema;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(ConfiguracionMantenimiento::class)
        ->assertStatus(200);
});

test('puede guardar la configuración de mantenimiento', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(ConfiguracionMantenimiento::class)
        ->set('hora_recordatorio', '09:30')
        ->set('expresion_cron', '0 6 * * *')
        ->call('guardarConfiguracion')
        ->assertHasNoErrors();

    expect(ConfiguracionSistema::where('clave', 'mantenimiento_hora_recordatorio')->value('valor'))->toBe('09:30');
});
