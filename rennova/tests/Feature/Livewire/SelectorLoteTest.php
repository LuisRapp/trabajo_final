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

test('un pronóstico con días acentuados produce un snapshot serializable', function () {
    // Regresión: con substr() en vez de mb_substr() el recorte de "miércoles"
    // partía el byte de la "é", json_encode() devolvía false y el snapshot del
    // componente quedaba vacío: el dashboard se renderizaba en blanco.
    app()->setLocale('es');

    Lote::factory()->create([
        'propietario' => 'Lote lluvia',
        'estado' => 'activo',
        'latitud' => -34.6,
        'longitud' => -58.4,
    ]);

    $componente = Livewire::actingAs(Usuario::factory()->create())->test(SelectorLote::class);
    $pronostico = $componente->get('pronosticoData');

    expect($pronostico)->not->toBeNull()
        ->and(json_encode($pronostico))->not->toBeFalse();

    $etiquetas = implode(' ', array_column($pronostico['pronostico'], 'label'));

    expect($etiquetas)->toContain('Mié');
});
