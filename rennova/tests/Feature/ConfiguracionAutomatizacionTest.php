<?php

use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Services\ConfiguracionService;
use App\Services\EstadoProcesosService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

afterEach(function () {
    Carbon::setTestNow();
});

it('las automatizaciones muestran la frecuencia configurada', function () {
    ConfiguracionService::establecer('mantenimiento_hora_verificacion_umbrales', '15 7 * * *', null, 'cron');
    ConfiguracionService::establecer('mantenimiento_cron_programados', '0 9 * * *', null, 'cron');

    $procesos = collect(app(EstadoProcesosService::class)->procesos());

    expect($procesos->firstWhere('comando', 'mantenimiento:check-umbrales')['cron'])->toBe('15 7 * * *')
        ->and($procesos->firstWhere('comando', 'mantenimiento:check-programados')['cron'])->toBe('0 9 * * *');
});

it('no envia el recordatorio antes de la hora configurada', function () {
    Carbon::setTestNow('2026-11-27 06:00:00');
    ConfiguracionService::establecer('mantenimiento_hora_recordatorio', '08:00', null, 'time');
    Cache::flush();

    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->toDateString(),
    ]);

    $this->artisan('mantenimiento:check-programados')->assertExitCode(0);

    expect(Cache::has('mantenimiento:recordatorio:2026-11-27'))->toBeFalse();
});

it('envia el recordatorio una vez desde la hora configurada', function () {
    Carbon::setTestNow('2026-11-27 09:00:00');
    ConfiguracionService::establecer('mantenimiento_hora_recordatorio', '08:00', null, 'time');
    Cache::flush();

    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->toDateString(),
    ]);

    $this->artisan('mantenimiento:check-programados')->assertExitCode(0);

    expect(Cache::has('mantenimiento:recordatorio:2026-11-27'))->toBeTrue();
});

it('la ejecucion manual fuerza el recordatorio aunque ya se haya enviado', function () {
    Carbon::setTestNow('2026-11-27 09:00:00');
    ConfiguracionService::establecer('mantenimiento_hora_recordatorio', '08:00', null, 'time');
    Cache::flush();

    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->toDateString(),
    ]);

    Cache::put('mantenimiento:recordatorio:2026-11-27', true);

    // Sin --forzar no reenvia; con --forzar si.
    $this->artisan('mantenimiento:check-programados', ['--forzar' => true])->assertExitCode(0);

    expect(Cache::has('mantenimiento:recordatorio:2026-11-27'))->toBeTrue();
});
