<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @if(!empty($loteId))
        <div class="flex flex-wrap justify-between items-center mb-4">
            <div>
                <h4 class="text-xl font-bold text-slate-900">✨ Recomendaciones del Lote #{{ $loteId }}</h4>
                <div class="text-slate-500 text-sm">Al pasar el lote a <strong>en proceso</strong> se generan estas propuestas.</div>
            </div>
            <div class="flex gap-2">
                <a class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" href="{{ route('lotes.index') }}">
                    ← Volver a Lotes
                </a>
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-brand bg-white text-brand rounded-lg text-sm font-medium hover:bg-brand/5 transition-colors" wire:click="generarAhora" @if($guardando) disabled @endif>
                    ⚙️ Generar ahora
                </button>
                @endcanany
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="refreshProposals" @if($guardando) disabled @endif>
                    ↻ Refrescar
                </button>
                @endcanany
            </div>
        </div>
    @endif

    <div class="flex border-b border-slate-200 mb-6" role="tablist">
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $mostrar_listado ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', true)"
        >
            ⚡ Propuestas
        </button>
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ !$mostrar_listado ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', false)"
            @if(!$selected_proposal_id) disabled @endif
        >
            📋 Detalle / Confirmar
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
            <span class="text-red-600">⚠</span>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
        </div>
    @endif

    <div>
        <div class="{{ $mostrar_listado ? '' : 'hidden' }}">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">✨ Propuestas Automáticas</h5>
                    <div class="flex gap-2">
                        @if(empty($loteId))
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <button class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="refreshProposals" @if($guardando) disabled @endif>
                                ↻ Refrescar
                            </button>
                            @endcanany
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    @if(empty($loteId))
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lote</label>
                            <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filter_lote_id">
                                <option value="">Todos</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}" wire:key="option-{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                            <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filter_status">
                                <option value="">Todos</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="applied">Applied</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="text-slate-500 text-sm">
                                Mostrando {{ is_countable($proposals) ? count($proposals) : 0 }} propuestas
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tarea</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estimación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Creada</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($proposals as $p)
                                    <tr wire:key="row-{{ $p->id_allocation_proposal }}" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5"><strong>#{{ $p->id_allocation_proposal }}</strong></td>
                                        <td class="px-4 py-2.5">
                                            <div><strong>Lote #{{ $p->id_lote }}</strong></div>
                                            <div class="text-slate-500 text-xs">{{ $p->lote->ubicacion ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $p->tipo_tarea }}</span>
                                            @if($p->id_lote_tarea)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700">Tarea #{{ $p->id_lote_tarea }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-xs">
                                            <div>Persona-día: <strong>{{ $p->estimated_person_days ?? 'N/A' }}</strong></div>
                                            <div>Máquina-día: <strong>{{ $p->estimated_machine_days ?? 'N/A' }}</strong></div>
                                            <div>Duración: <strong>{{ $p->estimated_duration_days ?? 'N/A' }}</strong></div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @php
                                                $badge = match($p->status) {
                                                    'applied' => 'bg-emerald-100 text-emerald-700',
                                                    'confirmed' => 'bg-brand/10 text-brand',
                                                    default => 'bg-amber-100 text-amber-700'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $p->status }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs text-slate-500">{{ $p->created_at }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-brand bg-white text-brand rounded-lg text-xs font-medium hover:bg-brand/5 transition-colors" wire:click="seleccionar({{ $p->id_allocation_proposal }})">
                                                👁️ Ver
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-12 text-slate-400">
                                            <div class="text-5xl mb-2">📥</div>
                                            <p>No hay propuestas para los filtros seleccionados.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mt-4">
                        <span>ℹ️</span>
                        <small>
                            Estas propuestas se generan en base a histórico (persona-día / máquina-día). Podés confirmar y aplicar para cargar asignaciones del lote.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ !$mostrar_listado ? '' : 'hidden' }}">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">📋 Detalle de Propuesta</h5>
                    <div class="flex gap-2">
                        <button class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="volver">
                            ← Volver
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    @if(!$selectedProposal)
                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                            <small>Seleccione una propuesta desde la pestaña "Propuestas".</small>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="border border-slate-200 rounded-lg p-4 bg-white">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="font-semibold">Propuesta #{{ $selectedProposal->id_allocation_proposal }}</div>
                                        <div class="text-slate-500 text-xs">Lote #{{ $selectedProposal->id_lote }} - {{ $selectedProposal->lote->ubicacion ?? '' }}</div>
                                    </div>
                                    <div>
                                        @php
                                            $badge = match($selectedProposal->status) {
                                                'applied' => 'bg-emerald-100 text-emerald-700',
                                                'confirmed' => 'bg-brand/10 text-brand',
                                                default => 'bg-amber-100 text-amber-700'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $selectedProposal->status }}</span>
                                    </div>
                                </div>

                                <hr class="border-slate-200 my-2">
                                <div class="text-sm">
                                    <div>Tarea: <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $selectedProposal->tipo_tarea }}</span></div>
                                    <div>Especie: <strong>{{ $selectedProposal->especie ?? 'N/A' }}</strong></div>
                                    <div>Superficie: <strong>{{ $selectedProposal->superficie_ha ?? 'N/A' }}</strong> ha</div>
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-lg p-4 bg-white">
                                <div class="font-semibold mb-2">Estimación</div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>Persona-día</div>
                                    <div class="text-right"><strong>{{ $selectedProposal->estimated_person_days ?? 'N/A' }}</strong></div>
                                    <div>Máquina-día</div>
                                    <div class="text-right"><strong>{{ $selectedProposal->estimated_machine_days ?? 'N/A' }}</strong></div>
                                    <div>Duración (días)</div>
                                    <div class="text-right"><strong>{{ $selectedProposal->estimated_duration_days ?? 'N/A' }}</strong></div>
                                </div>
                                <div class="text-slate-400 text-xs mt-2">
                                    Fallback: {{ $selectedProposal->meta['fallback_used'] ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-xl shadow-sm border border-slate-300 overflow-hidden">
                                <div class="bg-slate-600 text-white px-6 py-4 flex justify-between items-center">
                                    <strong>👥 Empleados sugeridos</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">{{ $selectedProposal->proposedEmployees->count() }}</span>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedEmployees->isEmpty())
                                        <div class="text-slate-400 text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            @foreach($selectedProposal->proposedEmployees as $row)
                                                <div class="flex items-center gap-2 py-1" wire:key="emp-{{ $row->id_allocation_proposal_employee }}">
                                                    <input
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                        type="checkbox"
                                                        id="ape-{{ $row->id_allocation_proposal_employee }}"
                                                        wire:model.live="employeeSelected.{{ $row->id_allocation_proposal_employee }}"
                                                        @if($guardando) disabled @endif
                                                    >
                                                    <label class="text-sm text-slate-700" for="ape-{{ $row->id_allocation_proposal_employee }}">
                                                        {{ $row->empleado->apellido ?? '' }}, {{ $row->empleado->nombre ?? '' }}
                                                        <small class="text-slate-500">- {{ $row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? 'Sin rol') }}</small>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700 ml-1">score: {{ $row->score ?? 'N/A' }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                                <div class="bg-brand text-white px-6 py-4 flex justify-between items-center">
                                    <strong>🚛 Maquinarias sugeridas</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">{{ $selectedProposal->proposedMaquinarias->count() }}</span>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedMaquinarias->isEmpty())
                                        <div class="text-slate-400 text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            @foreach($selectedProposal->proposedMaquinarias as $row)
                                                <div class="flex items-center gap-2 py-1" wire:key="maq-{{ $row->id_allocation_proposal_maquinaria }}">
                                                    <input
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                        type="checkbox"
                                                        id="apm-{{ $row->id_allocation_proposal_maquinaria }}"
                                                        wire:model.live="maquinariaSelected.{{ $row->id_allocation_proposal_maquinaria }}"
                                                        @if($guardando) disabled @endif
                                                    >
                                                    <label class="text-sm text-slate-700" for="apm-{{ $row->id_allocation_proposal_maquinaria }}">
                                                        {{ $row->maquinaria->modelo ?? '' }}
                                                        <small class="text-slate-500">- {{ $row->tipo_sugerido ?? ($row->maquinaria->tipoMaquinaria->nombre ?? 'N/A') }}</small>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700 ml-1">score: {{ $row->score ?? 'N/A' }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-emerald-300 overflow-hidden">
                                <div class="bg-emerald-600 text-white px-6 py-4 flex justify-between items-center">
                                    <strong>📦 Insumos (semana 1)</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">{{ $selectedProposal->proposedInsumos->count() }}</span>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedInsumos->isEmpty())
                                        <div class="text-slate-400 text-sm">Sin sugerencias.</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            @foreach($selectedProposal->proposedInsumos as $row)
                                                <div class="flex items-start gap-2 py-1" wire:key="insumo-{{ $row->id_allocation_proposal_insumo }}">
                                                    <div class="pt-0.5">
                                                        <input
                                                            class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                            type="checkbox"
                                                            id="api-{{ $row->id_allocation_proposal_insumo }}"
                                                            wire:model.live="insumoSelected.{{ $row->id_allocation_proposal_insumo }}"
                                                            @if($guardando) disabled @endif
                                                        >
                                                    </div>
                                                    <label class="w-full text-sm" for="api-{{ $row->id_allocation_proposal_insumo }}">
                                                        <div class="font-semibold text-slate-800">{{ $row->insumo->nombre ?? '' }}</div>
                                                        <div class="text-xs text-slate-500">
                                                            {{ $row->insumo->unidadMedida->nombre ?? '' }}
                                                            @if(!is_null($row->cantidad_semana_1))
                                                                · cant. semana 1: <strong>{{ $row->cantidad_semana_1 }}</strong>
                                                            @else
                                                                · cant. semana 1: <strong>N/A</strong>
                                                            @endif

                                                            @if(!is_null($row->costo_estimado_semana_1))
                                                                · costo: <strong>${{ $row->costo_estimado_semana_1 }}</strong>
                                                            @else
                                                                · costo: <strong>N/A</strong>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-6">
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="guardarSeleccion" @if($guardando) disabled @endif>
                                💾 Guardar selección
                            </button>

                            <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="confirmar" @if($guardando) disabled @endif>
                                ✓ Confirmar
                            </button>

                            <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="aplicar" @if($guardando) disabled @endif>
                                📥 Aplicar al lote
                            </button>
                            @endcanany
                        </div>

                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm mt-4">
                            <span>⚠</span>
                            <small>
                                "Aplicar" reemplaza las asignaciones actuales del lote por la selección de esta propuesta.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
