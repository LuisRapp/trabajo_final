@props([
    'editWireClick' => '',
    'deleteWireClick' => '',
    'deleteMessage' => '¿Esta seguro?',
    'canEdit' => true,
    'canDelete' => true,
    'editRoute' => null,
])

<div {{ $attributes->merge(['class' => 'flex gap-1 justify-end']) }}>
    @if($canEdit)
        @if($editRoute)
            <x-ui.button variant="secondary" size="sm" icon="pencil-square" href="{{ $editRoute }}" title="Editar" />
        @else
            <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="{{ $editWireClick }}" title="Editar" />
        @endif
    @endif

    @if($canDelete)
        <x-ui.button
            variant="danger"
            size="sm"
            icon="trash"
            wire:click="{{ $deleteWireClick }}"
            wire:confirm="{{ $deleteMessage }}"
            title="Eliminar"
        />
    @endif
</div>
