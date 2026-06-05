@props(['wireClick' => '', 'message' => '¿Está seguro?', 'title' => 'Eliminar'])

<button type="button"
    wire:click="{{ $wireClick }}"
    wire:confirm="{{ $message }}"
    title="{{ $title }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200']) }}>
    <i class="bi bi-trash text-sm"></i>
</button>
