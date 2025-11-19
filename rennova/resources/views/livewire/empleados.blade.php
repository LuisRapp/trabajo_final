<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-workspace"></i> Empleados</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="empleadosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-empleado" type="button" role="tab" aria-controls="nuevo-empleado" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Empleado
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-empleados" type="button" role="tab" aria-controls="listado-empleados" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Empleados
            </button>
        </li>
    </ul>

    <div class="tab-content" id="empleadosTabContent">
        <!-- Pestaña 1: Nuevo Empleado (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-empleado" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $empleado_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $empleado_id ? 'Editar Empleado' : 'Nuevo Empleado' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                        <input type="text" wire:model="dni" class="form-control @error('dni') is-invalid @enderror" placeholder="12345678" maxlength="8">
                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                        <input type="text" wire:model="apellido" class="form-control @error('apellido') is-invalid @enderror" placeholder="Apellido">
                        @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Rol Laboral <span class="text-danger">*</span></label>
                        <select wire:model="id_rol_laboral" class="form-select @error('id_rol_laboral') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_rol_laboral') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Nacimiento</label>
                        <input type="date" wire:model="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror">
                        @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_inicio_actividades" class="form-control @error('fecha_inicio_actividades') is-invalid @enderror">
                        @error('fecha_inicio_actividades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Fin</label>
                        <input type="date" wire:model="fecha_fin_actividades" class="form-control @error('fecha_fin_actividades') is-invalid @enderror">
                        <small class="text-muted">Opcional</small>
                        @error('fecha_fin_actividades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($empleado_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $empleado_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Empleados (Tabla) -->
        <div class="tab-pane fade" id="listado-empleados" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Empleados</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por apellido, nombre, DNI o rol...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>DNI</th>
                            <th>Apellido y Nombre</th>
                            <th>Rol</th>
                            <th>Fecha Nacimiento</th>
                            <th>Fecha Inicio</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empleados as $empleado)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $empleado->id_empleado }}</span></td>
                                <td>{{ number_format($empleado->dni, 0, ',', '.') }}</td>
                                <td class="fw-semibold">{{ $empleado->apellido }}, {{ $empleado->nombre }}</td>
                                <td>{{ $empleado->rolLaboral?->nombre ?? 'N/A' }}</td>
                                <td>{{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $empleado->fecha_inicio_actividades ? \Carbon\Carbon::parse($empleado->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $empleado->id_empleado }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $empleado->id_empleado }})" onclick="return confirm('¿Está seguro de eliminar este empleado?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay empleados registrados.</p>
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
        Livewire.on('empleadoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
