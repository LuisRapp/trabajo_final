<?php

use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\ParteDiario;
use App\Models\RolLaboral;
use App\Services\EmpleadoPagoService;
use App\Services\ReciboService;
use Illuminate\Support\Facades\DB;

function empleadoParaLiquidar(): Empleado
{
    $rol = RolLaboral::factory()->create(['costo_diario' => 1000]);

    HistoricoRolLaboral::create([
        'rol_laboral_id' => $rol->id_rol_laboral,
        'jornal_diario' => 1000,
        'precio_tonelada' => 100,
        'fecha_inicio' => '2026-01-01',
    ]);

    return Empleado::factory()->create(['id_rol_laboral' => $rol->id_rol_laboral]);
}

function cargaParaEmpleado(Empleado $empleado, ParteDiario $parte, float $pesoNeto = 1000): Carga
{
    $carga = Carga::factory()->create([
        'id_parte_diario' => $parte->id_parte_diario,
        'fecha_carga' => $parte->fecha,
        'peso_neto' => $pesoNeto,
    ]);

    $carga->empleados()->attach($empleado->id_empleado);

    return $carga;
}

it('no vuelve a pagar la misma produccion en una segunda liquidacion', function () {
    $empleado = empleadoParaLiquidar();
    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-10', 'es_dia_caido' => false]);
    $parte->empleados()->attach($empleado->id_empleado);
    $carga = cargaParaEmpleado($empleado, $parte, 1000);

    $inicio = '2026-11-01';
    $fin = '2026-11-30';

    $antes = EmpleadoPagoService::calcularPagoRango($empleado, $inicio, $fin);
    expect($antes['total_peso_toneladas'])->toBe(1.0);

    $recibo = EmpleadoPagoService::generarRecibo(
        $empleado->id_empleado,
        $antes['total_pagar_final'],
        0,
        'Liquidación período 01/11/2026 a 30/11/2026',
        collect(),
        $inicio,
        $fin
    );

    expect(DB::table('carga_empleado')->where('id_carga', $carga->id_carga)->value('id_recibo'))->toBe($recibo->id_recibo)
        ->and(DB::table('parte_diario_empleado')->where('id_parte_diario', $parte->id_parte_diario)->value('id_recibo'))->toBe($recibo->id_recibo);

    $despues = EmpleadoPagoService::calcularPagoRango($empleado, $inicio, $fin);
    expect($despues['total_peso_toneladas'])->toBe(0.0)
        ->and($despues['cantidad_dias_caidos'])->toBe(0);
});

it('una carga creada despues del recibo sigue pendiente de pago', function () {
    $empleado = empleadoParaLiquidar();
    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-10', 'es_dia_caido' => false]);
    $parte->empleados()->attach($empleado->id_empleado);

    EmpleadoPagoService::generarRecibo(
        $empleado->id_empleado,
        0,
        0,
        'Liquidación período 01/11/2026 a 30/11/2026',
        collect(),
        '2026-11-01',
        '2026-11-30'
    );

    $cargaNueva = cargaParaEmpleado($empleado, $parte, 1000);

    $calculo = EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30');

    expect($calculo['total_peso_toneladas'])->toBe(1.0)
        ->and(DB::table('carga_empleado')->where('id_carga', $cargaNueva->id_carga)->value('id_recibo'))->toBeNull();
});

it('borrar el recibo libera la produccion para volver a liquidarla', function () {
    $empleado = empleadoParaLiquidar();
    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-10', 'es_dia_caido' => false]);
    $parte->empleados()->attach($empleado->id_empleado);
    cargaParaEmpleado($empleado, $parte, 1000);

    $recibo = EmpleadoPagoService::generarRecibo(
        $empleado->id_empleado,
        0,
        0,
        'Liquidación período 01/11/2026 a 30/11/2026',
        collect(),
        '2026-11-01',
        '2026-11-30'
    );

    expect(EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30')['total_peso_toneladas'])->toBe(0.0);

    app(ReciboService::class)->eliminar($recibo->id_recibo);

    expect(EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30')['total_peso_toneladas'])->toBe(1.0);
});

it('un dia caido no se vuelve a pagar en la liquidacion siguiente', function () {
    $empleado = empleadoParaLiquidar();
    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-12', 'es_dia_caido' => true]);
    $parte->empleados()->attach($empleado->id_empleado);

    $antes = EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30');
    expect($antes['cantidad_dias_caidos'])->toBe(1);

    EmpleadoPagoService::generarRecibo(
        $empleado->id_empleado,
        $antes['total_pagar_final'],
        0,
        'Liquidación período 01/11/2026 a 30/11/2026',
        collect(),
        '2026-11-01',
        '2026-11-30'
    );

    expect(EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30')['cantidad_dias_caidos'])->toBe(0);
});

it('liquidar a todos tambien marca la produccion como pagada', function () {
    $empleado = empleadoParaLiquidar();
    $parte = ParteDiario::factory()->create(['fecha' => '2026-11-10', 'es_dia_caido' => false]);
    $parte->empleados()->attach($empleado->id_empleado);
    $carga = cargaParaEmpleado($empleado, $parte, 1000);

    EmpleadoPagoService::liquidarTodos('2026-11-01', '2026-11-30');

    expect(DB::table('carga_empleado')->where('id_carga', $carga->id_carga)->value('id_recibo'))->not->toBeNull()
        ->and(EmpleadoPagoService::calcularPagoRango($empleado, '2026-11-01', '2026-11-30')['total_peso_toneladas'])->toBe(0.0);
});
