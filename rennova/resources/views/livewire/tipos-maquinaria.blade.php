<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-gear-wide-connected"></i> Tipos de Maquinaria</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="tiposTabs" role="tablist">
        <li class="nav-item" role="presentation">
            @canany(['crear-tipos-maquinaria', 'editar-tipos-maquinaria'])
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-tipo" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Tipo
            </button>
            @endcanany
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-tipos" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Tipos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tiposTabContent">
        <!-- Tab 1: Formulario Nuevo Tipo -->
        @canany(['crear-tipos-maquinaria', 'editar-tipos-maquinaria'])
        <div class="tab-pane fade" id="nuevo-tipo" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $tipo_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $tipo_id ? 'Editar Tipo' : 'Nuevo Tipo' }}</h5>
                </div>
                <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Nombre del Tipo <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Excavadora, Tractor, Cargadora">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($tipo_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    @canany(['crear-tipos-maquinaria', 'editar-tipos-maquinaria'])
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $tipo_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                    @endcanany
                </div>
            </form>
        </div>
    </div>
</div>
        @endcanany

<!-- Tab 2: Listado de Tipos -->
<div class="tab-pane fade show active" id="listado-tipos" role="tabpanel">
    <div class="card shadow">
        <div class="card-body">
            <!-- Buscador -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tipos as $tipo)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $tipo->id_tipo_maquinaria }}</span></td>
                                <td><span class="fw-semibold">{{ $tipo->nombre }}</span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('editar-tipos-maquinaria')
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $tipo->id_tipo_maquinaria }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan
                                        @can('eliminar-tipos-maquinaria')
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $tipo->id_tipo_maquinaria }})" onclick="return confirm('¿Está seguro de eliminar este tipo?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay tipos registrados.</p>
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
        Livewire.on('tipoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
