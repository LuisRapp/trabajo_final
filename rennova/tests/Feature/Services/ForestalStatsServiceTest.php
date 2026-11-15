<?php

use App\Models\Carga;
use App\Models\Lote;
use App\Models\ParteDiario;
use App\Models\Venta;
use App\Services\ForestalStatsService;

it('calcula el punto de equilibrio usando los jornales de días caídos como costo fijo', function () {
    $lote = Lote::factory()->create();

    // Día caído: jornal (costo fijo) 1500
    ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => '2026-10-15',
        'es_dia_caido' => true,
        'costo_mano_obra' => 1500,
        'costo_insumos' => 0,
        'costo_maquinaria' => 0,
        'costo_total_dia' => 1500,
        'costo_unitario_calculado' => null,
    ]);

    // Día de producción: insumos 1000 + maquinaria 2000 + mano de obra 500 = 3500
    ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => '2026-10-16',
        'es_dia_caido' => false,
        'costo_mano_obra' => 500,
        'costo_insumos' => 1000,
        'costo_maquinaria' => 2000,
        'costo_total_dia' => 3500,
        'costo_unitario_calculado' => null,
    ]);

    // Producción: 50 toneladas
    $carga = Carga::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha_carga' => '2026-10-16',
        'peso_neto' => 50000,
    ]);

    // Venta: 50 tn a $200/tn = $10000
    $venta = Venta::factory()->create([
        'fecha_emision' => '2026-10-16',
        'monto' => 10000,
    ]);
    $venta->cargas()->attach($carga->id_carga, [
        'precio_unitario' => 200,
        'peso_toneladas' => 50,
        'subtotal' => 10000,
    ]);

    $service = new ForestalStatsService;
    $punto = $service->getPuntoEquilibrioDiario($lote, '2026-10-01', '2026-10-31');

    // fijo = 1500, días = 30 → fijo/día = 50
    // precio = 200, variable = 3500/50 = 70 → margen = 130
    // punto = 50 / 130 = 0.38
    expect($punto)->toBe(0.38);
});

it('devuelve 0 cuando no hay costo fijo (sin días caídos)', function () {
    $lote = Lote::factory()->create();

    ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => '2026-10-16',
        'es_dia_caido' => false,
        'costo_mano_obra' => 500,
        'costo_insumos' => 1000,
        'costo_maquinaria' => 2000,
        'costo_total_dia' => 3500,
    ]);

    Carga::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha_carga' => '2026-10-16',
        'peso_neto' => 50000,
    ]);

    // Venta para tener precio > 0
    $venta = Venta::factory()->create([
        'fecha_emision' => '2026-10-16',
        'monto' => 10000,
    ]);
    $venta->cargas()->attach(
        Carga::where('id_lote', $lote->id_lote)->first()->id_carga,
        ['precio_unitario' => 200, 'peso_toneladas' => 50, 'subtotal' => 10000]
    );

    $service = new ForestalStatsService;
    $punto = $service->getPuntoEquilibrioDiario($lote, '2026-10-01', '2026-10-31');

    expect($punto)->toBe(0.0);
});
