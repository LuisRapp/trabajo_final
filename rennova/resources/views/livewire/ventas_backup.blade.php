<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-geo-alt"></i> Lotes</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="lotesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-lote" type="button" role="tab" aria-controls="nuevo-lote" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Lote
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-lotes" type="button" role="tab" aria-controls="listado-lotes" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Lotes
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="lotesTabContent">
        <!-- Pestaña 1: Nuevo Lote (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-lote" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $lote_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $lote_id ? 'Modificar Lote' : 'Nuevo Lote' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Propietario <span class="text-danger">*</span></label>
                        <input type="text" wire:model="propietario" class="form-control @error('propietario') is-invalid @enderror" placeholder="Nombre del propietario">
                        @error('propietario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ubicación <span class="text-danger">*</span></label>
                        <input type="text" wire:model="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror" placeholder="Ubicación del lote">
                        @error('ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Especie</label>
                        <input type="text" wire:model="especie" class="form-control @error('especie') is-invalid @enderror" placeholder="Especie de madera">
                        @error('especie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Superficie (ha)</label>
                        <input type="number" wire:model="superficie" step="0.01" class="form-control @error('superficie') is-invalid @enderror" placeholder="0.00">
                        @error('superficie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Condición de compra</label>
                        <select wire:model="condicion_compra" class="form-select @error('condicion_compra') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="propio">Vuelo Forestal</option>
                            <option value="alquilado">Compra por tonelada</option>
                        </select>
                        @error('condicion_compra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <select wire:model="estado" class="form-select @error('estado') is-invalid @enderror">
                            <option value="activo">Activo</option>
                            <option value="en_proceso">En Explotación</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($lote_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $lote_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Lotes (Tabla) -->
        <div class="tab-pane fade" id="listado-lotes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Lotes</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por propietario, ubicación o especie...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Propietario</th>
                            <th>Ubicación</th>
                            <th>Especie</th>
                            <th>Superficie (ha)</th>
                            <th>Condición</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lotes as $lote)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $lote->id_lote }}</span></td>
                                <td>{{ $lote->propietario }}</td>
                                <td>{{ $lote->ubicacion }}</td>
                                <td>{{ $lote->especie ?? '-' }}</td>
                                <td class="text-end">{{ number_format($lote->superficie ?? 0, 2) }}</td>
                                <td>
                                    @if($lote->condicion_compra)
                                        <span class="badge bg-{{ $lote->condicion_compra == 'propio' ? 'success' : 'info' }}">
                                            {{ ucfirst($lote->condicion_compra) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $lote->estado == 'activo' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($lote->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $lote->id_lote }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $lote->id_lote }})" onclick="return confirm('¿Está seguro de eliminar este lote?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay lotes registrados.</p>
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

<!-- JavaScript para cambiar de pestaña al editar -->
<script>
    function cambiarAPestanaFormulario() {
        // Activar la pestaña del formulario
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        
        // Scroll suave al inicio de la página
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Listener para volver a la pestaña de listado después de guardar
    document.addEventListener('livewire:init', () => {
        Livewire.on('loteGuardado', () => {
            // Cambiar a la pestaña de listado después de guardar
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            
            // Scroll al inicio
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Cambiar título del tab cuando se está editando
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('message.processed', (message, component) => {
            const nuevoTabButton = document.getElementById('nuevo-tab');
            const tituloFormulario = document.querySelector('#nuevo-lote .card-header h5');
            
            // Detectar si hay un lote_id cargado (modo edición)
            if (component.fingerprint.name === 'lotes') {
                const isEditing = document.querySelector('form input[wire\\:model="lote_id"]')?.value;
                
                if (isEditing) {
                    nuevoTabButton.innerHTML = '<i class="bi bi-pencil-square"></i> Modificar Lote';
                } else {
                    nuevoTabButton.innerHTML = '<i class="bi bi-plus-circle"></i> Nuevo Lote';
                }
            }
        });
    });
</script>
