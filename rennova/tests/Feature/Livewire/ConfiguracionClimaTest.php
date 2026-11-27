<?php

use App\Http\Livewire\ConfiguracionClima;
use App\Services\ClimaConfiguracionService;
use Livewire\Livewire;

beforeEach(function () {
    ClimaConfiguracionService::limpiarCache();
});

test('usuario autenticado con permiso puede renderizar', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-clima']))
        ->test(ConfiguracionClima::class)
        ->assertStatus(200);
});

test('sin permiso no puede guardar los parametros', function () {
    Livewire::actingAs(usuarioConPermisos([]))
        ->test(ConfiguracionClima::class)
        ->set('umbral_lluvia_mm', 12)
        ->call('guardar')
        ->assertForbidden();
});

test('puede guardar los parametros y el servicio los lee', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-clima']))
        ->test(ConfiguracionClima::class)
        ->set('umbral_lluvia_mm', 12)
        ->set('aumento_maximo_pct', 30)
        ->set('forecast_dias', 5)
        ->call('guardar')
        ->assertHasNoErrors();

    ClimaConfiguracionService::limpiarCache();
    $servicio = app(ClimaConfiguracionService::class);

    expect((float) $servicio->valor('umbral_lluvia_mm'))->toBe(12.0)
        ->and((float) $servicio->valor('aumento_maximo_pct'))->toBe(30.0)
        ->and((int) $servicio->valor('forecast_dias'))->toBe(5);
});

test('valida los rangos de los parametros', function () {
    Livewire::actingAs(usuarioConPermisos(['configurar-clima']))
        ->test(ConfiguracionClima::class)
        ->set('umbral_nubosidad_pct', 120)
        ->set('forecast_dias', 0)
        ->call('guardar')
        ->assertHasErrors(['umbral_nubosidad_pct', 'forecast_dias']);
});

test('restaurar vuelve a los valores por defecto', function () {
    app(ClimaConfiguracionService::class)->guardar(['umbral_lluvia_mm' => 20]);
    ClimaConfiguracionService::limpiarCache();

    Livewire::actingAs(usuarioConPermisos(['configurar-clima']))
        ->test(ConfiguracionClima::class)
        ->call('restaurar')
        ->assertHasNoErrors();

    ClimaConfiguracionService::limpiarCache();

    expect((float) app(ClimaConfiguracionService::class)->valor('umbral_lluvia_mm'))
        ->toBe((float) config('clima.umbral_lluvia_mm'));
});
