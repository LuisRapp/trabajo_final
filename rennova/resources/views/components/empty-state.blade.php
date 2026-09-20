@props([
    'colspan' => null,
    'message' => 'No hay registros',
    'icon' => 'inbox',
])

@php
$legacyIconMap = [
    'inbox' => 'inbox',
    'archive' => 'archive-box',
    'list-ul' => 'list-bullet',
    'person' => 'user',
    'truck' => 'truck',
    'exclamation-triangle' => 'exclamation-triangle',
];
$resolvedIcon = $legacyIconMap[$icon] ?? $icon;
@endphp

@if($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="text-center py-8">
            <div class="inline-flex flex-col items-center justify-center gap-2 px-6 py-6">
                <x-dynamic-component component="flux::icon.{{ $resolvedIcon }}" class="size-8 text-arena-oscura" />
                <p class="text-tinta-suave text-sm font-medium">{{ $message }}</p>
            </div>
        </td>
    </tr>
@else
    <x-ui.card class="flex flex-col items-center justify-center gap-2 px-6 py-8 text-center">
        <x-dynamic-component component="flux::icon.{{ $resolvedIcon }}" class="size-10 text-arena-oscura" />
        <p class="text-tinta-suave text-sm font-medium">{{ $message }}</p>
    </x-ui.card>
@endif
