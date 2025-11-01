<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-truck"></i> Maquinarias</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="maquinariasTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nueva-maquinaria" type="button" role="tab" aria-controls="nueva-maquinaria" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nueva Maquinaria
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-maquinarias" type="button" role="tab" aria-controls="listado-maquinarias" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Maquinarias
            </button>
        </li>
    </ul>

    <div class="tab-content" id="maquinariasTabContent">
        <!-- Pestaña 1: Nueva Maquinaria (Formulario) -->
        <div class="tab-pane fade show active" id="nueva-maquinaria" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $maquinaria_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $maquinaria_id ? 'Editar Maquinaria' : 'Nueva Maquinaria' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de Maquinaria <span class="text-danger">*</span></label>
                        <select wire:model="id_tipo_maquinaria" class="form-select @error('id_tipo_maquinaria') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_maquinaria }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_tipo_maquinaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                        <input type="text" wire:model="modelo" class="form-control @error('modelo') is-invalid @enderror" placeholder="Modelo de la maquinaria">
                        @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select wire:model="estado" class="form-select @error('estado') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="operativa">Operativa</option>
                            <option value="en_mantenimiento">En Mantenimiento</option>
                            <option value="fuera_de_servicio">Fuera de Servicio</option>
                        </select>
                        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">¿Es Alquilada?</label>
                        <select wire:model="es_alquilada" class="form-select @error('es_alquilada') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                        @error('es_alquilada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha Inicio Actividades</label>
                        <input type="date" wire:model="fecha_inicio_actividades" class="form-control @error('fecha_inicio_actividades') is-invalid @enderror">
                        @error('fecha_inicio_actividades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($maquinaria_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $maquinaria_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Maquinarias (Tabla) -->
        <div class="tab-pane fade" id="listado-maquinarias" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Maquinarias</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por tipo, modelo o estado...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Modelo</th>
                            <th>Estado</th>
                            <th>Alquilada</th>
                            <th>Fecha Inicio</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maquinarias as $maquinaria)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $maquinaria->id_maquinaria }}</span></td>
                                <td class="fw-semibold">{{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }}</td>
                                <td>{{ $maquinaria->modelo }}</td>
                                <td>
                                    <span class="badge bg-{{ $maquinaria->estado == 'operativa' ? 'success' : ($maquinaria->estado == 'en_mantenimiento' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $maquinaria->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($maquinaria->es_alquilada)
                                        <i class="bi bi-check-circle-fill text-success"></i> Sí
                                    @else
                                        <i class="bi bi-x-circle-fill text-secondary"></i> No
                                    @endif
                                </td>
                                <td>{{ $maquinaria->fecha_inicio_actividades ? \Carbon\Carbon::parse($maquinaria->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $maquinaria->id_maquinaria }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $maquinaria->id_maquinaria }})" onclick="return confirm('¿Está seguro de eliminar esta maquinaria?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay maquinarias registradas.</p>
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
        Livewire.on('maquinariaGuardada', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
