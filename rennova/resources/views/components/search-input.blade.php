@props([
    'model' => 'busqueda',
    'placeholder' => 'Buscar...',
])

<div class="mb-4">
    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-hueso">
        <flux:icon.magnifying-glass class="size-4 text-arena-oscura" />
        <input
            type="text"
            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder-arena-oscura"
            placeholder="{{ $placeholder }}"
            wire:model.live="{{ $model }}"
        >
    </div>
</div>
