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
<x-ui.card class="mb-6">
    <div class="p-4">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-semibold text-tinta shrink-0">Seleccionar lote:</label>
            <select name="lote" class="form-input max-w-[350px]" onchange="this.form.submit()">
                @foreach($lotes as $op)
                    <option value="{{ $op->id_lote }}" @selected(optional($loteSeleccionado)->id_lote === $op->id_lote)>
                        {{ $op->nombre ?? $op->propietario ?? ('Lote #' . $op->id_lote) }}
                    </option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <input class="rounded-sm border-arena text-pino focus:ring-pino" type="checkbox" role="switch" id="demoSwitch" name="demo" value="1" @checked($demoActivo)>
                <label class="text-sm text-tinta-suave" for="demoSwitch">Modo demo</label>
            </div>
            <select name="escenario" id="demoScenario" class="form-input disabled:opacity-50 max-w-[240px]" @disabled(!$demoActivo)>
                <option value="">Auto por nombre</option>
                @foreach($escenarios as $key => $label)
                    <option value="{{ $key }}" @selected($escenario === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-ui.button variant="primary" size="sm" type="submit">Actualizar</x-ui.button>
        </form>
    </div>
</x-ui.card>
@else
<x-ui.alert variant="warning" dismissible="false" class="mb-6">
    No hay lotes activos disponibles
</x-ui.alert>
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
