<?php

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Services\AsignacionLoteService;
use App\Services\LoteService;

function datosSolicitudAsignacion(): array
{
    return [
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'url' => 'http://localhost/asignaciones-lote',
    ];
}

it('rechaza asignar un empleado ya asignado a un lote en proceso', function () {
    $loteEnProceso = Lote::factory()->enProceso()->create();
    $loteNuevo = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    $loteEnProceso->empleados()->attach($empleado->id_empleado);

    expect(fn () => app(AsignacionLoteService::class)->asignarRecursos(
        $loteNuevo->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    ))->toThrow(\RuntimeException::class, 'ya asignado');

    expect($loteNuevo->empleados()->count())->toBe(0);
});

it('rechaza asignar una maquinaria ya asignada a un lote en proceso', function () {
    $loteEnProceso = Lote::factory()->enProceso()->create();
    $loteNuevo = Lote::factory()->activo()->create();
    $maquinaria = Maquinaria::factory()->create();

    $loteEnProceso->maquinarias()->attach($maquinaria->id_maquinaria);

    expect(fn () => app(AsignacionLoteService::class)->asignarRecursos(
        $loteNuevo->id_lote,
        [],
        [$maquinaria->id_maquinaria],
        datosSolicitudAsignacion()
    ))->toThrow(\RuntimeException::class, 'ya asignada');

    expect($loteNuevo->maquinarias()->count())->toBe(0);
});

it('rechaza asignar un empleado ya asignado a otro lote activo', function () {
    $loteActivo = Lote::factory()->activo()->create();
    $loteNuevo = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    $loteActivo->empleados()->attach($empleado->id_empleado);

    expect(fn () => app(AsignacionLoteService::class)->asignarRecursos(
        $loteNuevo->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    ))->toThrow(\RuntimeException::class, 'ya asignado a Lote');

    expect($loteNuevo->empleados()->count())->toBe(0);
});

it('rechaza asignar una maquinaria ya asignada a otro lote activo', function () {
    $loteActivo = Lote::factory()->activo()->create();
    $loteNuevo = Lote::factory()->activo()->create();
    $maquinaria = Maquinaria::factory()->create();

    $loteActivo->maquinarias()->attach($maquinaria->id_maquinaria);

    expect(fn () => app(AsignacionLoteService::class)->asignarRecursos(
        $loteNuevo->id_lote,
        [],
        [$maquinaria->id_maquinaria],
        datosSolicitudAsignacion()
    ))->toThrow(\RuntimeException::class, 'ya asignada a Lote');

    expect($loteNuevo->maquinarias()->count())->toBe(0);
});

it('permite reasignar recursos que ya estan en el mismo lote', function () {
    $lote = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
    $maquinaria = Maquinaria::factory()->create();

    $lote->empleados()->attach($empleado->id_empleado);
    $lote->maquinarias()->attach($maquinaria->id_maquinaria);

    $resultado = app(AsignacionLoteService::class)->asignarRecursos(
        $lote->id_lote,
        [$empleado->id_empleado],
        [$maquinaria->id_maquinaria],
        datosSolicitudAsignacion()
    );

    expect($resultado['empleados_adjuntados'])->toBe([])
        ->and($resultado['maquinarias_adjuntadas'])->toBe([])
        ->and($lote->empleados()->count())->toBe(1)
        ->and($lote->maquinarias()->count())->toBe(1);
});

it('permite asignar recursos que estaban en un lote cerrado', function () {
    $loteCerrado = Lote::factory()->cerrado()->create();
    $loteNuevo = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    $loteCerrado->empleados()->attach($empleado->id_empleado);

    $resultado = app(AsignacionLoteService::class)->asignarRecursos(
        $loteNuevo->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    );

    expect($resultado['empleados_adjuntados'])->toBe([$empleado->id_empleado])
        ->and($loteNuevo->empleados()->count())->toBe(1);
});

