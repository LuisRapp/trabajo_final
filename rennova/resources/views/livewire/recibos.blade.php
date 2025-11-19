<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-file-earmark-text"></i> Comprobantes de pago</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="recibosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-recibo" type="button" role="tab" aria-controls="nuevo-recibo" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Comprobante
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-recibos" type="button" role="tab" aria-controls="listado-recibos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Comprobantes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="recibosTabContent">
        <!-- Pestaña 1: Nuevo Recibo (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-recibo" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $recibo_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $recibo_id ? 'Editar Recibo' : 'Nuevo Recibo' }}</h5>
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
                        <label class="form-label fw-semibold">Fecha Emisión <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_emision" class="form-control @error('fecha_emision') is-invalid @enderror">
                        @error('fecha_emision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <input type="text" wire:model="observaciones" class="form-control @error('observaciones') is-invalid @enderror" maxlength="150" placeholder="Observaciones">
                        @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto Bruto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto_bruto" step="0.01" class="form-control @error('monto_bruto') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('monto_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Descuentos</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="descuentos" step="0.01" class="form-control @error('descuentos') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('descuentos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto Neto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto" step="0.01" class="form-control bg-light" placeholder="0.00" readonly>
                        </div>
                        <small class="text-muted">Se calcula automáticamente (Bruto - Descuentos)</small>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if ($recibo_id)
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ $recibo_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Recibos (Tabla) -->
        <div class="tab-pane fade" id="listado-recibos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Recibos</h5>
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
                            <th>Fecha Emisión</th>
                            <th>Monto Bruto</th>
                            <th>Descuentos</th>
                            <th>Monto Neto</th>
                            <th>Observaciones</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recibos as $recibo)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $recibo->id_recibo }}</span></td>
                                <td class="fw-semibold">{{ $recibo->empleado?->apellido ?? 'N/A' }}, {{ $recibo->empleado?->nombre ?? '' }}</td>
                                <td>{{ $recibo->fecha_emision ? \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') : 'N/A' }}</td>
                                <td>${{ number_format($recibo->monto_bruto, 2, ',', '.') }}</td>
                                <td class="text-danger">${{ number_format($recibo->descuentos, 2, ',', '.') }}</td>
                                <td class="text-success fw-semibold">${{ number_format($recibo->monto, 2, ',', '.') }}</td>
                                <td>{{ $recibo->observaciones ?? '-' }}</td>
                                <td>
                                    @if($recibo->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar({{ $recibo->id_recibo }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar({{ $recibo->id_recibo }})" onclick="return confirm('¿Está seguro de eliminar este recibo?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay comprobantes registrados.</p>
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
        Livewire.on('reciboGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
