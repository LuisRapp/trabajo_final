@props([
    'accion',
    'parametros' => [],
    'mensaje',
    'detalle' => null,
    'titulo' => 'Confirmar eliminación',
    'etiqueta' => 'Eliminar',
    'texto' => null,
    'variante' => 'danger',
    'tamano' => 'sm',
    'icono' => 'trash',
    'tituloBoton' => 'Eliminar',
])

<x-ui.button
    :variant="$variante"
    :size="$tamano"
    :icon="$icono"
    :title="$tituloBoton"
    data-accion="{{ $accion }}"
    data-parametros="{{ json_encode(array_values($parametros ?? [])) }}"
    data-titulo="{{ $titulo }}"
    data-mensaje="{{ $mensaje }}"
    data-detalle="{{ $detalle }}"
    data-etiqueta="{{ $etiqueta }}"
    x-on:click="$dispatch('confirmar-accion', Object.assign({}, $el.dataset))"
    {{ $attributes }}
>{{ $texto }}</x-ui.button>
