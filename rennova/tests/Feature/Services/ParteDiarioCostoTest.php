<?php

use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use App\Models\RolLaboral;
use App\Services\ParteDiarioCostoService;

function prepararParteConCarga(float $pesoNeto = 1000): array
{
    $rol = RolLaboral::factory()->create(['costo_diario' => 1000]);

    HistoricoRolLaboral::create([
        'rol_laboral_id' => $rol->id_rol_laboral,
        'jornal_diario' => 1000,
        'precio_tonelada' => 100,
        'fecha_inicio' => '2026-01-01',
    ]);

    $empleado = Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);

    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-26', 'es_dia_caido' => false]);

    $carga = Carga::factory()->create([
        'id_parte_diario' => $parte->id_parte_diario,
        'fecha_carga' => '2026-11-26',
        'peso_neto' => $pesoNeto,
    ]);

    // El empleado esta solo en la carga, no en el parte: el costo igual lo incluye.
    $carga->empleados()->attach($empleado->id_empleado);

    return [$parte, $carga, $empleado];
}

it('incluye la mano de obra de los empleados asignados a las cargas', function () {
    [$parte] = prepararParteConCarga(1000);

    ParteDiarioCostoService::calcularYGuardarCostos($parte);

    $parte->refresh();

    // 1 tonelada x $100/tn
    expect((float) $parte->costo_mano_obra)->toBe(100.0)
        ->and((float) $parte->costo_total_dia)->toBe(100.0);
});

it('suma el alquiler como precio por tonelada de cada maquina', function () {
    [$parte, $carga] = prepararParteConCarga(1000);

    $maquina = Maquinaria::factory()->create([
        'es_alquilada' => true,
        'precio_alquiler_destajo' => 10,
    ]);
    $carga->maquinarias()->attach($maquina->id_maquinaria);

    ParteDiarioCostoService::calcularYGuardarCostos($parte);

    $parte->refresh();

    // 1 tonelada x $10/tn
    expect((float) $parte->costo_maquinaria)->toBe(10.0)
        ->and((float) $parte->costo_total_dia)->toBe(110.0);
});

it('con dos maquinas alquiladas cobra el alquiler de las dos', function () {
    [$parte, $carga] = prepararParteConCarga(1000);

    $maquina1 = Maquinaria::factory()->create(['es_alquilada' => true, 'precio_alquiler_destajo' => 10]);
    $maquina2 = Maquinaria::factory()->create(['es_alquilada' => true, 'precio_alquiler_destajo' => 5]);
    $carga->maquinarias()->attach([$maquina1->id_maquinaria, $maquina2->id_maquinaria]);

    ParteDiarioCostoService::calcularYGuardarCostos($parte);

    $parte->refresh();

    // (1 tn x $10) + (1 tn x $5)
    expect((float) $parte->costo_maquinaria)->toBe(15.0);
});

it('no cobra alquiler de una maquina que no es alquilada', function () {
    [$parte, $carga] = prepararParteConCarga(1000);

    $maquina = Maquinaria::factory()->create([
        'es_alquilada' => false,
        'precio_alquiler_destajo' => 10,
    ]);
    $carga->maquinarias()->attach($maquina->id_maquinaria);

    ParteDiarioCostoService::calcularYGuardarCostos($parte);

    expect((float) $parte->fresh()->costo_maquinaria)->toBe(0.0);
});
