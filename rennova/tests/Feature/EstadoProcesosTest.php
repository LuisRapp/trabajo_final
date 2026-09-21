<?php

use App\Models\ClimaDiaLote;
use App\Models\Lote;
use App\Models\Usuario;
use App\Services\EstadoProcesosService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'gestionar-usuarios', 'guard_name' => 'web']);
});

test('usuario con permiso puede ver el estado de procesos', function () {
    $user = Usuario::factory()->create();
    $user->givePermissionTo('gestionar-usuarios');

    $this->actingAs($user)
        ->get(route('estado-procesos.index'))
        ->assertOk()
        ->assertSeeLivewire('estado-procesos');
});

test('usuario sin permiso no puede ver el estado de procesos', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user)
        ->get(route('estado-procesos.index'))
        ->assertForbidden();
});

test('el servicio reporta el clima por lote y los trabajos fallidos de la cola', function () {
    $lote = Lote::factory()->create();

    ClimaDiaLote::create([
        'id_lote' => $lote->id_lote,
        'fecha' => now()->toDateString(),
        'estado_operativo' => 'OPERATIVO',
        'fuente' => 'api',
        'pronostico_actualizado_at' => now(),
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => 'estado-procesos-test',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\ProcessAllocationProposal']),
        'exception' => 'RuntimeException: error simulado para el test',
        'failed_at' => now()->toDateTimeString(),
    ]);

    $servicio = app(EstadoProcesosService::class);

    $clima = $servicio->climaPorLote();

    expect($clima)->toHaveCount(1)
        ->and($clima[0]['lote'])->toBe($lote->propietario.' - '.$lote->ubicacion)
        ->and($clima[0]['estado_operativo'])->toBe('OPERATIVO')
        ->and($clima[0]['fuente'])->toBe('api')
        ->and($clima[0]['con_coordenadas'])->toBeTrue();

    $cola = $servicio->colaTrabajos();

    expect($cola['fallidos'])->toHaveCount(1)
        ->and($cola['fallidos'][0]['nombre'])->toBe('App\\Jobs\\ProcessAllocationProposal')
        ->and($cola['fallidos'][0]['error'])->toBe('RuntimeException: error simulado para el test');
});
