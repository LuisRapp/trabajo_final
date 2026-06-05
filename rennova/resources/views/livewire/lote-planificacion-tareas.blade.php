<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-xl font-bold text-slate-900">📋 Planificar tareas del Lote #{{ $lote->id_lote }}</h4>
            <div class="text-slate-500 text-sm">
                Definí qué actividades vas a realizar (ej: 5 ha raleo + 5 ha tala rasa). Esto alimenta el histórico y dispara recomendaciones.
            </div>
        </div>
        <div class="flex gap-2">
            <a class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" href="{{ route('lotes.index') }}">
                ← Volver a Lotes
            </a>
            <a class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-brand bg-white text-brand rounded-lg text-sm font-medium hover:bg-brand/5 transition-colors" href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}">
                ✨ Ver recomendaciones
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
            <strong>🌳 Lote</strong>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $lote->especie ?? 'Sin especie' }}</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="text-slate-500 text-sm">Ubicación</div>
                    <div class="font-semibold">{{ $lote->ubicacion }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-sm">Superficie</div>
                    <div class="font-semibold">{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</div>
                </div>
                <div>
                    <div class="text-slate-500 text-sm">Estado</div>
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lote->estado === 'en_proceso' ? 'bg-brand/10 text-brand' : 'bg-emerald-100 text-emerald-700' }}">{{ $lote->estado }}</span>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200 my-6">

            @error('tareas')
                <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm">
                    <span>⚠</span> {{ $message }}
                </div>
            @enderror

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[220px]">Tipo de tarea</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-[160px]">Superficie (ha)</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Observaciones</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-[80px]">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tareas as $i => $row)
                            <tr wire:key="row-{{ $i }}" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5">
                                    <select class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                        wire:model.live="tareas.{{ $i }}.tipo_tarea" @if($guardando) disabled @endif>
                                        @foreach($taskTypes as $tt)
                                            <option value="{{ $tt->value }}" wire:key="option-{{ $tt->value }}">{{ $tt->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('tareas.' . $i . '.tipo_tarea')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="number" step="0.01" min="0"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-right transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                        wire:model.live="tareas.{{ $i }}.superficie_afectada_ha" placeholder="(opcional)" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.superficie_afectada_ha')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="text"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                        wire:model.live="tareas.{{ $i }}.observaciones" placeholder="Opcional" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.observaciones')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <button class="inline-flex items-center gap-1 px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-xs font-medium transition-colors"
                                        type="button" wire:click="removeTareaRow({{ $i }})" @if($guardando) disabled @endif>
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex flex-wrap justify-between items-center">
                                    <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                                        type="button" wire:click="addTareaRow" @if($guardando) disabled @endif>
                                        ➕ Agregar tarea
                                    </button>
                                    <div class="text-slate-500 text-sm">
                                        Total planificado: <strong>{{ number_format($this->totalSuperficie, 2) }} ha</strong>
                                        · Superficie lote: <strong>{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                <span>💡</span>
                <small>
                    Tip: si dejás la superficie en blanco, se asume la del lote al estimar (pero para dividir 5/5 completá superficies).
                </small>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 bg-white border-t border-slate-200">
            <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors"
                type="button" wire:click="guardar" @if($guardando) disabled @endif>
                ✓ Guardar y generar recomendaciones
            </button>
        </div>
    </div>
</div>
