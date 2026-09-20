@props([
    'status',
])

@if ($status)
    <x-ui.alert variant="success" {{ $attributes->merge(['class' => 'text-sm']) }}>
        {{ $status }}
    </x-ui.alert>
@endif
