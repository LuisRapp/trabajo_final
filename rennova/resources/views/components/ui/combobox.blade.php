@props([
    'modelo',
    'opciones' => [],
    'seleccion' => null,
    'seleccionados' => [],
    'multiple' => false,
    'placeholder' => 'Seleccione...',
    'deshabilitado' => false,
])

@php
    $porValor = collect($opciones)->keyBy(fn ($opcion) => (string) $opcion['value']);

    if ($multiple) {
        $elegidos = collect($seleccionados)
            ->map(fn ($valor) => $porValor->get((string) $valor))
            ->filter()
            ->values();
        $texto = $elegidos->isEmpty()
            ? $placeholder
            : $elegidos->count().' '.($elegidos->count() === 1 ? 'seleccionado' : 'seleccionados');
    } else {
        $elegido = $seleccion !== null && $seleccion !== '' ? $porValor->get((string) $seleccion) : null;
        $texto = $elegido['label'] ?? $placeholder;
        $elegidos = collect();
    }
@endphp

<div
    x-data="{ abierto: false, q: '' }"
    x-on:click.away="abierto = false"
    x-on:keydown.escape.window="abierto = false"
    class="relative"
>
    <button
        type="button"
        x-on:click="abierto = ! abierto; if (abierto) { q = ''; $nextTick(() => $refs.buscar?.focus()) }"
        @if ($deshabilitado) disabled @endif
        class="form-input flex w-full items-center justify-between gap-2 text-left {{ $deshabilitado ? 'cursor-not-allowed bg-corteza-suave text-tinta-suave' : 'cursor-pointer' }}"
    >
        <span class="truncate {{ $elegidos === null || $elegidos->isEmpty() ? 'text-arena-oscura' : 'text-tinta' }}">
            {{ $texto }}
        </span>
        <flux:icon.chevron-down class="size-4 shrink-0 text-tinta-suave" />
    </button>

    @if ($multiple && $elegidos->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-1">
            @foreach ($elegidos as $opcion)
                <span class="inline-flex items-center gap-1 rounded-full bg-pino-suave px-2 py-0.5 text-xs font-medium text-pino">
                    {{ $opcion['label'] }}
                </span>
            @endforeach
        </div>
    @endif

    <div
        x-show="abierto"
        x-cloak
        x-transition.opacity.duration.120ms
        class="absolute z-40 mt-1 w-full overflow-hidden rounded-sm border border-arena bg-white shadow-xl"
    >
        <div class="border-b border-arena p-2">
            <input
                x-ref="buscar"
                x-model="q"
                type="text"
                autocomplete="off"
                placeholder="Buscar..."
                class="form-input"
            >
        </div>

        <div class="max-h-56 overflow-y-auto p-1">
            @forelse ($opciones as $opcion)
                @php
                    $valor = (string) $opcion['value'];
                    $textoOpcion = mb_strtolower($opcion['label'].' '.($opcion['detalle'] ?? ''));
                    $activo = $multiple
                        ? in_array($opcion['value'], $seleccionados, false) || in_array($valor, array_map('strval', $seleccionados), true)
                        : $valor === (string) $seleccion;
                @endphp

                @if ($multiple)
                    <label
                        data-texto="{{ $textoOpcion }}"
                        x-show="q === '' || $el.dataset.texto.includes(q.toLowerCase())"
                        class="flex cursor-pointer items-start gap-2 rounded-sm px-2 py-1.5 hover:bg-corteza-suave"
                    >
                        <input
                            type="checkbox"
                            value="{{ $valor }}"
                            wire:model="{{ $modelo }}"
                            class="mt-0.5 size-4 rounded-sm border-arena accent-pino"
                        >
                        <span class="text-sm text-tinta">
                            {{ $opcion['label'] }}
                            @if (! empty($opcion['detalle']))
                                <span class="block text-xs text-tinta-suave">{{ $opcion['detalle'] }}</span>
                            @endif
                        </span>
                    </label>
                @else
                    <button
                        type="button"
                        data-texto="{{ $textoOpcion }}"
                        x-show="q === '' || $el.dataset.texto.includes(q.toLowerCase())"
                        x-on:click="$wire.set('{{ $modelo }}', '{{ $valor }}'); abierto = false"
                        class="flex w-full items-center justify-between gap-2 rounded-sm px-2 py-1.5 text-left text-sm transition-colors hover:bg-corteza-suave {{ $activo ? 'bg-pino-suave font-medium text-pino' : 'text-tinta' }}"
                    >
                        <span>
                            {{ $opcion['label'] }}
                            @if (! empty($opcion['detalle']))
                                <span class="block text-xs text-tinta-suave">{{ $opcion['detalle'] }}</span>
                            @endif
                        </span>
                        @if ($activo)
                            <flux:icon.check class="size-4 shrink-0" />
                        @endif
                    </button>
                @endif
            @empty
                <p class="px-2 py-3 text-center text-sm text-tinta-suave">{{ $placeholder }}</p>
            @endforelse
        </div>
    </div>
</div>
