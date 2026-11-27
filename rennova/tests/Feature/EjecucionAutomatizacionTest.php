<?php

use App\Http\Livewire\EstadoProcesos;
use App\Models\EjecucionAutomatizacion;
use App\Services\EjecucionAutomatizacionService;
use App\Services\EstadoProcesosService;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function crearTareaProgramada(string $comando): Event
{
    return new Event(new CacheEventMutex(app('cache')), $comando);
}

it('registra la ultima ejecucion y pisa el estado anterior', function () {
    $servicio = app(EjecucionAutomatizacionService::class);

    $servicio->registrar('mantenimiento:check-umbrales', 'error', 'fallo previo');
    $servicio->registrar('mantenimiento:check-umbrales', 'ok');

    expect(EjecucionAutomatizacion::count())->toBe(1);

    $fila = EjecucionAutomatizacion::first();
    expect($fila->estado)->toBe('ok')
        ->and($fila->motivo_error)->toBeNull()
        ->and($fila->ultima_ejecucion_at)->not->toBeNull();
});

it('el scheduler registra una ejecucion exitosa con su duracion', function () {
    event(new ScheduledTaskFinished(crearTareaProgramada('mantenimiento:check-umbrales'), 1.25));

    $fila = EjecucionAutomatizacion::where('comando', 'mantenimiento:check-umbrales')->first();

    expect($fila)->not->toBeNull()
        ->and($fila->estado)->toBe('ok')
        ->and($fila->duracion_ms)->toBe(1250);
});

it('el scheduler registra el motivo cuando la tarea falla', function () {
    event(new ScheduledTaskFailed(
        crearTareaProgramada('clima:decisiones'),
        new RuntimeException('API caida')
    ));

    $fila = EjecucionAutomatizacion::where('comando', 'clima:decisiones')->first();

    expect($fila)->not->toBeNull()
        ->and($fila->estado)->toBe('error')
        ->and($fila->motivo_error)->toBe('API caida');
});

it('normaliza el nombre de la tarea programada', function () {
    $servicio = app(EjecucionAutomatizacionService::class);

    $evento = crearTareaProgramada("'/usr/local/bin/php' 'artisan' mantenimiento:check-umbrales");

    expect($servicio->comandoDe($evento))->toBe('mantenimiento:check-umbrales');
});

it('normaliza el comando aunque venga con argumentos', function () {
    $servicio = app(EjecucionAutomatizacionService::class);

    $evento = crearTareaProgramada("'php artisan clima:analizar --dias=7'");

    expect($servicio->comandoDe($evento))->toBe('clima:analizar');
});

it('el estado de procesos muestra la ultima ejecucion', function () {
    app(EjecucionAutomatizacionService::class)->registrar('mantenimiento:check-umbrales', 'ok');

    $fila = collect(app(EstadoProcesosService::class)->procesos())
        ->firstWhere('comando', 'mantenimiento:check-umbrales');

    expect($fila['ultimo_estado'])->toBe('ok')
        ->and($fila['ultima_at'])->not->toBeNull();
});

it('permite descartar un trabajo fallido por su uuid', function () {
    $uuid = 'job-test-descartar';

    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\Proceso']),
        'exception' => 'RuntimeException: error simulado',
        'failed_at' => now(),
    ]);

    Livewire::actingAs(usuarioConPermisos(['gestionar-usuarios']))
        ->test(EstadoProcesos::class)
        ->call('descartarJob', $uuid)
        ->assertHasNoErrors();

    expect(DB::table('failed_jobs')->where('uuid', $uuid)->count())->toBe(0);
});

it('sin permiso no puede descartar un trabajo fallido', function () {
    Livewire::actingAs(usuarioConPermisos([]))
        ->test(EstadoProcesos::class)
        ->call('descartarJob', 'cualquiera')
        ->assertForbidden();
});
