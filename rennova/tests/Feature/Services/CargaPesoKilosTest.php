<?php

use App\Models\Carga;
use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use App\Services\PartesDiariosService;

test('una carga de 800 kg se reparte a las maquinas como 0,8 toneladas', function () {
    // Regresion: habia una heuristica por magnitud (> 1000 => kilos) que registraba
    // una carga de 800 kg como 800 toneladas. Todo el sistema carga kilos.
    $lote = Lote::factory()->create();
    $parte = ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => now()->toDateString(),
    ]);
    $categoria = CategoriaMadera::factory()->create();
    $cliente = Cliente::factory()->create();
    $chofer = Chofer::create([
        'id_cliente' => $cliente->id_cliente,
        'nombre' => 'Chofer',
        'apellido' => 'Prueba',
        'dni' => '30111222',
    ]);
    $maquinaria = Maquinaria::factory()->create();

    $resultado = PartesDiariosService::registrarCargas(
        $parte->id_parte_diario,
        [[
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_chofer' => $chofer->id_chofer,
            'destino' => $cliente->id_cliente,
            'ticket' => 'T-800',
            'peso_bruto' => 1000,
            'tara' => 200,
            'peso_neto' => 800,
            'empleados' => [],
            'maquinarias' => [$maquinaria->id_maquinaria],
        ]],
        $lote->id_lote,
        now()->toDateString()
    );

    expect($resultado['eventos'])->toHaveCount(1)
        ->and($resultado['eventos'][0][2])->toBe(0.8);

    expect((float) Carga::query()->latest('id_carga')->first()->peso_neto)->toBe(800.0);
});
