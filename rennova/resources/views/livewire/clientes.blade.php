<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-people"></i> Clientes</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="clientesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-cliente" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-clientes" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Clientes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="clientesTabContent">
        <!-- Tab 1: Formulario Nuevo Cliente -->
        <div class="tab-pane fade show active" id="nuevo-cliente" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $cliente_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $cliente_id ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Razón Social / Nombre <span class="text-danger">*</span></label>
                                <input type="text" wire:model="razon_social" class="form-control @error('razon_social') is-invalid @enderror" placeholder="Nombre del cliente">
                                @error('razon_social') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">CUIT <span class="text-danger">*</span></label>
                                <input type="text" wire:model="cuit" class="form-control @error('cuit') is-invalid @enderror" placeholder="XX-XXXXXXXX-X">
                                @error('cuit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" wire:model="direccion" class="form-control @error('direccion') is-invalid @enderror" placeholder="Dirección completa">
                                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contacto</label>
                                <input type="text" wire:model="contacto" class="form-control @error('contacto') is-invalid @enderror" placeholder="Teléfono / Email">
                                @error('contacto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            @if ($cliente_id)
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ $cliente_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab 2: Listado de Clientes -->
        <div class="tab-pane fade" id="listado-clientes" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por razón social, CUIT o contacto...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Razón Social / Nombre</th>
                                    <th>CUIT</th>
                                    <th>Dirección</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clientes as $cliente)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $cliente->id_cliente }}</span></td>
                                        <td><span class="fw-semibold">{{ $cliente->razon_social ?? $cliente->nombre }}</span></td>
                                        <td>{{ $cliente->cuit }}</td>
                                        <td>{{ $cliente->direccion ?? '-' }}</td>
                                        <td>{{ $cliente->contacto ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar({{ $cliente->id_cliente }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar({{ $cliente->id_cliente }})" onclick="return confirm('¿Está seguro de eliminar este cliente?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No hay clientes registrados.</p>
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

<!-- JavaScript para cambiar entre pestañas -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('clienteGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
