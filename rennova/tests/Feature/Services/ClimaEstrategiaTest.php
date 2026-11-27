<?php

use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Services\ClimaEstrategiaService;

function analisisConLluviaHoy(int $diasOperativos, float $riesgo, int $diasPerdidos = 3): array
{
    return [
        'volumen_riesgo' => $riesgo,
        'total_dias_perdidos' => $diasPerdidos,
        'dias_operativos_previos' => 0,
        'dias_operativos_posterior' => $diasOperativos,
        'total_dias_operativos' => $diasOperativos,
        'meta_diaria' => 100.0,
        'dia_cero_index' => 0,
        'dias_detalle' => [
            ['fecha' => '2026-11-27', 'fecha_str' => '27/11/2026', 'dia_semana' => 'viernes', 'estado' => 'INACTIVO', 'razon' => 'Lluvia diurna activa'],
            ['fecha' => '2026-11-30', 'fecha_str' => '30/11/2026', 'dia_semana' => 'lunes', 'estado' => 'OPERATIVO', 'razon' => null],
        ],
    ];
}

it('planifica la recuperacion si la lluvia es hoy pero quedan dias operativos', function () {
    $lote = Lote::factory()->create();

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(4, 300));

    expect($estrategia['nivel_urgencia'])->not->toBe('CRITICA')
        ->and($estrategia['estrategia'])->toStartWith('ANTICIPACION')
        ->and($estrategia['datos_calculados']['dias_perdidos'])->toBe(3);
});

it('solo cae en reaccion cuando no hay ningun dia operativo', function () {
    $lote = Lote::factory()->create();

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(0, 300));

    expect($estrategia['nivel_urgencia'])->toBe('CRITICA')
        ->and($estrategia['estrategia'])->toBe('REACCION');
});

it('no inventa un deficit cuando el lote no tiene produccion historica', function () {
    $lote = Lote::factory()->create();

    $analisis = analisisConLluviaHoy(4, 0);
    $analisis['meta_diaria'] = 0;
    $analisis['meta_fuente'] = 'sin_datos';

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, $analisis);

    expect($estrategia['estrategia'])->toBe('SIN_HISTORIAL')
        ->and($estrategia['nivel_urgencia'])->toBe('BAJA')
        ->and($estrategia['datos_calculados']['volumen_riesgo'])->toBe(0);
});

it('recomienda mantenimiento en un dia sin ventana cuando hay una maquina cerca del umbral', function () {
    $lote = Lote::factory()->create();
    $maquina = Maquinaria::factory()->create([
        'toneladas_acumuladas' => 900,
        'umbral_toneladas' => 1000,
    ]);
    $lote->maquinarias()->attach($maquina->id_maquinaria);

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(0, 300));

    expect($estrategia['accion_recomendada'])->toBe('MANTENIMIENTO_PREVENTIVO')
        ->and($estrategia['datos_calculados']['maquinarias_mantenimiento'])->toHaveCount(1)
        ->and($estrategia['datos_calculados']['maquinarias_mantenimiento'][0]['nombre'])->toBe($maquina->modelo);
});

it('incluye las maquinas con orden abierta en el plan de recuperacion', function () {
    $lote = Lote::factory()->create();
    $maquina = Maquinaria::factory()->create(['toneladas_acumuladas' => 100, 'umbral_toneladas' => 1000]);
    $lote->maquinarias()->attach($maquina->id_maquinaria);

    Mantenimiento::factory()->programado()->create(['id_maquinaria' => $maquina->id_maquinaria]);

    $estrategia = app(ClimaEstrategiaService::class)->determinarEstrategia($lote, analisisConLluviaHoy(4, 300));

    expect($estrategia['datos_calculados']['maquinarias_mantenimiento'])->toHaveCount(1)
        ->and($estrategia['datos_calculados']['maquinarias_mantenimiento'][0]['motivo'])->toContain('orden');
});
