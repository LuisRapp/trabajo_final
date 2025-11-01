<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Mantenimientos</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="mantenimientosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-mantenimiento" type="button" role="tab" aria-controls="nuevo-mantenimiento" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-mantenimientos" type="button" role="tab" aria-controls="listado-mantenimientos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Mantenimientos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="mantenimientosTabContent">
        <!-- Pestaña 1: Nuevo Mantenimiento (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-mantenimiento" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $mantenimiento_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $mantenimiento_id ? 'Editar Mantenimiento' : 'Nuevo Mantenimiento' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Maquinaria <span class="text-danger">*</span></label>
                        <select wire:model="id_maquinaria" class="form-select @error('id_maquinaria') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($maquinarias as $maquinaria)
                                <option value="{{ $maquinaria->id_maquinaria }}">{{ $maquinaria->modelo }} ({{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        @error('id_maquinaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                        <select wire:model="id_tipo_mantenimiento" class="form-select @error('id_tipo_mantenimiento') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_mantenimiento }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_tipo_mantenimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror">
                        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Fin</label>
                        <input type="date" wire:model="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror">
                        @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Costo Total</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="costo_total" step="0.01" class="form-control @error('costo_total') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('costo_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select wire:model="estado" class="form-select @error('estado') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="programado">Programado</option>
                            <option value="en curso">En Curso</option>
                            <option value="completado">Completado</option>
                        </select>
                        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($mantenimiento_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $mantenimiento_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Mantenimientos (Tabla) -->
        <div class="tab-pane fade" id="listado-mantenimientos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Mantenimientos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por maquinaria, tipo, estado o costo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Maquinaria</th>
                            <th>Tipo</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Costo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mantenimientos as $mantenimiento)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $mantenimiento->id_mantenimiento }}</span></td>
                                <td class="fw-semibold">{{ $mantenimiento->maquinaria?->modelo ?? 'N/A' }}</td>
                                <td>{{ $mantenimiento->tipoMantenimiento?->nombre ?? 'N/A' }}</td>
                                <td>{{ $mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                                <td>${{ number_format($mantenimiento->costo_total, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-{{ $mantenimiento->estado == 'completado' ? 'success' : ($mantenimiento->estado == 'en curso' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($mantenimiento->estado) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $mantenimiento->id_mantenimiento }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $mantenimiento->id_mantenimiento }})" onclick="return confirm('¿Está seguro de eliminar este mantenimiento?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay mantenimientos registrados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para cambiar de pestaña al editar/guardar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('mantenimientoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
