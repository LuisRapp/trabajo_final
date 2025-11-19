<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-cash-coin"></i> Adelantos</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="adelantosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-adelanto" type="button" role="tab" aria-controls="nuevo-adelanto" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Adelanto
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-adelantos" type="button" role="tab" aria-controls="listado-adelantos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Adelantos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="adelantosTabContent">
        <!-- Pestaña 1: Nuevo Adelanto (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-adelanto" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $adelanto_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $adelanto_id ? 'Editar Adelanto' : 'Nuevo Adelanto' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                        <select wire:model="id_empleado" class="form-select @error('id_empleado') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($empleados as $empleado)
                                <option value="{{ $empleado->id_empleado }}">{{ $empleado->apellido }}, {{ $empleado->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto" step="0.01" class="form-control @error('monto') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('monto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha de Adelanto <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_adelanto" class="form-control @error('fecha_adelanto') is-invalid @enderror">
                        @error('fecha_adelanto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($adelanto_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $adelanto_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Adelantos (Tabla) -->
        <div class="tab-pane fade" id="listado-adelantos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Adelantos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por empleado, monto o fecha...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Empleado</th>
                            <th>Monto</th>
                            <th>Fecha Adelanto</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adelantos as $adelanto)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $adelanto->id_adelanto }}</span></td>
                                <td class="fw-semibold">{{ $adelanto->empleado?->apellido ?? 'N/A' }}, {{ $adelanto->empleado?->nombre ?? '' }}</td>
                                <td>${{ number_format($adelanto->monto, 2, ',', '.') }}</td>
                                <td>{{ $adelanto->fecha_adelanto ? \Carbon\Carbon::parse($adelanto->fecha_adelanto)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($adelanto->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $adelanto->id_adelanto }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $adelanto->id_adelanto }})" onclick="return confirm('¿Está seguro de eliminar este adelanto?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay adelantos registrados.</p>
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
        Livewire.on('adelantoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
