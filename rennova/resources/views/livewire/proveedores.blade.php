<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-truck"></i> Proveedores</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

        <!-- Pestañas (Tabs) -->
        <ul class="nav nav-tabs mb-4" id="proveedoresTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-proveedor" type="button" role="tab" aria-controls="nuevo-proveedor" aria-selected="true">
                    <i class="bi bi-plus-circle"></i> Nuevo Proveedor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-proveedores" type="button" role="tab" aria-controls="listado-proveedores" aria-selected="false">
                    <i class="bi bi-list-ul"></i> Listado de Proveedores
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="proveedoresTabContent">
            <!-- Pestaña 1: Nuevo Proveedor (Formulario) -->
            <div class="tab-pane fade show active" id="nuevo-proveedor" role="tabpanel" aria-labelledby="nuevo-tab">
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-{{ $proveedor_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" wire:model="razon_social" class="form-control @error('razon_social') is-invalid @enderror" placeholder="Nombre del proveedor">
                        @error('razon_social') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CUIT <span class="text-danger">*</span></label>
                        <input type="text" wire:model="cuit" class="form-control @error('cuit') is-invalid @enderror" placeholder="XX-XXXXXXXX-X">
                        @error('cuit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dirección</label>
                        <input type="text" wire:model="direccion" class="form-control @error('direccion') is-invalid @enderror" placeholder="Dirección completa">
                        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" wire:model="telefono" class="form-control @error('telefono') is-invalid @enderror" placeholder="+54 9 11 1234-5678">
                        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($proveedor_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $proveedor_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
            </div>

            <!-- Pestaña 2: Listado de Proveedores (Tabla) -->
            <div class="tab-pane fade" id="listado-proveedores" role="tabpanel" aria-labelledby="listado-tab">
    <div class="card shadow">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Listado de Proveedores</h5>
                    </div>
        <div class="card-body">
                        <!-- Buscador -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por razón social, CUIT o email...">
                                </div>
                            </div>
                        </div>

            <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Razón Social</th>
                            <th>CUIT</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proveedores as $proveedor)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $proveedor->id_proveedor }}</span></td>
                                <td><span class="fw-semibold">{{ $proveedor->razon_social }}</span></td>
                                <td>{{ $proveedor->cuit }}</td>
                                <td>{{ $proveedor->direccion ?? '-' }}</td>
                                <td>{{ $proveedor->telefono ?? '-' }}</td>
                                <td>{{ $proveedor->email ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $proveedor->id_proveedor }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $proveedor->id_proveedor }})" onclick="return confirm('¿Está seguro de eliminar este proveedor?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay proveedores registrados.</p>
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

<!-- JavaScript para cambiar de pestaña al editar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('proveedorGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
</div>
