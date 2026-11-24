@props([
    'editWireClick' => '',
    'deleteAction' => '',
    'deleteParams' => [],
    'deleteMessage' => '¿Eliminar este registro?',
    'deleteDetalle' => null,
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

    @if($canDelete && $deleteAction)
        <x-ui.confirmador
            :accion="$deleteAction"
            :parametros="$deleteParams"
            :mensaje="$deleteMessage"
            :detalle="$deleteDetalle"
        />
    @endif
</div>
