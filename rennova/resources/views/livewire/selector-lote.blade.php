<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- SELECTOR DE LOTE --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-4">
            <label class="text-sm font-semibold text-slate-500 shrink-0">Seleccionar lote:</label>
            <select wire:model.live="loteSeleccionado"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20 max-w-xs">
                <option value="">-- Seleccionar --</option>
                @foreach($lotes as $op)
                    <option value="{{ $op['id_lote'] }}" wire:key="option-{{ $op['id_lote'] }}">
                        {{ $op['propietario'] ?? ('Lote #' . $op['id_lote']) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- COMPONENTE DE CLIMA --}}
    @if($pronosticoData)
        <div class="mb-8">
            <x-clima.pronostico
                :alerta="$pronosticoData['alerta'] ?? null"
                :pronostico="$pronosticoData['pronostico'] ?? []"
                :analisisImpacto="$pronosticoData['analisisImpacto'] ?? []"
                :lote="$pronosticoData['loteNombre'] ?? null"
            />
        </div>
    @endif
</div>
