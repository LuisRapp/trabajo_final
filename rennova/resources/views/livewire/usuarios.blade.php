<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-circle"></i> Usuarios</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="usuariosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            @canany(['crear-usuarios', 'editar-usuarios'])
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-usuario" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </button>
            @endcanany
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-usuarios" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Usuarios
            </button>
        </li>
    </ul>

    <div class="tab-content" id="usuariosTabContent">
        <!-- Tab 1: Formulario Nuevo Usuario -->
        @canany(['crear-usuarios', 'editar-usuarios'])
        <div class="tab-pane fade" id="nuevo-usuario" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $usuario_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $usuario_id ? 'Editar Usuario' : 'Nuevo Usuario' }}</h5>
                </div>
                <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                        <input type="text" wire:model="apellido" class="form-control @error('apellido') is-invalid @enderror" placeholder="Apellido">
                        @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" wire:model="telefono" class="form-control @error('telefono') is-invalid @enderror" placeholder="Número de teléfono">
                        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Contraseña @if($usuario_id)<span class="text-muted">(dejar vacío para no cambiar)</span>@else<span class="text-danger">*</span>@endif</label>
                        <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Contraseña">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Confirmar Contraseña</label>
                        <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Repetir contraseña">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select wire:model="activo" class="form-select @error('activo') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        @error('activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($usuario_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    @canany(['crear-usuarios', 'editar-usuarios'])
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $usuario_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                    @endcanany
                </div>
            </form>
        </div>
    </div>
</div>
        @endcanany

<!-- Tab 2: Listado de Usuarios -->
<div class="tab-pane fade show active" id="listado-usuarios" role="tabpanel">
    <div class="card shadow">
        <div class="card-body">
            <!-- Buscador -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre, apellido o email...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            <tr wire:key="row-{{ $usuario->id }}">
                                <td><span class="badge bg-secondary">{{ $usuario->id }}</span></td>
                                <td class="fw-semibold">{{ $usuario->apellido }}, {{ $usuario->nombre }}</td>
                                <td><i class="bi bi-envelope"></i> {{ $usuario->email }}</td>
                                <td><i class="bi bi-telephone"></i> {{ $usuario->telefono ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $usuario->activo ? 'success' : 'secondary' }}">
                                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('editar-usuarios')
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $usuario->id }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan
                                        @can('eliminar-usuarios')
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $usuario->id }})" onclick="return confirm('¿Está seguro de eliminar este usuario?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay usuarios registrados.</p>
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
        Livewire.on('usuarioGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
