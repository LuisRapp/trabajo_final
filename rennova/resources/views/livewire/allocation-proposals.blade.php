<div class="container py-4">
    @if(!empty($loteId))
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0"><i class="bi bi-magic"></i> Recomendaciones del Lote #{{ $loteId }}</h4>
                <div class="text-muted small">Al pasar el lote a <strong>en proceso</strong> se generan estas propuestas.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('lotes.index') }}">
                    <i class="bi bi-arrow-left"></i> Volver a Lotes
                </a>
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <button class="btn btn-outline-primary" wire:click="generarAhora" @if($guardando) disabled @endif>
                    <i class="bi bi-gear"></i> Generar ahora
                </button>
                @endcanany
                @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                <button class="btn btn-outline-secondary" wire:click="refreshProposals" @if($guardando) disabled @endif>
                    <i class="bi bi-arrow-clockwise"></i> Refrescar
                </button>
                @endcanany
            </div>
        </div>
    @endif

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link {{ $mostrar_listado ? 'active' : '' }}"
                type="button"
                role="tab"
                wire:click="$set('mostrar_listado', true)"
            >
                <i class="bi bi-lightning-charge"></i> Propuestas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link {{ !$mostrar_listado ? 'active' : '' }}"
                type="button"
                role="tab"
                wire:click="$set('mostrar_listado', false)"
                @if(!$selected_proposal_id) disabled @endif
            >
                <i class="bi bi-clipboard-check"></i> Detalle / Confirmar
            </button>
        </li>
    </ul>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="tab-content">
        <div class="tab-pane fade {{ $mostrar_listado ? 'show active' : '' }}">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-magic"></i> Propuestas Automáticas</h5>
                    <div class="d-flex gap-2">
                        @if(empty($loteId))
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <button class="btn btn-outline-secondary btn-sm" wire:click="refreshProposals" @if($guardando) disabled @endif>
                                <i class="bi bi-arrow-clockwise"></i> Refrescar
                            </button>
                            @endcanany
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if(empty($loteId))
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote</label>
                            <select class="form-select" wire:model.live="filter_lote_id">
                                <option value="">Todos</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}" wire:key="option-{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Estado</label>
                            <select class="form-select" wire:model.live="filter_status">
                                <option value="">Todos</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="applied">Applied</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="text-muted small">
                                Mostrando {{ is_countable($proposals) ? count($proposals) : 0 }} propuestas
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
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
                                            <div class="text-muted small">{{ $p->lote->ubicacion ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $p->tipo_tarea }}</span>
                                            @if($p->id_lote_tarea)
                                                <span class="badge bg-light text-dark">Tarea #{{ $p->id_lote_tarea }}</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <div>Persona-día: <strong>{{ $p->estimated_person_days ?? 'N/A' }}</strong></div>
                                            <div>Máquina-día: <strong>{{ $p->estimated_machine_days ?? 'N/A' }}</strong></div>
                                            <div>Duración: <strong>{{ $p->estimated_duration_days ?? 'N/A' }}</strong></div>
                                        </td>
                                        <td>
                                            @php
                                                $badge = match($p->status) {
                                                    'applied' => 'success',
                                                    'confirmed' => 'primary',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ $p->status }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $p->created_at }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-sm" wire:click="seleccionar({{ $p->id_allocation_proposal }})">
                                                <i class="bi bi-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay propuestas para los filtros seleccionados.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Estas propuestas se generan en base a histórico (persona-día / máquina-día). Podés confirmar y aplicar para cargar asignaciones del lote.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ !$mostrar_listado ? 'show active' : '' }}">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Detalle de Propuesta</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" wire:click="volver">
                            <i class="bi bi-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if(!$selectedProposal)
                        <div class="alert alert-warning mb-0">
                            <small>Seleccione una propuesta desde la pestaña "Propuestas".</small>
                        </div>
                    @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-semibold">Propuesta #{{ $selectedProposal->id_allocation_proposal }}</div>
                                            <div class="text-muted small">Lote #{{ $selectedProposal->id_lote }} - {{ $selectedProposal->lote->ubicacion ?? '' }}</div>
                                        </div>
                                        <div>
                                            @php
                                                $badge = match($selectedProposal->status) {
                                                    'applied' => 'success',
                                                    'confirmed' => 'primary',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ $selectedProposal->status }}</span>
                                        </div>
                                    </div>

                                    <hr class="my-2">
                                    <div class="small">
                                        <div>Tarea: <span class="badge bg-secondary">{{ $selectedProposal->tipo_tarea }}</span></div>
                                        <div>Especie: <strong>{{ $selectedProposal->especie ?? 'N/A' }}</strong></div>
                                        <div>Superficie: <strong>{{ $selectedProposal->superficie_ha ?? 'N/A' }}</strong> ha</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <div class="fw-semibold mb-2">Estimación</div>
                                    <div class="row small">
                                        <div class="col-6">Persona-día</div>
                                        <div class="col-6 text-end"><strong>{{ $selectedProposal->estimated_person_days ?? 'N/A' }}</strong></div>
                                        <div class="col-6">Máquina-día</div>
                                        <div class="col-6 text-end"><strong>{{ $selectedProposal->estimated_machine_days ?? 'N/A' }}</strong></div>
                                        <div class="col-6">Duración (días)</div>
                                        <div class="col-6 text-end"><strong>{{ $selectedProposal->estimated_duration_days ?? 'N/A' }}</strong></div>
                                    </div>
                                    <div class="text-muted small mt-2">
                                        Fallback: {{ $selectedProposal->meta['fallback_used'] ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-secondary h-100">
                                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-people-fill"></i> Empleados sugeridos</strong>
                                        <span class="badge bg-light text-dark">{{ $selectedProposal->proposedEmployees->count() }}</span>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedProposal->proposedEmployees->isEmpty())
                                            <div class="text-muted small">Sin sugerencias (falta histórico/pivotes).</div>
                                        @else
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                @foreach($selectedProposal->proposedEmployees as $row)
                                                    <div class="form-check" wire:key="emp-{{ $row->id_allocation_proposal_employee }}">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="ape-{{ $row->id_allocation_proposal_employee }}"
                                                            wire:model.live="employeeSelected.{{ $row->id_allocation_proposal_employee }}"
                                                            @if($guardando) disabled @endif
                                                        >
                                                        <label class="form-check-label w-100" for="ape-{{ $row->id_allocation_proposal_employee }}">
                                                            {{ $row->empleado->apellido ?? '' }}, {{ $row->empleado->nombre ?? '' }}
                                                            <small class="text-muted">- {{ $row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? 'Sin rol') }}</small>
                                                            <span class="badge bg-light text-dark ms-1">score: {{ $row->score ?? 'N/A' }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-primary h-100">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-truck"></i> Maquinarias sugeridas</strong>
                                        <span class="badge bg-light text-dark">{{ $selectedProposal->proposedMaquinarias->count() }}</span>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedProposal->proposedMaquinarias->isEmpty())
                                            <div class="text-muted small">Sin sugerencias (falta histórico/pivotes).</div>
                                        @else
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                @foreach($selectedProposal->proposedMaquinarias as $row)
                                                    <div class="form-check" wire:key="maq-{{ $row->id_allocation_proposal_maquinaria }}">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="apm-{{ $row->id_allocation_proposal_maquinaria }}"
                                                            wire:model.live="maquinariaSelected.{{ $row->id_allocation_proposal_maquinaria }}"
                                                            @if($guardando) disabled @endif
                                                        >
                                                        <label class="form-check-label w-100" for="apm-{{ $row->id_allocation_proposal_maquinaria }}">
                                                            {{ $row->maquinaria->modelo ?? '' }}
                                                            <small class="text-muted">- {{ $row->tipo_sugerido ?? ($row->maquinaria->tipoMaquinaria->nombre ?? 'N/A') }}</small>
                                                            <span class="badge bg-light text-dark ms-1">score: {{ $row->score ?? 'N/A' }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-box-seam"></i> Insumos (semana 1)</strong>
                                        <span class="badge bg-light text-dark">{{ $selectedProposal->proposedInsumos->count() }}</span>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedProposal->proposedInsumos->isEmpty())
                                            <div class="text-muted small">Sin sugerencias.</div>
                                        @else
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                @foreach($selectedProposal->proposedInsumos as $row)
                                                    <div class="d-flex align-items-start gap-2 py-1" wire:key="insumo-{{ $row->id_allocation_proposal_insumo }}">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                id="api-{{ $row->id_allocation_proposal_insumo }}"
                                                                wire:model.live="insumoSelected.{{ $row->id_allocation_proposal_insumo }}"
                                                                @if($guardando) disabled @endif
                                                            >
                                                        </div>
                                                        <label class="w-100" for="api-{{ $row->id_allocation_proposal_insumo }}">
                                                            <div class="fw-semibold">{{ $row->insumo->nombre ?? '' }}</div>
                                                            <div class="small text-muted">
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
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @canany(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])
                            <button class="btn btn-outline-secondary" wire:click="guardarSeleccion" @if($guardando) disabled @endif>
                                <i class="bi bi-save"></i> Guardar selección
                            </button>

                            <button class="btn btn-primary" wire:click="confirmar" @if($guardando) disabled @endif>
                                <i class="bi bi-check2-circle"></i> Confirmar
                            </button>

                            <button class="btn btn-success" wire:click="aplicar" @if($guardando) disabled @endif>
                                <i class="bi bi-box-arrow-in-down"></i> Aplicar al lote
                            </button>
                            @endcanany
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i>
                                "Aplicar" reemplaza las asignaciones actuales del lote por la selección de esta propuesta.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
