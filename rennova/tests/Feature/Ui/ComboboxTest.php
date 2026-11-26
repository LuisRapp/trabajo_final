<?php

use Illuminate\Support\Facades\Blade;

test('el combobox de seleccion simple muestra opciones y la elegida', function () {
    $html = Blade::render(
        '<x-ui.combobox modelo="carga_destino" :opciones="$opciones" :seleccion="$seleccion" placeholder="Elegí el destino..." />',
        [
            'opciones' => [
                ['value' => 1, 'label' => 'Sauer', 'detalle' => '30-123'],
                ['value' => 2, 'label' => 'Theisen', 'detalle' => null],
            ],
            'seleccion' => 2,
        ]
    );

    expect($html)->toContain('Sauer')
        ->and($html)->toContain('Theisen')
        ->and($html)->toContain("\$wire.set('carga_destino'")
        ->and($html)->toContain('Buscar...');
});

test('el combobox multiple marca seleccionados y usa checkboxes de livewire', function () {
    $html = Blade::render(
        '<x-ui.combobox modelo="carga_empleados" :opciones="$opciones" :seleccionados="$seleccionados" multiple placeholder="Elegí los operarios..." />',
        [
            'opciones' => [
                ['value' => 7, 'label' => 'Rapp, Luis', 'detalle' => 'Gruista'],
                ['value' => 8, 'label' => 'Perez, Ana', 'detalle' => null],
            ],
            'seleccionados' => [7],
        ]
    );

    expect($html)->toContain('1 seleccionado')
        ->and($html)->toContain('Rapp, Luis')
        ->and($html)->toContain('wire:model="carga_empleados"');
});

test('el combobox deshabilitado avisa que primero hay que elegir el destino', function () {
    $html = Blade::render(
        '<x-ui.combobox modelo="carga_id_chofer" :opciones="[]" :deshabilitado="true" placeholder="Primero elegí el destino" />'
    );

    expect($html)->toContain('Primero elegí el destino')
        ->and($html)->toContain('disabled');
});
