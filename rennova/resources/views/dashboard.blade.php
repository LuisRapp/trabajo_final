<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @php
            $lotes = \App\Models\Lote::query()->orderByDesc('created_at')->get();
            // Tomar el id_lote enviado por querystring y forzar entero
            $loteSeleccionadoId = (int) request('lote');
            $lote = null;
            if ($loteSeleccionadoId) {
                $lote = \App\Models\Lote::find($loteSeleccionadoId);
            }
            if (!$lote) {
                $lote = $lotes->first(); // por defecto: último lote cargado
            }
            $svc = app(\App\Services\ClimaDecisionService::class);
            $data = $lote ? $svc->analizarYRecomendar($lote) : null;
        @endphp

        <div class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-4">
            <div class="text-sm text-neutral-700">
                <span class="font-medium">Seleccionar lote:</span>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <select name="lote" class="rounded-md border px-3 py-2 text-sm">
                    @foreach($lotes as $op)
                        <option value="{{ $op->id_lote }}" @selected(optional($lote)->id_lote === $op->id_lote)>
                            {{ $op->propietario ?? $op->nombre ?? ('Lote #' . $op->id_lote) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md border bg-neutral-50 px-3 py-2 text-sm">Ver</button>
            </form>
        </div>

        @include('partials.selector-lote', ['lotes' => \App\Models\Lote::all(), 'loteSeleccionado' => $pronosticoData['loteModelo'] ?? null])

        @if($data)
            <x-clima.pronostico
                :lote="$data['lote_nombre'] ?? ($lote->nombre ?? 'Lote')"
                :ventana="$data['ventana'] ?? '2 días'"
                :diaCritico="$data['dia_critico'] ?? 'Próximo'"
                :recomendacion="$data['recomendacion'] ?? 'NORMAL'"
                :pronostico="$data['pronostico'] ?? []"
                :diasPerdidos="$data['dias_perdidos'] ?? 0"
                :rangoPerdidos="$data['rango_perdidos'] ?? ''"
                :deficitTn="$data['deficit_tn'] ?? 0"
                :accionPorcentaje="$data['accion_porcentaje'] ?? 0"
                :accionDias="$data['accion_dias'] ?? ''"
            />
        @else
            <div class="rounded-xl border border-neutral-200 p-6 text-center text-neutral-600">
                No hay lotes configurados aún. Crea un lote para ver el pronóstico.
            </div>
        @endif
    </div>
</x-layouts.app>
