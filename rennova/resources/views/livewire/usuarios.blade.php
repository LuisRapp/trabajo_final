<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-people-fill"></i> Usuarios</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Usuario', 'icon' => 'plus-circle', 'can' => auth()->user()->can('crear-usuarios')],
        ['value' => 'listado', 'label' => 'Listado de Usuarios', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @can('crear-usuarios')
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
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Contraseña @if(!$usuario_id)<span class="text-danger">*</span>@endif</label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Contraseña">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Confirmar Contraseña @if(!$usuario_id)<span class="text-danger">*</span>@endif</label>
                            <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Confirmar contraseña">
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" wire:model="telefono" class="form-control @error('telefono') is-invalid @enderror" placeholder="Teléfono">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select wire:model="activo" class="form-select @error('activo') is-invalid @enderror">
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
                        @can('crear-usuarios')
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $usuario_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
        @endcan
    @else
        <div class="card shadow">
            <div class="card-body">
                <x-search-input placeholder="Buscar por nombre, apellido, email..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usuario)
                                <tr wire:key="row-{{ $usuario->id }}">
                                    <td><span class="badge bg-secondary">{{ $usuario->id }}</span></td>
                                    <td><span class="fw-semibold">{{ $usuario->nombre }}</span></td>
                                    <td>{{ $usuario->apellido }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->telefono ?? '-' }}</td>
                                    <td>
                                        @if($usuario->trashed())
                                            <span class="badge bg-danger">Inactivo</span>
                                        @else
                                            <span class="badge bg-success">Activo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $usuario->id }})"
                                                deleteWireClick="eliminar({{ $usuario->id }})"
                                                deleteMessage="¿Está seguro de eliminar este usuario?"
                                                :canEdit="auth()->user()->can('editar-usuarios')"
                                                :canDelete="auth()->user()->can('eliminar-usuarios')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay usuarios registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
