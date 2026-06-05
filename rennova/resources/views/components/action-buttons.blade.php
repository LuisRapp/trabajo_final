@props([
    'editWireClick' => '',
    'deleteWireClick' => '',
    'deleteMessage' => '¿Está seguro?',
    'canEdit' => true,
    'canDelete' => true,
    'editRoute' => null,
])

<div {{ $attributes->merge(['class' => 'flex gap-1 justify-center']) }}>
    @if($canEdit)
        @if($editRoute)
            <a href="{{ $editRoute }}" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                ✏️
            </a>
        @else
            <button type="button"
                wire:click="{{ $editWireClick }}"
                title="Editar"
                class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                ✏️
            </button>
        @endif
    @endif

    @if($canDelete)
        <button type="button"
            wire:click="{{ $deleteWireClick }}"
            wire:confirm="{{ $deleteMessage }}"
            title="Eliminar"
            class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
            🗑️
        </button>
    @endif
</div>
