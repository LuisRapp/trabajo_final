@props([
    'on',
])

<div
    x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
    x-show.transition.out.opacity.duration.1500ms="shown"
    x-transition:leave.opacity.duration.1500ms
    style="display: none;"
>
    <x-ui.alert variant="success" dismissible="false" {{ $attributes->merge(['class' => 'inline-flex items-center py-1.5 px-2 text-xs']) }}>
        {{ $slot->isEmpty() ? __('Saved.') : $slot }}
    </x-ui.alert>
</div>
