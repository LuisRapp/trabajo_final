<?php

use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoCostosMaquinaria;
use App\Models\HistoricoRolLaboral;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use App\Models\RolLaboral;
use App\Services\ClimaAnalisisService;

function parteConPeso(Lote $lote, float $kg, string $fecha): void
{
    $parte = ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => $fecha,
        'es_dia_caido' => false,
    ]);

    Carga::factory()->create([
        'id_parte_diario' => $parte->id_parte_diario,
        'id_lote' => $lote->id_lote,
        'fecha_carga' => $fecha,
        'peso_neto' => $kg,
    ]);
}

it('calcula la meta diaria con la produccion de cada lote', function () {
    $loteA = Lote::factory()->create();
    $loteB = Lote::factory()->create();
    $fecha = now()->subDays(3)->toDateString();

    parteConPeso($loteA, 1000, $fecha);
    parteConPeso($loteB, 5000, $fecha);

    $servicio = app(ClimaAnalisisService::class);

    expect($servicio->calcularMetaDiaria($loteA)['valor'])->toBe(1.0)
        ->and($servicio->calcularMetaDiaria($loteB)['valor'])->toBe(5.0)
        ->and($servicio->calcularMetaDiaria()['valor'])->toBe(3.0);
});

it('devuelve sin_datos cuando el lote no tiene produccion', function () {
    $lote = Lote::factory()->create();

    $meta = app(ClimaAnalisisService::class)->calcularMetaDiaria($lote);

    expect($meta['valor'])->toBe(0.0)
        ->and($meta['fuente'])->toBe('sin_datos');
});

it('calcula el costo estructural solo con los empleados y maquinas del lote', function () {
    $lote = Lote::factory()->create();
    $rol = RolLaboral::factory()->create();

    HistoricoRolLaboral::create([
        'rol_laboral_id' => $rol->id_rol_laboral,
        'jornal_diario' => 1000,
        'precio_tonelada' => 0,
        'fecha_inicio' => '2026-01-01',
    ]);

    $empleado = Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);
    $lote->empleados()->attach($empleado->id_empleado);

    parteConPeso($lote, 1000, now()->subDay()->toDateString());

    $maquina = Maquinaria::factory()->create(['es_alquilada' => true]);
    $lote->maquinarias()->attach($maquina->id_maquinaria);
    HistoricoCostosMaquinaria::create([
        'id_maquinaria' => $maquina->id_maquinaria,
        'costo_por_tonelada' => 10,
        'fecha_inicio_vigencia' => '2026-01-01',
    ]);

    // Otro lote con su propio empleado y maquina: no debe sumarse.
    $otroLote = Lote::factory()->create();
    $otroEmpleado = Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);
    $otroLote->empleados()->attach($otroEmpleado->id_empleado);
    $otraMaquina = Maquinaria::factory()->create(['es_alquilada' => true]);
    $otroLote->maquinarias()->attach($otraMaquina->id_maquinaria);

    $costo = app(ClimaAnalisisService::class)->calcularCostoEstructuralDiario($lote);

    // Jornal 1000 + 1 tn de produccion * 10 = 1010.
    expect($costo)->toBe(1010.0);
});
