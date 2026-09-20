<div class="w-full">
    {{-- SELECTOR DE LOTE --}}
    <x-ui.card class="p-5 mb-5">
        <div class="flex items-center gap-4">
            <label class="text-sm font-semibold text-tinta-suave shrink-0">Seleccionar lote:</label>
            <select wire:model.live="loteSeleccionado"
                class="form-input max-w-xs">
                <option value="">-- Seleccionar --</option>
                @foreach($lotes as $op)
                    <option value="{{ $op['id_lote'] }}" wire:key="option-{{ $op['id_lote'] }}">
                        {{ $op['propietario'] ?? ('Lote #' . $op['id_lote']) }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-ui.card>

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
