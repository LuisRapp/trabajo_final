<div class="w-full">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
                <flux:icon.clipboard-document-list class="size-6" />
                Tareas del Lote #{{ $lote->id_lote }}
            </h1>
            <p class="mt-1 text-sm text-tinta-suave">
                {{ $lote->especie ?? 'Sin especie' }}
                &middot; {{ $lote->ubicacion }}
                &middot; {{ number_format((float) ($lote->superficie ?? 0), 2) }} ha
                &middot; {{ ucfirst(str_replace('_', ' ', $lote->estado)) }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn-secondary" href="{{ route('lotes.ficha', ['loteId' => $lote->id_lote]) }}">
                <flux:icon.arrow-left class="size-4" />
                Volver al lote
            </a>
            <a class="btn-secondary" href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}">
                <flux:icon.sparkles class="size-4" />
                Ver propuestas
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-5">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-ui.card class="overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-arena bg-hueso px-4 py-3">
            <h5 class="text-sm font-semibold text-tinta">Actividades planificadas</h5>
            <span class="text-xs text-tinta-suave">
                {{ count($tareas) }} {{ count($tareas) === 1 ? 'tarea' : 'tareas' }}
            </span>
        </div>

        <div class="p-4">
            @error('tareas')
                <x-ui.alert variant="danger" class="mb-4" dismissible="false">
                    {{ $message }}
                </x-ui.alert>
            @enderror

            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-[220px]">Tipo de tarea</th>
                            <th class="w-[160px] text-right">Superficie (ha)</th>
                            <th>Observaciones</th>
                            <th class="w-[80px] text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareas as $i => $row)
                            <tr wire:key="row-{{ $i }}">
                                <td>
                                    <select class="form-input"
                                        wire:model.live="tareas.{{ $i }}.tipo_tarea" @if($guardando) disabled @endif>
                                        @foreach($taskTypes as $tt)
                                            <option value="{{ $tt->value }}" wire:key="option-{{ $tt->value }}">{{ $tt->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('tareas.' . $i . '.tipo_tarea')
                                        <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td>
                                    <input autocomplete="off" type="number" step="0.01" min="0"
                                        class="form-input text-right"
                                        wire:model.live="tareas.{{ $i }}.superficie_afectada_ha" placeholder="Opcional" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.superficie_afectada_ha')
                                        <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td>
                                    <input autocomplete="off" type="text"
                                        class="form-input"
                                        wire:model.live="tareas.{{ $i }}.observaciones" placeholder="Opcional" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.observaciones')
                                        <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="text-right">
                                    <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="removeTareaRow($i)" :disabled="$guardando" title="Eliminar" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <x-ui.button variant="secondary" icon="plus" wire:click="addTareaRow" :disabled="$guardando">
                                        Agregar tarea
                                    </x-ui.button>
                                    <div class="text-sm text-tinta-suave">
                                        Total planificado: <strong class="text-tinta">{{ number_format($this->totalSuperficie, 2) }} ha</strong>
                                        &middot; Superficie del lote: <strong class="text-tinta">{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </x-ui.table-container>

            <p class="mt-3 text-xs text-tinta-suave">
                Si dejás la superficie vacía, se toma la del lote.
            </p>
        </div>

        <div class="flex justify-end gap-2 border-t border-arena bg-white px-4 py-3">
            <x-ui.button variant="primary" icon="check"
                type="button" wire:click="guardar" :disabled="$guardando">
                Guardar tareas y ver propuestas
            </x-ui.button>
        </div>
    </x-ui.card>
</div>
