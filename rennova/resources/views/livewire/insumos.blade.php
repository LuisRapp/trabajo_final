<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-box-seam"></i> Insumos</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="insumosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-insumo" type="button" role="tab" aria-controls="nuevo-insumo" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Insumo
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-insumos" type="button" role="tab" aria-controls="listado-insumos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Insumos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="insumosTabContent">
        <!-- Pestaña 1: Nuevo Insumo (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-insumo" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $insumo_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $insumo_id ? 'Editar Insumo' : 'Nuevo Insumo' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre del insumo">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Costo Unitario</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="costo_unitario" step="0.01" class="form-control @error('costo_unitario') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('costo_unitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unidad de Medida</label>
                        <select wire:model="id_unidad_medida" class="form-select @error('id_unidad_medida') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($unidades as $unidad)
                                <option value="{{ $unidad->id_unidad_medida }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                            @endforeach
                        </select>
                        @error('id_unidad_medida') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Proveedor</label>
                        <select wire:model="id_proveedor" class="form-select @error('id_proveedor') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                            @endforeach
                        </select>
                        @error('id_proveedor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea wire:model="descripcion" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Descripción del insumo" rows="1"></textarea>
                        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($insumo_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $insumo_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Insumos (Tabla) -->
        <div class="tab-pane fade" id="listado-insumos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Insumos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre, descripción, proveedor, unidad o costo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Proveedor</th>
                            <th>Costo Unitario</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($insumos as $insumo)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $insumo->id_insumo }}</span></td>
                                <td class="fw-semibold">{{ $insumo->nombre }}</td>
                                <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                                <td>
                                    @if($insumo->unidadMedida)
                                        {{ $insumo->unidadMedida->nombre }} <span class="badge bg-info">{{ $insumo->unidadMedida->abreviatura }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $insumo->proveedor?->razon_social ?? 'N/A' }}</td>
                                <td>${{ number_format($insumo->costo_unitario, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $insumo->id_insumo }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $insumo->id_insumo }})" onclick="return confirm('¿Está seguro de eliminar este insumo?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay insumos registrados.</p>
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
        Livewire.on('insumoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
