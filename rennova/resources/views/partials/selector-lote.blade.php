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
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-semibold text-slate-700 shrink-0">Seleccionar lote:</label>
            <select name="lote" class="rounded-lg border border-brand bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand" style="max-width: 350px;" onchange="this.form.submit()">
                @foreach($lotes as $op)
                    <option value="{{ $op->id_lote }}" @selected(optional($loteSeleccionado)->id_lote === $op->id_lote)>
                        {{ $op->nombre ?? $op->propietario ?? ('Lote #' . $op->id_lote) }}
                    </option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <input class="rounded border-slate-300 text-brand shadow-sm focus:ring-brand" type="checkbox" role="switch" id="demoSwitch" name="demo" value="1" @checked($demoActivo)>
                <label class="text-sm text-slate-600" for="demoSwitch">Modo demo</label>
            </div>
            <select name="escenario" id="demoScenario" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand disabled:opacity-50" style="max-width: 240px;" @disabled(!$demoActivo)>
                <option value="">Auto por nombre</option>
                @foreach($escenarios as $key => $label)
                    <option value="{{ $key }}" @selected($escenario === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">Actualizar</button>
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
