@props([
    'variant' => 'neutral',
])

@php
    $classes = match ($variant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$classes}"]) }}>
    {{ $slot }}
</span>
