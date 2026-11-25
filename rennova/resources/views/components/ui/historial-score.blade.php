@props(['valor' => null])

@php
    $historial = (float) ($valor ?? 0);
    $texto = 'sin historial';

    if ($historial > 0) {
        $numero = rtrim(rtrim(number_format($historial, 2, ',', ''), '0'), ',');
        $texto = 'historial: '.$numero.' d';
    }
@endphp

<x-ui.badge variant="neutral">{{ $texto }}</x-ui.badge>
