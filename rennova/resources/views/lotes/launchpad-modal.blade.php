<div x-data="{ 
    open: false, 
    currentPageTeam: 1, 
    currentPageMachinery: 1, 
    currentPageLogistics: 1,
    itemsPerPage: 5
}" class="relative">
    <!-- Trigger example -->
    <button @click="open = true" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
        ▶ START OPERATION
    </button>

    <!-- Overlay -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40"></div>

    <!-- Modal -->
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        aria-modal="true"
        role="dialog"
    >
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div>
                    <h2 class="text-xl font-semibold">Launchpad · Lote #{{ $lote->id_lote ?? '' }}</h2>
                    <p class="text-sm text-slate-500">Recomendaciones pre-calculadas (Week 1).</p>
                </div>
                <button @click="open = false" class="rounded-md p-2 text-slate-500 hover:bg-slate-100">✕</button>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                <!-- Team -->
                <div class="rounded-xl border p-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-600">Team (Top 5)</h3>
                    <div class="space-y-3">
                        @php
                            $allEmployees = ($proposal->proposedEmployees ?? collect())->take(5)->values();
                            $totalTeam = count($allEmployees);
                            $totalPagesTeam = max(1, ceil($totalTeam / 5));
                        @endphp
                        <template x-for="(emp, idx) in $data.allEmployeesTeam.slice((currentPageTeam - 1) * itemsPerPage, currentPageTeam * itemsPerPage)" :key="idx">
                            <label class="flex items-center justify-between gap-3 rounded-lg border px-3 py-2">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="h-4 w-4" checked>
                                    <div>
                                        <div class="text-sm font-medium" x-text="emp.nombre"></div>
                                        <div class="text-xs text-slate-500" x-text="emp.rol"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-emerald-600">Eficiencia ↑</span>
                            </label>
                        </template>
                        @foreach(($proposal->proposedEmployees ?? collect())->take(5) as $row)
                            <label class="flex items-center justify-between gap-3 rounded-lg border px-3 py-2">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="h-4 w-4" checked>
                                    <div>
                                        <div class="text-sm font-medium">{{ $row->empleado->nombre ?? 'Empleado' }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->empleado->rolLaboral->nombre ?? 'Sin rol' }}</div>
                                    </div>
                                </div>
                                <span class="text-xs text-emerald-600">Eficiencia ↑</span>
                            </label>
                        @endforeach
                    </div>
                    @if($totalTeam > 5)
                        <div class="mt-4 flex items-center justify-between border-t pt-3">
                            <button @click="currentPageTeam = Math.max(1, currentPageTeam - 1)" :disabled="currentPageTeam === 1" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                ← Anterior
                            </button>
                            <span class="text-xs text-slate-600" x-text="`Pág ${currentPageTeam} de {{ $totalPagesTeam }}`"></span>
                            <button @click="currentPageTeam = Math.min({{ $totalPagesTeam }}, currentPageTeam + 1)" :disabled="currentPageTeam === {{ $totalPagesTeam }}" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                Siguiente →
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Machinery -->
                <div class="rounded-xl border p-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-600">Machinery</h3>
                    <div class="space-y-3">
                        @php
                            $allMachinery = ($proposal->proposedMaquinarias ?? collect())->values();
                            $totalMachinery = count($allMachinery);
                            $totalPagesMachinery = max(1, ceil($totalMachinery / 5));
                        @endphp
                        @foreach(($proposal->proposedMaquinarias ?? collect()) as $row)
                            <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                                <div>
                                    <div class="text-sm font-medium">{{ $row->maquinaria->nombre ?? 'Maquinaria' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->maquinaria->tipoMaquinaria->nombre ?? 'Tipo' }}</div>
                                </div>
                                @php
                                    $ocupada = (bool) ($row->maquinaria->ocupada ?? false);
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $ocupada ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $ocupada ? 'Ocupada' : 'Libre' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($totalMachinery > 5)
                        <div class="mt-4 flex items-center justify-between border-t pt-3">
                            <button @click="currentPageMachinery = Math.max(1, currentPageMachinery - 1)" :disabled="currentPageMachinery === 1" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                ← Anterior
                            </button>
                            <span class="text-xs text-slate-600" x-text="`Pág ${currentPageMachinery} de {{ $totalPagesMachinery }}`"></span>
                            <button @click="currentPageMachinery = Math.min({{ $totalPagesMachinery }}, currentPageMachinery + 1)" :disabled="currentPageMachinery === {{ $totalPagesMachinery }}" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                Siguiente →
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Logistics -->
                <div class="rounded-xl border p-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-600">Logistics (Week 1)</h3>
                    <div class="space-y-3">
                        @php
                            $allLogistics = ($proposal->proposedInsumos ?? collect())->values();
                            $totalLogistics = count($allLogistics);
                            $totalPagesLogistics = max(1, ceil($totalLogistics / 5));
                        @endphp
                        @foreach(($proposal->proposedInsumos ?? collect()) as $row)
                            <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                                <div>
                                    <div class="text-sm font-medium">{{ $row->insumo->nombre ?? 'Insumo' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->insumo->unidadMedida->abreviatura ?? '' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold">{{ $row->cantidad_semana_1 ?? 0 }}</div>
                                    <div class="text-xs text-slate-500">${{ number_format((float) ($row->costo_estimado_semana_1 ?? 0), 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($totalLogistics > 5)
                        <div class="mt-4 flex items-center justify-between border-t pt-3">
                            <button @click="currentPageLogistics = Math.max(1, currentPageLogistics - 1)" :disabled="currentPageLogistics === 1" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                ← Anterior
                            </button>
                            <span class="text-xs text-slate-600" x-text="`Pág ${currentPageLogistics} de {{ $totalPagesLogistics }}`"></span>
                            <button @click="currentPageLogistics = Math.min({{ $totalPagesLogistics }}, currentPageLogistics + 1)" :disabled="currentPageLogistics === {{ $totalPagesLogistics }}" class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                                Siguiente →
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t px-6 py-4">
                <button @click="open = false" class="rounded-lg border px-4 py-2 text-sm">Cancelar</button>
                <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    CONFIRM & LAUNCH
                </button>
            </div>
        </div>
    </div>
</div>
