<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-badge"></i> Choferes</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="choferesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-chofer" type="button" role="tab" aria-controls="nuevo-chofer" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Chofer
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-choferes" type="button" role="tab" aria-controls="listado-choferes" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Choferes
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="choferesTabContent">
        <!-- Pestaña 1: Nuevo Chofer (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-chofer" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $chofer_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $chofer_id ? 'Modificar Chofer' : 'Nuevo Chofer' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_cliente') is-invalid @enderror" wire:model="id_cliente">
                                    <option value="">Seleccione un cliente...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social ?? $cliente->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">El chofer estará asociado a este cliente</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('apellido') is-invalid @enderror" placeholder="Apellido del chofer" wire:model="apellido">
                                @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre del chofer" wire:model="nombre">
                                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('dni') is-invalid @enderror" placeholder="DNI del chofer" wire:model="dni">
                                @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" placeholder="Teléfono de contacto" wire:model="telefono">
                                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror" placeholder="Dirección del chofer" wire:model="direccion">
                                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="estado" wire:model="estado">
                                    <label class="form-check-label" for="estado">
                                        <span class="badge {{ $estado ? 'bg-success' : 'bg-secondary' }}">{{ $estado ? 'Activo' : 'Inactivo' }}</span>
                                    </label>
                                </div>
                                <small class="text-muted">Los choferes inactivos no aparecerán en los formularios de carga</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            @if ($chofer_id)
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ $chofer_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Choferes (Tabla) -->
        <div class="tab-pane fade" id="listado-choferes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Choferes</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por cliente, apellido, nombre, DNI, teléfono o dirección...">
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
                                    <th>Cliente</th>
                                    <th>Apellido y Nombre</th>
                                    <th>DNI</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($choferes as $c)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $c->id_chofer }}</span></td>
                                        <td>{{ $c->cliente?->razon_social ?? $c->cliente?->nombre ?? 'Sin cliente' }}</td>
                                        <td>{{ $c->apellido }}, {{ $c->nombre }}</td>
                                        <td>{{ $c->dni }}</td>
                                        <td>{{ $c->telefono ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $c->estado ? 'success' : 'secondary' }}">
                                                {{ $c->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar({{ $c->id_chofer }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar({{ $c->id_chofer }})" onclick="return confirm('¿Está seguro de eliminar este chofer?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay choferes registrados.</p>
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
        Livewire.on('choferGuardado', () => {
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
            
            // Detectar si hay un chofer_id cargado (modo edición)
            if (component.fingerprint.name === 'choferes') {
                const isEditing = document.querySelector('form input[wire\\:model="chofer_id"]')?.value;
                
                if (isEditing) {
                    nuevoTabButton.innerHTML = '<i class="bi bi-pencil-square"></i> Modificar Chofer';
                } else {
                    nuevoTabButton.innerHTML = '<i class="bi bi-plus-circle"></i> Nuevo Chofer';
                }
            }
        });
    });
</script>
