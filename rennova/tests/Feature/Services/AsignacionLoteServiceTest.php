<?php

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Services\AsignacionLoteService;

function datosSolicitudAsignacion(): array
{
    return [
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'url' => 'http://localhost/asignaciones-lote',
    ];
}

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
