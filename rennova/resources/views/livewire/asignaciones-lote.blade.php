<div class="container py-4">
    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="asignacionesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $mostrar_historial ? 'active' : '' }}" 
                    id="historial-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#historial-asignaciones" 
                    type="button" 
                    role="tab"
                    wire:click="$set('mostrar_historial', true)">
                <i class="bi bi-list-ul"></i> Historial de Asignaciones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !$mostrar_historial ? 'active' : '' }}" 
                    id="formulario-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#formulario-asignacion" 
                    type="button" 
                    role="tab"
                    wire:click="$set('mostrar_historial', false)">
                <i class="bi bi-{{ $modo === 'editar' ? 'pencil-square' : 'plus-circle' }}"></i> 
                {{ $modo === 'editar' ? 'Modificar Asignación' : 'Nueva Asignación' }}
            </button>
        </li>
    </ul>

    <div class="tab-content" id="asignacionesTabContent">
        <!-- Pestaña 1: Historial de Asignaciones -->
        <div class="tab-pane fade {{ $mostrar_historial ? 'show active' : '' }}" 
             id="historial-asignaciones" 
             role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Historial de Asignaciones por Lote</h5>
                    <button class="btn btn-primary btn-sm" wire:click="nuevaAsignacion">
                        <i class="bi bi-plus-circle"></i> Nueva Asignación
                    </button>
                </div>
                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Lote</th>
                                    <th>Estado</th>
                                    <th>Empleados Asignados</th>
                                    <th>Maquinarias Asignadas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historial as $lote)
                                    <tr>
                                        <td>
                                            <strong>Lote #{{ $lote->id_lote }}</strong><br>
                                            <small class="text-muted">{{ $lote->ubicacion }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $lote->estado === 'activo' ? 'success' : ($lote->estado === 'terminado' ? 'secondary' : 'warning') }}">
                                                {{ ucfirst($lote->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($lote->empleados->count() > 0)
                                                <small>
                                                    @foreach($lote->empleados as $emp)
                                                        <span class="badge bg-info me-1">{{ $emp->apellido }}</span>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-muted">Total: {{ $lote->empleados->count() }}</small>
                                            @else
                                                <span class="text-muted">Sin empleados</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lote->maquinarias->count() > 0)
                                                <small>
                                                    @foreach($lote->maquinarias as $maq)
                                                        <span class="badge bg-primary me-1">{{ $maq->modelo }}</span>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-muted">Total: {{ $lote->maquinarias->count() }}</small>
                                            @else
                                                <span class="text-muted">Sin maquinarias</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" 
                                                        wire:click="editarAsignacion({{ $lote->id_lote }})"
                                                        title="Modificar asignaciones">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if($lote->estado !== 'terminado')
                                                    <button class="btn btn-outline-warning" 
                                                            wire:click="liberar({{ $lote->id_lote }})"
                                                            onclick="return confirm('¿Marcar lote como terminado y liberar recursos?')"
                                                            title="Finalizar y liberar">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline-danger" 
                                                        wire:click="eliminarAsignacion({{ $lote->id_lote }})"
                                                        onclick="return confirm('¿Eliminar todas las asignaciones de este lote?')"
                                                        title="Eliminar asignaciones">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay asignaciones registradas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Formulario de Asignación -->
        <div class="tab-pane fade {{ !$mostrar_historial ? 'show active' : '' }}" 
             id="formulario-asignacion" 
             role="tabpanel">
            <div class="card shadow mb-4" id="formulario-asignacion-card">
                <div class="card-header bg-light d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $modo === 'editar' ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $modo === 'editar' ? 'Modificar Asignación' : 'Nueva Asignación' }}
                    </h5>
                </div>
                <div class="card-body">
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

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_lote') is-invalid @enderror" wire:model.live="id_lote">
                                <option value="">Seleccione un lote</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Primero seleccione el Lote para ver y editar sus asignaciones.</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-people-fill"></i> Empleados asignados</strong>
                                    @if($id_lote)
                                        <span class="badge bg-light text-dark">{{ count($empleados_seleccionados) }} seleccionados</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($id_lote)
                                        <div class="mb-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Buscar empleado..."
                                                   wire:model.live="busqueda_empleado">
                                        </div>
                                        <div style="max-height: 300px; overflow-y: auto;" class="border rounded p-2">
                                            @forelse($this->empleadosFiltrados as $emp)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           value="{{ $emp->id_empleado }}" 
                                                           id="emp-{{ $emp->id_empleado }}" 
                                                           wire:model.live="empleados_seleccionados">
                                                    <label class="form-check-label w-100" for="emp-{{ $emp->id_empleado }}">
                                                        {{ $emp->apellido }}, {{ $emp->nombre }}
                                                        <small class="text-muted">- {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <small class="text-muted">No se encontraron empleados.</small>
                                            @endforelse
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle"></i> Seleccione todos los empleados que trabajarán en este lote.
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <small>Seleccione un Lote para habilitar esta sección.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-truck"></i> Maquinarias asignadas</strong>
                                    @if($id_lote)
                                        <span class="badge bg-light text-dark">{{ count($maquinarias_seleccionadas) }} seleccionadas</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($id_lote)
                                        <div class="mb-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Buscar maquinaria..."
                                                   wire:model.live="busqueda_maquinaria">
                                        </div>
                                        <div style="max-height: 300px; overflow-y: auto;" class="border rounded p-2">
                                            @forelse($this->maquinariasFiltrada as $maq)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           value="{{ $maq->id_maquinaria }}" 
                                                           id="maq-{{ $maq->id_maquinaria }}" 
                                                           wire:model.live="maquinarias_seleccionadas">
                                                    <label class="form-check-label w-100" for="maq-{{ $maq->id_maquinaria }}">
                                                        {{ $maq->modelo }}
                                                        <small class="text-muted">- {{ $maq->estado }} - {{ $maq->tipoMaquinaria->nombre ?? 'N/A' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <small class="text-muted">No se encontraron maquinarias.</small>
                                            @endforelse
                                        </div>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small>
                                                <i class="bi bi-info-circle"></i> Si solo hay una maquinaria asignada al lote, se preseleccionará en el Parte Diario.
                                            </small>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <small>Seleccione un Lote para habilitar esta sección.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success" 
                                wire:click="guardar" 
                                wire:loading.attr="disabled" 
                                @disabled(!$id_lote)>
                            <i class="bi bi-save"></i> Guardar asignaciones
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelar">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <div wire:loading wire:target="guardar" class="text-muted align-self-center">
                            <i class="bi bi-arrow-repeat"></i> Guardando...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            document.getElementById('formulario-asignacion-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
