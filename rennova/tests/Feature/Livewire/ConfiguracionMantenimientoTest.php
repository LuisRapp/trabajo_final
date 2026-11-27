<?php

use App\Http\Livewire\ConfiguracionMantenimiento;
use App\Models\ConfiguracionSistema;
use Livewire\Livewire;

test('usuario autenticado puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->assertStatus(200);
});

test('puede guardar la configuración de mantenimiento', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->set('hora_recordatorio', '09:30')
        ->set('expresion_cron', '0 6 * * *')
        ->call('guardarConfiguracion')
        ->assertHasNoErrors();

    expect(ConfiguracionSistema::where('clave', 'mantenimiento_hora_recordatorio')->value('valor'))->toBe('09:30');
});

test('muestra la frecuencia en lenguaje claro en vez de la expresion cron', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->assertSee('Se ejecuta:')
        ->assertSee('Todos los días a las 06:30')
        ->assertSee('Cada 4 horas')
        ->assertSee('Próxima ejecución:');
});

test('al elegir un preset se guarda esa frecuencia', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->set('modoUmbrales', '0 8 * * *')
        ->set('modoProgramados', '0 */6 * * *')
        ->call('guardarConfiguracion')
        ->assertHasNoErrors();

    expect(ConfiguracionSistema::where('clave', 'mantenimiento_hora_verificacion_umbrales')->value('valor'))->toBe('0 8 * * *')
        ->and(ConfiguracionSistema::where('clave', 'mantenimiento_cron_programados')->value('valor'))->toBe('0 */6 * * *');
});

test('una frecuencia personalizada se describe en lenguaje claro', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->set('modoUmbrales', 'custom')
        ->set('expresion_cron', '0 5 * * *')
        ->assertSee('Todos los días a las 05:00');
});

test('la ejecucion manual deja registrado el estado de la tarea', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-mantenimiento']))
        ->test(ConfiguracionMantenimiento::class)
        ->call('ejecutarVerificacionUmbrales')
        ->assertHasNoErrors();

    expect(
        \App\Models\EjecucionAutomatizacion::where('comando', 'mantenimiento:check-umbrales')->exists()
    )->toBeTrue();
});
