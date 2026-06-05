{{-- Detalle expandible de una propuesta de asignación --}}
@props(['rec'])

@if($editProposalId === $rec->id_allocation_proposal)
    <!-- MODO EDICIÓN -->
    <div class="space-y-4">
        <!-- Sección Estimaciones (lectura) -->
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
            <div>Persona-día estimado: <span class="font-semibold">{{ $rec->estimated_person_days ?? 'N/A' }}</span></div>
            <div>Máquina-día estimado: <span class="font-semibold">{{ $rec->estimated_machine_days ?? 'N/A' }}</span></div>
            <div>Duración estimada: <span class="font-semibold">{{ $rec->estimated_duration_days ?? 'N/A' }}</span></div>
        </div>

        <!-- Sección Empleados Editable -->
        <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3">
            <div class="mb-3 text-xs font-semibold text-blue-700">✎ Empleados sugeridos (editable)</div>
            @if(empty($rec->proposedEmployees))
                <div class="text-slate-500">Sin empleados disponibles.</div>
            @else
                <div class="space-y-2">
                    @foreach($rec->proposedEmployees as $idx => $empRow)
                        <div class="flex items-center gap-3 rounded border border-blue-100 bg-white p-2">
                            <input 
                                type="checkbox" 
                                wire:model.live="editingProposals.{{ $rec->id_allocation_proposal }}.employees.{{ $idx }}.selected"
                                class="h-4 w-4 rounded border-slate-300"
                            />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-700">
                                    {{ $empRow['empleado']['apellido'] ?? '' }} {{ $empRow['empleado']['nombre'] ?? '' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $empRow['empleado']['rolLaboral']['nombre'] ?? $empRow['rol_sugerido'] ?? 'Rol' }}
                                </div>
                            </div>
                            @if($empRow['selected'] ?? false)
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                    ✅  Seleccionado
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sección Maquinarias Editable -->
        <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
            <div class="mb-3 text-xs font-semibold text-purple-700">✎ Maquinarias sugeridas (editable)</div>
            @if(empty($rec->proposedMaquinarias))
                <div class="text-slate-500">Sin maquinarias disponibles.</div>
            @else
                <div class="space-y-2">
                    @foreach($rec->proposedMaquinarias as $idx => $maqRow)
                        <div class="flex items-center gap-3 rounded border border-purple-100 bg-white p-2">
                            <input 
                                type="checkbox" 
                                wire:model.live="editProposedMaquinarias.{{ $idx }}.selected"
                                class="h-4 w-4 rounded border-slate-300"
                            />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-700">
                                    {{ $maqRow['maquinaria']['modelo'] ?? 'Maquinaria' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $maqRow['maquinaria']['tipoMaquinaria']['nombre'] ?? $maqRow['tipo_sugerido'] ?? 'Tipo' }}
                                </div>
                            </div>
                            @if($maqRow['selected'] ?? false)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700">
                                    ✅  Seleccionada
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sección Insumos Editable -->
        <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
            <div class="mb-3 text-xs font-semibold text-green-700">✎ Insumos semana 1 (editable)</div>
            @if(empty($rec->proposedInsumos))
                <div class="text-slate-500">Sin insumos disponibles.</div>
            @else
                <div class="space-y-2">
                    @foreach($rec->proposedInsumos as $idx => $insumoRow)
                        <div class="flex items-center gap-3 rounded border border-green-100 bg-white p-2">
                            <input 
                                type="checkbox" 
                                wire:model.live="editingProposals.{{ $rec->id_allocation_proposal }}.insumos.{{ $idx }}.selected"
                                class="h-4 w-4 rounded border-slate-300"
                            />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-700">
                                    {{ $insumoRow['insumo']['nombre'] ?? 'Insumo' }}
                                </div>
                            </div>
                            @if($insumoRow['selected'] ?? false)
                                <input 
                                    type="number" 
                                    wire:model.defer="editingProposals.{{ $rec->id_allocation_proposal }}.insumos.{{ $idx }}.cantidad_semana_1"
                                    placeholder="Cant."
                                    step="1"
                                    min="1"
                                    class="w-20 rounded border border-slate-300 px-2 py-1 text-xs"
                                />
                                <span class="text-xs text-slate-500">
                                    {{ $insumoRow['insumo']['unidadMedida']['nombre'] ?? '' }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Botones de acción -->
        <div class="flex gap-2 border-t border-slate-200 pt-3">
            <button 
                wire:click="saveEdit({{ $rec->id_allocation_proposal }})"
                class="rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
            >
                Guardar
            </button>
            <button 
                wire:click="cancelEdit"
                class="rounded bg-slate-400 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-500"
            >
                Cancelar
            </button>
        </div>
    </div>
@else
    <!-- MODO LECTURA -->
    @if($rec->status === 'applied')
        <!-- Vista cuando está APLICADA -->
        <div class="space-y-4">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                <div class="mb-2 text-xs font-semibold text-blue-700">✅ Asignación confirmada</div>
                <div class="text-xs text-slate-600 mb-2">
                    Confirmada: {{ $rec->confirmed_at ? \Carbon\Carbon::parse($rec->confirmed_at)->format('d/m/Y H:i') : 'N/A' }}
                </div>
                <div class="text-xs text-slate-600">
                    Aplicada: {{ $rec->applied_at ? \Carbon\Carbon::parse($rec->applied_at)->format('d/m/Y H:i') : 'N/A' }}
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
                    <div class="text-xs">Persona-día: <span class="font-semibold">{{ $rec->estimated_person_days ?? 'N/A' }}</span></div>
                    <div class="text-xs">Máquina-día: <span class="font-semibold">{{ $rec->estimated_machine_days ?? 'N/A' }}</span></div>
                    <div class="text-xs">Duración: <span class="font-semibold">{{ $rec->estimated_duration_days ?? 'N/A' }}</span> días</div>
                </div>
            </div>

            <!-- Empleados Seleccionados -->
            <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3">
                <div class="mb-2 text-xs font-semibold text-blue-700">👤 Empleados asignados</div>
                @php
                    $empleadosSeleccionados = collect($rec->proposedEmployees ?? [])->where('selected', true);
                @endphp
                @if($empleadosSeleccionados->isEmpty())
                    <div class="text-xs text-slate-500">Sin empleados asignados.</div>
                @else
                    <div class="space-y-1.5">
                        @foreach($empleadosSeleccionados as $emp)
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700">{{ $emp->empleado->apellido ?? '' }} {{ $emp->empleado->nombre ?? '' }}</span>
                                <span class="text-slate-500">{{ $emp->empleado->rolLaboral->nombre ?? 'Rol' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Maquinarias Seleccionadas -->
            <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
                <div class="mb-2 text-xs font-semibold text-purple-700">🏗️ Maquinarias asignadas</div>
                @php
                    $maquinariasSeleccionadas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                @endphp
                @if($maquinariasSeleccionadas->isEmpty())
                    <div class="text-xs text-slate-500">Sin maquinarias asignadas.</div>
                @else
                    <div class="space-y-1.5">
                        @foreach($maquinariasSeleccionadas as $maq)
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700">{{ $maq->maquinaria->modelo ?? 'Maquinaria' }}</span>
                                <span class="text-slate-500">{{ $maq->maquinaria->tipoMaquinaria->nombre ?? 'Tipo' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Insumos Asignados -->
            <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
                <div class="mb-2 text-xs font-semibold text-green-700">📦 Insumos semana 1 (asignados)</div>
                @php
                    $insumosSeleccionados = collect($rec->proposedInsumos ?? [])->where('selected', true);
                @endphp
                @if($insumosSeleccionados->isEmpty())
                    <div class="text-xs text-slate-500">Sin insumos asignados.</div>
                @else
                    <div class="space-y-1.5">
                        @foreach($insumosSeleccionados as $insumo)
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700">{{ $insumo->insumo->nombre ?? 'Insumo' }}</span>
                                <span class="text-slate-500">{{ $insumo->cantidad_semana_1 ?? 'N/A' }} {{ $insumo->insumo->unidadMedida->nombre ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Vista cuando NO está aplicada (borrador o confirmada) -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
                <div>Persona-día estimado: <span class="font-semibold">{{ $rec->estimated_person_days ?? 'N/A' }}</span></div>
                <div>Máquina-día estimado: <span class="font-semibold">{{ $rec->estimated_machine_days ?? 'N/A' }}</span></div>
                <div>Duración estimada: <span class="font-semibold">{{ $rec->estimated_duration_days ?? 'N/A' }}</span></div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Insumos semana 1</div>
                @if(empty($rec->proposedInsumos))
                    <div class="text-slate-500">Sin insumos sugeridos.</div>
                @else
                    <div class="space-y-2">
                        @foreach($rec->proposedInsumos as $insumoRow)
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    {{ $insumoRow->insumo->nombre ?? 'Insumo' }}
                                </div>
                                <div class="text-slate-500">
                                    {{ $insumoRow->cantidad_semana_1 ?? 'N/A' }}
                                    {{ $insumoRow->insumo->unidadMedida->nombre ?? '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Empleados sugeridos (libres)</div>
                @php
                    $empleadosSugeridos = collect($rec->proposedEmployees ?? [])->where('selected', true);
                @endphp
                @if($empleadosSugeridos->isEmpty())
                    <div class="text-slate-500">Sin empleados sugeridos.</div>
                @else
                    <div class="space-y-2">
                        @foreach($empleadosSugeridos as $empRow)
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    {{ $empRow->empleado->apellido ?? '' }} {{ $empRow->empleado->nombre ?? '' }}
                                </div>
                                <div class="text-slate-500">
                                    {{ $empRow->empleado->rolLaboral->nombre ?? $empRow->rol_sugerido ?? 'Rol' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Maquinarias sugeridas (libres)</div>
                @php
                    $maquinariasSugeridas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                @endphp
                @if($maquinariasSugeridas->isEmpty())
                    <div class="text-slate-500">Sin maquinarias sugeridas.</div>
                @else
                    <div class="space-y-2">
                        @foreach($maquinariasSugeridas as $maqRow)
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    {{ $maqRow->maquinaria->modelo ?? 'Maquinaria' }}
                                </div>
                                <div class="text-slate-500">
                                    {{ $maqRow->maquinaria->tipoMaquinaria->nombre ?? $maqRow->tipo_sugerido ?? 'Tipo' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
