<div class="container py-4">
    <!-- Header con información del Kit -->
    <div class="card bg-light mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="bi bi-tools"></i> Configurando Kit: <strong>{{ $kit->nombre_kit }}</strong></h4>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-gear-wide-connected"></i> Tipo de Maquinaria: 
                        <span class="badge bg-info">{{ $kit->tipoMaquinaria->nombre }}</span>
                    </p>
                </div>
                <a href="{{ route('kits-preventivos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Kits
                </a>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Formulario para Añadir Insumo -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Añadir Insumo al Kit</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="agregarInsumo">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                        <select wire:model="insumo_id" class="form-select @error('insumo_id') is-invalid @enderror">
                            <option value="">Seleccione un insumo</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}">
                                    {{ $insumo->nombre }} ({{ $insumo->unidad_medida }})
                                </option>
                            @endforeach
                        </select>
                        @error('insumo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cantidad Necesaria <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0" wire:model="cantidad_necesaria" class="form-control @error('cantidad_necesaria') is-invalid @enderror" placeholder="0.00">
                        @error('cantidad_necesaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Insumos del Kit -->
    <div class="card shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Insumos en el Kit</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Insumo</th>
                            <th>Unidad de Medida</th>
                            <th>Cantidad Necesaria</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($insumosKit as $insumo)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $insumo['id_insumo'] }}</span></td>
                                <td><span class="fw-semibold">{{ $insumo['nombre'] }}</span></td>
                                <td>{{ $insumo['unidad_medida'] }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ number_format($insumo['cantidad_necesaria'], 2) }} {{ $insumo['unidad_medida'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" wire:click="quitarInsumo({{ $insumo['id_insumo'] }})" onclick="return confirm('¿Está seguro de quitar este insumo del kit?')" title="Quitar">
                                        <i class="bi bi-trash"></i> Quitar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No hay insumos en este kit. Agregue insumos usando el formulario superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