it('pasa un lote activo a en proceso al asignarle recursos', function () {
    $lote = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    app(AsignacionLoteService::class)->asignarRecursos(
        $lote->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    );

    expect($lote->fresh()->estado)->toBe('en_proceso');
});

it('no cambia el estado de un lote en proceso al reasignar recursos', function () {
    $lote = Lote::factory()->enProceso()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    app(AsignacionLoteService::class)->asignarRecursos(
        $lote->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    );

    expect($lote->fresh()->estado)->toBe('en_proceso');
});

it('no transiciona a en proceso si la asignacion queda vacia', function () {
    $lote = Lote::factory()->activo()->create();

    app(AsignacionLoteService::class)->asignarRecursos(
        $lote->id_lote,
        [],
        [],
        datosSolicitudAsignacion()
    );

    expect($lote->fresh()->estado)->toBe('activo');
});

it('libera los recursos de un lote al llamar liberarRecursos', function () {
    $lote = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
    $maquinaria = Maquinaria::factory()->create();

    $lote->empleados()->attach($empleado->id_empleado);
    $lote->maquinarias()->attach($maquinaria->id_maquinaria);

    app(AsignacionLoteService::class)->liberarRecursos($lote->id_lote, datosSolicitudAsignacion());

    expect($lote->empleados()->count())->toBe(0)
        ->and($lote->maquinarias()->count())->toBe(0);
});

it('cierra un lote por el ABM y libera sus recursos', function () {
    $lote = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
    $lote->empleados()->attach($empleado->id_empleado);

    app(LoteService::class)->crearOActualizar($lote->id_lote, [
        'propietario' => 'Propietario Test',
        'ubicacion' => 'Misiones',
        'superficie' => 100,
        'estado' => 'cerrado',
        'condicion_compra' => 'propio',
        'especie' => 'Pino',
        'main_task_type' => 'tala_rasa',
    ], datosSolicitudAsignacion());

    expect($lote->fresh()->estado)->toBe('cerrado')
        ->and($lote->empleados()->count())->toBe(0);
});

it('rechaza asignar recursos a un lote no operativo', function (string $estado) {
    $lote = Lote::factory()->create(['estado' => $estado]);
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

    expect(fn () => app(AsignacionLoteService::class)->asignarRecursos(
        $lote->id_lote,
        [$empleado->id_empleado],
        [],
        datosSolicitudAsignacion()
    ))->toThrow(\RuntimeException::class, 'lote '.$estado);
})->with(['cerrado', 'baja', 'inactivo']);

it('no finaliza un lote dado de baja', function () {
    $lote = Lote::factory()->create(['estado' => 'baja']);

    $finalizado = app(AsignacionLoteService::class)->finalizar($lote->id_lote, datosSolicitudAsignacion());

    expect($finalizado)->toBeFalse()
        ->and($lote->fresh()->estado)->toBe('baja');
});

it('libera los recursos al pasar un lote a inactivo por el ABM', function () {
    $lote = Lote::factory()->enProceso()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
    $lote->empleados()->attach($empleado->id_empleado);

    app(LoteService::class)->crearOActualizar($lote->id_lote, [
        'propietario' => 'Propietario Test',
        'ubicacion' => 'Misiones',
        'superficie' => 100,
        'estado' => 'inactivo',
        'condicion_compra' => 'propio',
        'especie' => 'Pino',
        'main_task_type' => 'tala_rasa',
    ], datosSolicitudAsignacion());

    expect($lote->fresh()->estado)->toBe('inactivo')
        ->and($lote->empleados()->count())->toBe(0);
});

it('libera los recursos al eliminar un lote', function () {
    $lote = Lote::factory()->activo()->create();
    $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
    $lote->empleados()->attach($empleado->id_empleado);

    app(LoteService::class)->eliminar($lote->id_lote, datosSolicitudAsignacion());

    expect($lote->fresh()->trashed())->toBeTrue()
        ->and($lote->empleados()->count())->toBe(0);
});
