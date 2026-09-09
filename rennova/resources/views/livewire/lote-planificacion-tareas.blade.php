<div class="w-full">
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-xl font-bold text-tinta flex items-center gap-2">
                <flux:icon.clipboard-document-list class="size-5" />
                Planificar tareas del Lote #{{ $lote->id_lote }}
            </h4>
            <div class="text-tinta-suave text-sm">
                Definí qué actividades vas a realizar (ej: 5 ha raleo + 5 ha tala rasa). Esto alimenta el histórico y dispara propuestas de asignación.
            </div>
        </div>
        <div class="flex gap-2">
            <a class="btn-secondary" href="{{ route('lotes.index') }}">
                <flux:icon.arrow-left class="size-4" />
                Volver a Lotes
            </a>
            <a class="btn-secondary" href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}">
                <flux:icon.sparkles class="size-4" />
                Ver propuestas de asignación
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-5">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
            <strong class="text-tinta flex items-center gap-2">
                <flux:icon.map-pin class="size-5" />
                Lote
            </strong>
            <x-ui.badge variant="neutral">{{ $lote->especie ?? 'Sin especie' }}</x-ui.badge>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="text-tinta-suave text-sm">Ubicación</div>
                    <div class="font-semibold text-tinta">{{ $lote->ubicacion }}</div>
                </div>
                <div>
                    <div class="text-tinta-suave text-sm">Superficie</div>
                    <div class="font-semibold text-tinta">{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</div>
                </div>
                <div>
                    <div class="text-tinta-suave text-sm">Estado</div>
                    <div>
                        <x-ui.badge variant="{{ $lote->estado === 'en_proceso' ? 'info' : 'success' }}">{{ $lote->estado }}</x-ui.badge>
                    </div>
                </div>
            </div>

            <hr class="border-arena my-6">

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
                            <th class="text-right w-[160px]">Superficie (ha)</th>
                            <th>Observaciones</th>
                            <th class="text-right w-[80px]">Acción</th>
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
                                        <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                        class="form-input text-right"
                                        wire:model.live="tareas.{{ $i }}.superficie_afectada_ha" placeholder="(opcional)" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.superficie_afectada_ha')
                                        <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text"
                                        class="form-input"
                                        wire:model.live="tareas.{{ $i }}.observaciones" placeholder="Opcional" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.observaciones')
                                        <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="text-right">
                                    <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="removeTareaRow({{ $i }})" @if($guardando) disabled @endif title="Eliminar" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex flex-wrap justify-between items-center">
                                    <x-ui.button variant="secondary" icon="plus" wire:click="addTareaRow" @if($guardando) disabled @endif>
                                        Agregar tarea
                                    </x-ui.button>
                                    <div class="text-tinta-suave text-sm">
                                        Total planificado: <strong class="text-tinta">{{ number_format($this->totalSuperficie, 2) }} ha</strong>
                                        · Superficie lote: <strong class="text-tinta">{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </x-ui.table-container>

            <x-ui.alert variant="info" class="mt-4" dismissible="false">
                Tip: si dejás la superficie en blanco, se asume la del lote al estimar (pero para dividir 5/5 completá superficies).
            </x-ui.alert>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 bg-blanco border-t border-arena">
            <x-ui.button variant="primary" icon="check"
                type="button" wire:click="guardar" @if($guardando) disabled @endif>
                Guardar y generar propuestas de asignación
            </x-ui.button>
        </div>
    </x-ui.card>
</div>
