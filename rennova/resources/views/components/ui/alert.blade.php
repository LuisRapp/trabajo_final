@props([
    'variant' => 'info',
    'dismissible' => true,
])

@php
    $icon = match ($variant) {
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'danger' => 'x-circle',
        default => 'information-circle',
    };
@endphp

<div
    @if($dismissible) x-data="{ open: true }" x-show="open" @endif
    role="alert"
    {{ $attributes->merge(['class' => "alert alert-{$variant}"]) }}
>
    <x-dynamic-component component="flux::icon.{{ $icon }}" class="size-5 shrink-0 mt-0.5" />

    <div class="flex-1">
        {{ $slot }}
    </div>

    @if($dismissible)
        <button
            type="button"
            class="shrink-0 text-tinta-suave hover:text-tinta focus:outline-hidden"
            @click="open = false"
            aria-label="Cerrar alerta"
        >
            <flux:icon.x-mark class="size-4" />
        </button>
    @endif
</div>
