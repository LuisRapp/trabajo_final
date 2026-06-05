@php
    $lotes = $lotes ?? \App\Models\Lote::where('estado', 'activo')->get();
    $loteSeleccionado = $loteSeleccionado ?? ($lotes->first() ?? null);
    $demoActivo = request()->boolean('demo');
    $escenario = (string) request('escenario', '');
    $escenarios = [
        'normal' => 'Normal',
        'lluvia_moderada' => 'Lluvia moderada',
        'lluvia_intensa' => 'Lluvia intensa',
        'reaccion_inmediata' => 'Reaccion inmediata',
        'mantenimiento_preventivo' => 'Mantenimiento preventivo',
        'suspension_total' => 'Suspension total',
    ];
@endphp

@if($lotes && $lotes->count() > 0)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
    <div class="p-4">
                <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-center gap-3">
            <label class="fw-semibold text-dark small mb-0" style="min-width: fit-content;">Seleccionar lote:</label>
            <select name="lote" class="form-select form-select-sm" style="max-width: 350px; border: 2px solid var(--primary-color);" onchange="this.form.submit()">
                @foreach($lotes as $op)
                    <option value="{{ $op->id_lote }}" @selected(optional($loteSeleccionado)->id_lote === $op->id_lote)>
                        {{ $op->nombre ?? $op->propietario ?? ('Lote #' . $op->id_lote) }}
                    </option>
                @endforeach
            </select>
            <div class="form-check form-switch ms-2">
                <input class="form-check-input" type="checkbox" role="switch" id="demoSwitch" name="demo" value="1" @checked($demoActivo)>
                <label class="form-check-label small" for="demoSwitch">Modo demo</label>
            </div>
            <select name="escenario" id="demoScenario" class="form-select form-select-sm" style="max-width: 240px;" @disabled(!$demoActivo)>
                <option value="">Auto por nombre</option>
                @foreach($escenarios as $key => $label)
                    <option value="{{ $key }}" @selected($escenario === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-brand hover:bg-brand-hover text-white px-3 py-1.5 rounded-lg text-sm font-medium">Actualizar</button>
        </form>
    </div>
</div>
@else
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm mb-6">
    ⚠️ No hay lotes activos disponibles
</div>
@endif

<script>
    (function () {
        const demoSwitch = document.getElementById('demoSwitch');
        const demoScenario = document.getElementById('demoScenario');
        if (!demoSwitch || !demoScenario) return;
        const sync = () => {
            demoScenario.disabled = !demoSwitch.checked;
            if (!demoSwitch.checked) {
                demoScenario.value = '';
            }
        };
        demoSwitch.addEventListener('change', sync);
        sync();
    })();
</script>
