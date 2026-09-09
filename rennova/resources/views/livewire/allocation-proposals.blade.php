@php use App\Enums\EstadoPropuesta; @endphp
<div class="w-full">
    @if(!empty($loteId))
        <div class="flex flex-wrap justify-between items-center mb-4">
            <div>
                <h4 class="text-xl font-bold text-tinta flex items-center gap-2">
                    <flux:icon.sparkles class="size-5" />
                    Sugerencias de asignación — Lote #{{ $loteId }}
                </h4>
                <div class="text-tinta-suave text-sm">Al pasar el lote a <strong>en proceso</strong> se generan estas propuestas.</div>
            </div>
            <div class="flex gap-2">
                <a class="btn-secondary" href="{{ route('lotes.index') }}">
                    <flux:icon.arrow-left class="size-4" />
                    Volver a Lotes
                </a>
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <x-ui.button variant="secondary" icon="bolt" wire:click="generarAhora" :disabled="$guardando">
                    Generar ahora
                </x-ui.button>
                @endcanany
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <x-ui.button variant="ghost" icon="arrow-path" wire:click="refreshProposals" :disabled="$guardando">
                    Refrescar
                </x-ui.button>
                @endcanany
            </div>
        </div>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $mostrar_listado ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', true)"
        >
            <flux:icon.bolt class="size-4 inline mr-1" />
            Propuestas
        </button>
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ !$mostrar_listado ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', false)"
            @if(!$selected_proposal_id) disabled @endif
        >
            <flux:icon.clipboard-document-list class="size-4 inline mr-1" />
            Detalle / Confirmar
        </button>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-5">
            {{ session('message') }}
        </x-ui.alert>
    @endif
    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-5">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div>
        <div class="{{ $mostrar_listado ? '' : 'hidden' }}">
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                        <flux:icon.sparkles class="size-5" />
                        Propuestas Automáticas
                    </h5>
                    <div class="flex gap-2">
                        @if(empty($loteId))
                            <a class="btn-secondary" href="{{ route('asignaciones-lote.index') }}">
                                <flux:icon.arrow-left class="size-4" />
                                Volver a Asignación manual
                            </a>
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <x-ui.button variant="ghost" size="sm" icon="arrow-path" wire:click="refreshProposals" :disabled="$guardando">
                                Refrescar
                            </x-ui.button>
                            @endcanany
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    @if(empty($loteId))
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Lote</label>
                            <select class="form-input" wire:model.live="filter_lote_id">
                                <option value="">Todos</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}" wire:key="option-{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Estado</label>
                            <select class="form-input" wire:model.live="filter_status">
                                <option value="">Todos</option>
                                <option value="{{ EstadoPropuesta::DRAFT->value }}">{{ EstadoPropuesta::DRAFT->label() }}</option>
                                <option value="{{ EstadoPropuesta::CONFIRMED->value }}">{{ EstadoPropuesta::CONFIRMED->label() }}</option>
                                <option value="{{ EstadoPropuesta::APPLIED->value }}">{{ EstadoPropuesta::APPLIED->label() }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="text-tinta-suave text-sm">
                                Mostrando {{ is_countable($proposals) ? count($proposals) : 0 }} propuestas
                            </div>
                        </div>
                    </div>
                    @endif

                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lote</th>
                                    <th>Tarea</th>
                                    <th>Estimación</th>
                                    <th>Estado</th>
                                    <th>Creada</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proposals as $p)
                                    <tr wire:key="row-{{ $p->id_allocation_proposal }}">
                                        <td><strong>#{{ $p->id_allocation_proposal }}</strong></td>
                                        <td>
                                            <div><strong>Lote #{{ $p->id_lote }}</strong></div>
                                            <div class="text-tinta-suave text-xs">{{ $p->lote->ubicacion ?? '' }}</div>
                                        </td>
                                        <td>
                                            <x-ui.badge variant="neutral">{{ $p->tipo_tarea }}</x-ui.badge>
                                            @if($p->id_lote_tarea)
                                                <x-ui.badge variant="info">Tarea #{{ $p->id_lote_tarea }}</x-ui.badge>
                                            @endif
                                        </td>
                                        <td class="text-xs text-tinta-suave">
                                            <div>Persona-día: <strong>{{ $p->estimated_person_days ?? 'N/A' }}</strong></div>
                                            <div>Máquina-día: <strong>{{ $p->estimated_machine_days ?? 'N/A' }}</strong></div>
                                            <div>Duración: <strong>{{ $p->estimated_duration_days ?? 'N/A' }}</strong></div>
                                        </td>
                                        <td>
                                            @php
                                                $statusVariant = match($p->status) {
                                                    'applied' => 'success',
                                                    'confirmed' => 'info',
                                                    'closed' => 'neutral',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <x-ui.badge variant="{{ $statusVariant }}">{{ EstadoPropuesta::etiqueta($p->status) }}</x-ui.badge>
                                        </td>
                                        <td class="text-xs text-tinta-suave">{{ $p->created_at }}</td>
                                        <td class="text-center">
                                            <x-ui.button variant="ghost" size="sm" icon="eye" wire:click="seleccionar({{ $p->id_allocation_proposal }})" title="Ver" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-12 text-tinta-suave">
                                            No hay propuestas para los filtros seleccionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </x-ui.table-container>

                    <x-ui.alert variant="info" class="mt-4" dismissible="false">
                        Estas propuestas se generan en base a histórico (persona-día / máquina-día). Podés confirmar y aplicar para cargar asignaciones del lote.
                    </x-ui.alert>
                </div>
            </x-ui.card>
        </div>

        <div class="{{ !$mostrar_listado ? '' : 'hidden' }}">
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                        <flux:icon.clipboard-document-list class="size-5" />
                        Detalle de Propuesta
                    </h5>
                    <div class="flex gap-2">
                        <x-ui.button variant="secondary" icon="arrow-left" wire:click="volver">
                            Volver
                        </x-ui.button>
                    </div>
                </div>

                <div class="p-6">
                    @if(!$selectedProposal)
                        <x-ui.alert variant="warning" dismissible="false">
                            Seleccione una propuesta desde la pestaña "Propuestas".
                        </x-ui.alert>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <x-ui.card class="p-4">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="font-semibold text-tinta">Propuesta #{{ $selectedProposal->id_allocation_proposal }}</div>
                                        <div class="text-tinta-suave text-xs">Lote #{{ $selectedProposal->id_lote }} - {{ $selectedProposal->lote->ubicacion ?? '' }}</div>
                                    </div>
                                    <div>
                                        @php
                                            $statusVariant = match($selectedProposal->status) {
                                                'applied' => 'success',
                                                'confirmed' => 'info',
                                                'closed' => 'neutral',
                                                default => 'warning',
                                            };
                                        @endphp
                                        <x-ui.badge variant="{{ $statusVariant }}">{{ EstadoPropuesta::etiqueta($selectedProposal->status) }}</x-ui.badge>
                                    </div>
                                </div>

                                <hr class="border-arena my-2">
                                <div class="text-sm text-tinta-suave">
                                    <div>Tarea: <x-ui.badge variant="neutral">{{ $selectedProposal->tipo_tarea }}</x-ui.badge></div>
                                    <div>Especie: <strong class="text-tinta">{{ $selectedProposal->especie ?? 'N/A' }}</strong></div>
                                    <div>Superficie: <strong class="text-tinta">{{ $selectedProposal->superficie_ha ?? 'N/A' }}</strong> ha</div>
                                </div>
                            </x-ui.card>

                            <x-ui.card class="p-4">
                                <div class="font-semibold text-tinta mb-2">Estimación</div>
                                <div class="grid grid-cols-2 gap-2 text-sm text-tinta-suave">
                                    <div>Persona-día</div>
                                    <div class="text-right"><strong class="text-tinta">{{ $selectedProposal->estimated_person_days ?? 'N/A' }}</strong></div>
                                    <div>Máquina-día</div>
                                    <div class="text-right"><strong class="text-tinta">{{ $selectedProposal->estimated_machine_days ?? 'N/A' }}</strong></div>
                                    <div>Duración (días)</div>
                                    <div class="text-right"><strong class="text-tinta">{{ $selectedProposal->estimated_duration_days ?? 'N/A' }}</strong></div>
                                </div>
                                <div class="text-tinta-suave text-xs mt-2">
                                    Fallback: {{ $selectedProposal->meta['fallback_used'] ?? 'N/A' }}
                                </div>
                            </x-ui.card>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-ui.card class="overflow-hidden border-arena">
                                <div class="bg-tinta text-blanco px-6 py-4 flex justify-between items-center">
                                    <strong class="flex items-center gap-2">
                                        <flux:icon.users class="size-5" />
                                        Empleados sugeridos
                                    </strong>
                                    <x-ui.badge variant="neutral" class="bg-blanco/20 text-blanco">{{ $selectedProposal->proposedEmployees->count() }}</x-ui.badge>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedEmployees->isEmpty())
                                        <div class="text-tinta-suave text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-arena rounded-sm p-3">
                                            @foreach($selectedProposal->proposedEmployees as $row)
                                                <div class="flex items-center gap-2 py-1" wire:key="emp-{{ $row->id_allocation_proposal_employee }}">
                                                    <input
                                                        class="rounded border-arena text-pino focus:ring-pino"
                                                        type="checkbox"
                                                        id="ape-{{ $row->id_allocation_proposal_employee }}"
                                                        wire:model.live="employeeSelected.{{ $row->id_allocation_proposal_employee }}"
                                                        @if($guardando) disabled @endif
                                                    >
                                                    <label class="text-sm text-tinta" for="ape-{{ $row->id_allocation_proposal_employee }}">
                                                        {{ $row->empleado->apellido ?? '' }}, {{ $row->empleado->nombre ?? '' }}
                                                        <small class="text-tinta-suave">- {{ $row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? 'Sin rol') }}</small>
                                                        <x-ui.badge variant="neutral">score: {{ $row->score ?? 'N/A' }}</x-ui.badge>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </x-ui.card>

                            <x-ui.card class="overflow-hidden border-pino/30">
                                <div class="bg-pino text-blanco px-6 py-4 flex justify-between items-center">
                                    <strong class="flex items-center gap-2">
                                        <flux:icon.truck class="size-5" />
                                        Maquinarias sugeridas
                                    </strong>
                                    <x-ui.badge variant="neutral" class="bg-blanco/20 text-blanco">{{ $selectedProposal->proposedMaquinarias->count() }}</x-ui.badge>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedMaquinarias->isEmpty())
                                        <div class="text-tinta-suave text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-arena rounded-sm p-3">
                                            @foreach($selectedProposal->proposedMaquinarias as $row)
                                                <div class="flex items-center gap-2 py-1" wire:key="maq-{{ $row->id_allocation_proposal_maquinaria }}">
                                                    <input
                                                        class="rounded border-arena text-pino focus:ring-pino"
                                                        type="checkbox"
                                                        id="apm-{{ $row->id_allocation_proposal_maquinaria }}"
                                                        wire:model.live="maquinariaSelected.{{ $row->id_allocation_proposal_maquinaria }}"
                                                        @if($guardando) disabled @endif
                                                    >
                                                    <label class="text-sm text-tinta" for="apm-{{ $row->id_allocation_proposal_maquinaria }}">
                                                        {{ $row->maquinaria->modelo ?? '' }}
                                                        <small class="text-tinta-suave">- {{ $row->tipo_sugerido ?? ($row->maquinaria->tipoMaquinaria->nombre ?? 'N/A') }}</small>
                                                        <x-ui.badge variant="neutral">score: {{ $row->score ?? 'N/A' }}</x-ui.badge>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </x-ui.card>

                            <x-ui.card class="overflow-hidden border-musgo/30">
                                <div class="bg-musgo text-blanco px-6 py-4 flex justify-between items-center">
                                    <strong class="flex items-center gap-2">
                                        <flux:icon.cube class="size-5" />
                                        Insumos (semana 1)
                                    </strong>
                                    <x-ui.badge variant="neutral" class="bg-blanco/20 text-blanco">{{ $selectedProposal->proposedInsumos->count() }}</x-ui.badge>
                                </div>
                                <div class="p-6">
                                    @if($selectedProposal->proposedInsumos->isEmpty())
                                        <div class="text-tinta-suave text-sm">Sin sugerencias.</div>
                                    @else
                                        <div class="max-h-[320px] overflow-y-auto border border-arena rounded-sm p-3">
                                            @foreach($selectedProposal->proposedInsumos as $row)
                                                <div class="flex items-start gap-2 py-1" wire:key="insumo-{{ $row->id_allocation_proposal_insumo }}">
                                                    <div class="pt-0.5">
                                                        <input
                                                            class="rounded border-arena text-pino focus:ring-pino"
                                                            type="checkbox"
                                                            id="api-{{ $row->id_allocation_proposal_insumo }}"
                                                            wire:model.live="insumoSelected.{{ $row->id_allocation_proposal_insumo }}"
                                                            @if($guardando) disabled @endif
                                                        >
                                                    </div>
                                                    <label class="w-full text-sm" for="api-{{ $row->id_allocation_proposal_insumo }}">
                                                        <div class="font-semibold text-tinta">{{ $row->insumo->nombre ?? '' }}</div>
                                                        <div class="text-xs text-tinta-suave">
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
                            </x-ui.card>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-6">
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <x-ui.button variant="secondary" icon="check" wire:click="guardarSeleccion" :disabled="$guardando">
                                Guardar selección
                            </x-ui.button>

                            <x-ui.button variant="primary" icon="check-circle" wire:click="confirmar" :disabled="$guardando">
                                Confirmar
                            </x-ui.button>

                            <x-ui.button variant="primary" icon="archive-box-arrow-down" wire:click="aplicar" :disabled="$guardando">
                                Aplicar al lote
                            </x-ui.button>
                            @endcanany
                        </div>

                        <x-ui.alert variant="warning" class="mt-4" dismissible="false">
                            "Aplicar" reemplaza las asignaciones actuales del lote por la selección de esta propuesta.
                        </x-ui.alert>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
