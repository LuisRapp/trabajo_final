<?php

use App\Http\Livewire\SelectorLote;
use App\Models\Lote;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar el selector de lote', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(SelectorLote::class)
        ->assertStatus(200);
});

test('sin coordenadas no carga pronóstico', function () {
    Lote::factory()->create([
        'estado' => 'activo',
        'latitud' => null,
        'longitud' => null,
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(SelectorLote::class)
        ->assertSet('pronosticoData', null);
});
