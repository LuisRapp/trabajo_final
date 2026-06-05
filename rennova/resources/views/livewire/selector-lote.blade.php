<div>
    {{-- SELECTOR DE LOTE --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <label class="fw-semibold text-muted small mb-0">Seleccionar lote:</label>
                <select wire:model.live="loteSeleccionado" class="form-select form-select-sm" style="max-width: 320px;">
                    <option value="">-- Seleccionar --</option>
                    @foreach($lotes as $op)
                        <option value="{{ $op['id_lote'] }}" wire:key="option-{{ $op['id_lote'] }}">
                            {{ $op['propietario'] ?? ('Lote #' . $op['id_lote']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- COMPONENTE DE CLIMA --}}
    @if($pronosticoData)
        <div class="mb-5">
            <x-clima.pronostico 
                :alerta="$pronosticoData['alerta'] ?? null"
                :pronostico="$pronosticoData['pronostico'] ?? []"
                :analisisImpacto="$pronosticoData['analisisImpacto'] ?? []"
                :lote="$pronosticoData['loteNombre'] ?? null"
            />
        </div>
    @endif
</div>
