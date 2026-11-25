<?php

use Illuminate\Support\Facades\Blade;

test('el historial se muestra en dias, no con decimales crudos', function () {
    $cero = Blade::render('<x-ui.historial-score :valor="0" />');
    $medio = Blade::render('<x-ui.historial-score :valor="2.5" />');
    $entero = Blade::render('<x-ui.historial-score :valor="12" />');
    $nulo = Blade::render('<x-ui.historial-score />');

    expect($cero)->toContain('sin historial')
        ->and($nulo)->toContain('sin historial')
        ->and($medio)->toContain('historial: 2,5 d')
        ->and($entero)->toContain('historial: 12 d');

    expect($cero)->not->toContain('0.0000');
    expect($entero)->not->toContain('12.0000');
});
