<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="flex flex-wrap items-center gap-2 text-2xl font-bold text-tinta">
                <flux:icon.map-pin class="size-6" />
                Lote #{{ $lote->id_lote }} — {{ $lote->propietario ?? 'Sin propietario' }}

                @php
                    $varianteEstado = match ($lote->estado) {
                        'activo' => 'success',
                        'en_proceso' => 'warning',
                        'cerrado' => 'info',
                        default => 'neutral',
                    };
                @endphp
                <x-ui.badge variant="{{ $varianteEstado }}">{{ ucfirst(str_replace('_', ' ', $lote->estado)) }}</x-ui.badge>
            </h1>
            <p class="mt-1 text-sm text-tinta-suave">
                {{ $lote->especie ?? 'Sin especie' }}
                &middot; {{ $lote->ubicacion }}
                &middot; {{ number_format((float) ($lote->superficie ?? 0), 2) }} ha
                &middot; Tarea principal: <span class="font-medium text-tinta">{{ $tareaPrincipal ?? 'sin definir' }}</span>
            </p>
        </div>

        <a class="btn-secondary" href="{{ route('lotes.index') }}">
            <flux:icon.arrow-left class="size-4" />
            Volver a Lotes
        </a>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Proximo paso: la accion principal de la pantalla --}}
    <div class="mb-4 rounded-sm border p-4 {{ $siguiente['accion'] === null ? 'border-arena bg-white' : 'border-pino/30 bg-pino-suave' }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-tinta">{{ $siguiente['titulo'] }}</h2>
                <p class="mt-1 text-sm text-tinta-suave">{{ $siguiente['texto'] }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($siguiente['accion'] === 'tareas')
                    <a class="btn-primary" href="{{ route('lotes.tareas', ['loteId' => $lote->id_lote]) }}">
                        <flux:icon.clipboard-document-list class="size-4" />
                        Planificar tareas
                    </a>
                @elseif ($siguiente['accion'] === 'recursos')
                    <a class="btn-primary" href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}">
                        <flux:icon.sparkles class="size-4" />
                        Ver sugerencias
                    </a>
                    @can('editar-asignaciones-lote')
                        <a class="btn-secondary" href="{{ route('asignaciones-lote.index', ['lote' => $lote->id_lote]) }}">
                            <flux:icon.link class="size-4" />
                            Asignar manualmente
                        </a>
                    @endcan
                @elseif ($siguiente['accion'] === 'finalizar')
                    <x-ui.confirmador accion="finalizarLote"
                        variante="primary"
                        icono="flag"
                        tamano="default"
                        mensaje="¿Finalizar el lote «{{ $lote->propietario ?? '#'.$lote->id_lote }}»?"
                        detalle="Se liberan los empleados y las maquinarias asignadas, y se cierran las propuestas del lote."
                        etiqueta="Finalizar"
                        texto="Finalizar lote" />
                @endif
            </div>
        </div>
    </div>

    {{-- Paso a paso del ciclo --}}
    <div class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
        @foreach ($pasos as $paso)
            <div class="flex items-center gap-3 rounded-sm border border-arena bg-white px-3 py-2">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $paso['completo'] ? 'bg-pino text-white' : 'bg-corteza-suave text-tinta-suave' }}">
                    @if ($paso['completo'])
                        <flux:icon.check class="size-3.5" />
                    @else
                        {{ $paso['numero'] }}
                    @endif
                </span>
                <span class="text-sm {{ $paso['completo'] ? 'font-medium text-tinta' : 'text-tinta-suave' }}">
                    {{ $paso['titulo'] }}
                </span>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Tareas --}}
        <x-ui.card class="overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-arena bg-hueso px-4 py-3">
                <h5 class="flex items-center gap-2 text-sm font-semibold text-tinta">
                    <flux:icon.clipboard-document-list class="size-4" />
                    Tareas planificadas
                </h5>
                <div class="flex items-center gap-2">
                    <x-ui.badge variant="neutral">{{ $tareas->count() }}</x-ui.badge>
                    @if ($siguiente['accion'] !== null)
                        <x-ui.button variant="secondary" size="sm" icon="{{ $tareas->isEmpty() ? 'plus' : 'pencil-square' }}"
                            href="{{ route('lotes.tareas', ['loteId' => $lote->id_lote]) }}">
                            {{ $tareas->isEmpty() ? 'Planificar' : 'Editar' }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="p-4">
                @if ($tareas->isEmpty())
                    <p class="rounded-sm border border-arena bg-hueso py-6 text-center text-sm text-tinta-suave">
                        Todavía no hay tareas planificadas.
                    </p>
                @else
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tarea</th>
                                    <th class="text-right">Superficie</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tareas as $tarea)
                                    <tr wire:key="tarea-{{ $tarea->id_lote_tarea }}">
                                        <td class="font-medium text-tinta">{{ $tarea->tipo_tarea_label }}</td>
                                        <td class="text-right text-tinta-suave">
                                            {{ $tarea->superficie_afectada_ha !== null ? number_format((float) $tarea->superficie_afectada_ha, 2).' ha' : '—' }}
                                        </td>
                                        <td>
                                            <x-ui.badge variant="{{ $tarea->estado === 'completada' ? 'success' : ($tarea->estado === 'en_ejecucion' ? 'warning' : 'neutral') }}">
                                                {{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}
                                            </x-ui.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>
                @endif
            </div>
        </x-ui.card>

        {{-- Recursos --}}
        <x-ui.card class="overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-arena bg-hueso px-4 py-3">
                <h5 class="flex items-center gap-2 text-sm font-semibold text-tinta">
                    <flux:icon.users class="size-4" />
                    Recursos asignados
                </h5>
                <x-ui.badge variant="neutral">{{ $empleados->count() + $maquinarias->count() }}</x-ui.badge>
            </div>

            <div class="p-4">
                @if ($empleados->isEmpty() && $maquinarias->isEmpty())
                    <p class="rounded-sm border border-arena bg-hueso py-6 text-center text-sm text-tinta-suave">
                        Todavía no hay recursos asignados.
                    </p>
                @else
                    <div class="space-y-4">
                        <div>
                            <h6 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-tinta-suave">
                                <flux:icon.user class="size-3.5" />
                                Empleados
                            </h6>

                            @if ($empleados->isEmpty())
                                <p class="text-sm text-tinta-suave">Sin empleados asignados.</p>
                            @else
                                <ul class="space-y-1">
                                    @foreach ($empleados as $empleado)
                                        <li wire:key="empleado-{{ $empleado->id_empleado }}" class="flex items-center justify-between gap-2 rounded-sm border border-arena px-3 py-1.5">
                                            <span class="text-sm text-tinta">{{ $empleado->apellido }}, {{ $empleado->nombre }}</span>
                                            <span class="text-xs text-tinta-suave">{{ $empleado->rolLaboral?->nombre ?? 'Sin rol' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div>
                            <h6 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-tinta-suave">
                                <flux:icon.truck class="size-3.5" />
                                Maquinarias
                            </h6>

                            @if ($maquinarias->isEmpty())
                                <p class="text-sm text-tinta-suave">Sin maquinarias asignadas.</p>
                            @else
                                <ul class="space-y-1">
                                    @foreach ($maquinarias as $maquinaria)
                                        <li wire:key="maquinaria-{{ $maquinaria->id_maquinaria }}" class="flex items-center justify-between gap-2 rounded-sm border border-arena px-3 py-1.5">
                                            <span class="text-sm text-tinta">{{ $maquinaria->modelo ?: 'Sin modelo' }}</span>
                                            <span class="text-xs text-tinta-suave">{{ $maquinaria->tipoMaquinaria?->nombre ?? 'Sin tipo' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>
</div>
