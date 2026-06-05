<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><i class="bi bi-file-earmark-text"></i> Auditorías del Sistema</h1>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <strong><i class="bi bi-clock-history"></i> Registro de Cambios</strong>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-light" wire:click="toggleFiltros" aria-controls="filtrosAuditoria" aria-expanded="{{ $mostrarFiltros ? 'true' : 'false' }}">
                        <i class="bi bi-funnel"></i> Filtros
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros Colapsables -->
            @if($mostrarFiltros)
            <div id="filtrosAuditoria">
                <div class="row g-3 mb-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Buscar</label>
                        <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control" placeholder="URL, IP o tag...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Modelo</label>
                        <select wire:model.live="filtroModelo" class="form-select">
                            <option value="">Todos los modelos</option>
                            @foreach($modelos as $modelo)
                                <option value="{{ $modelo['value'] }}" wire:key="option-{{ $modelo['value'] }}">{{ $modelo['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Evento</label>
                        <select wire:model.live="filtroEvento" class="form-select">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Usuario</label>
                        <select wire:model.live="filtroUsuario" class="form-select">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario['id'] }}" wire:key="option-{{ $usuario['id'] }}">{{ $usuario['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Desde</label>
                        <input type="date" wire:model.live="filtroFechaDesde" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Hasta</label>
                        <input type="date" wire:model.live="filtroFechaHasta" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" wire:click="limpiarFiltros" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tabla de Auditorías -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Modelo / Registro</th>
                            <th>Evento</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                            <tr wire:key="row-{{ $auditoria->id }}">
                                <td><span class="badge bg-secondary">#{{ $auditoria->id }}</span></td>
                                <td>
                                    <strong>{{ class_basename($auditoria->auditable_type) }}</strong><br>
                                    <small class="text-muted">ID: {{ $auditoria->auditable_id }}</small>
                                </td>
                                <td>
                                    @if($auditoria->event === 'created')
                                        <span class="badge bg-success"><i class="bi bi-plus-circle"></i> Creado</span>
                                    @elseif($auditoria->event === 'updated')
                                        <span class="badge bg-primary"><i class="bi bi-pencil"></i> Actualizado</span>
                                    @elseif($auditoria->event === 'deleted')
                                        <span class="badge bg-danger"><i class="bi bi-trash"></i> Eliminado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($auditoria->event) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($auditoria->user)
                                        <i class="bi bi-person-circle text-primary"></i> {{ $auditoria->user->name }}<br>
                                        <small class="text-muted">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted"><i class="bi bi-robot"></i> Sistema</span><br>
                                        <small class="text-muted">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $auditoria->created_at->format('d/m/Y H:i') }}<br>
                                    <small class="text-muted">{{ $auditoria->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalDetalle{{ $auditoria->id }}">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay auditorías registradas con los filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $auditorias->links() }}
        </div>
    </div>

    <!-- Modales de Detalles -->
    @foreach($auditorias as $auditoria)
        <div class="modal fade" id="modalDetalle{{ $auditoria->id }}" tabindex="-1" aria-hidden="true" wire:key="modal-{{ $auditoria->id }}">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle"></i> Detalles de Auditoría #{{ $auditoria->id }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Modelo:</strong> {{ class_basename($auditoria->auditable_type) }}</p>
                                <p class="mb-2"><strong>ID del Registro:</strong> #{{ $auditoria->auditable_id }}</p>
                                <p class="mb-0"><strong>Evento:</strong> 
                                    @if($auditoria->event === 'created')
                                        <span class="badge bg-success">Creado</span>
                                    @elseif($auditoria->event === 'updated')
                                        <span class="badge bg-primary">Actualizado</span>
                                    @elseif($auditoria->event === 'deleted')
                                        <span class="badge bg-danger">Eliminado</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Usuario:</strong> {{ $auditoria->user->name ?? 'Sistema' }}</p>
                                <p class="mb-2"><strong>IP:</strong> {{ $auditoria->ip_address ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Fecha:</strong> {{ $auditoria->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>

                        @if($auditoria->url)
                            <div class="mb-3">
                                <strong>URL:</strong> <code class="d-block bg-light p-2 rounded">{{ $auditoria->url }}</code>
                            </div>
                        @endif

                        @if($auditoria->event === 'updated' && $auditoria->old_values && $auditoria->new_values)
                            <h6 class="mb-3"><i class="bi bi-arrow-left-right"></i> Cambios Realizados</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Campo</th>
                                            <th style="width: 35%;">Valor Anterior</th>
                                            <th style="width: 35%;">Valor Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($auditoria->new_values as $campo => $valorNuevo)
                                            @if(isset($auditoria->old_values[$campo]) && $auditoria->old_values[$campo] != $valorNuevo)
                                                <tr wire:key="field-{{ $campo }}">
                                                    <td><strong>{{ $campo }}</strong></td>
                                                    <td>
                                                        <div class="text-break">
                                                            {{ is_array($auditoria->old_values[$campo]) ? json_encode($auditoria->old_values[$campo], JSON_UNESCAPED_UNICODE) : ($auditoria->old_values[$campo] ?? 'null') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-break">
                                                            {{ is_array($valorNuevo) ? json_encode($valorNuevo, JSON_UNESCAPED_UNICODE) : ($valorNuevo ?? 'null') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($auditoria->event === 'created' && $auditoria->new_values)
                            <h6 class="mb-3"><i class="bi bi-plus-circle"></i> Datos Creados</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($auditoria->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @elseif($auditoria->event === 'deleted' && $auditoria->old_values)
                            <h6 class="mb-3"><i class="bi bi-trash"></i> Datos Eliminados</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($auditoria->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
