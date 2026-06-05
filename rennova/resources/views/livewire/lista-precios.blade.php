<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tags"></i> Lista de Precios</h1>
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

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Precio', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-precios', 'editar-precios'])],
        ['value' => 'listado', 'label' => 'Listado de Precios', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-precios', 'editar-precios'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $precio_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $precio_id ? 'Editar Precio' : 'Nuevo Precio' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                            <select wire:model="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                            <select wire:model="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
                            <input type="number" wire:model="precio" step="0.01" class="form-control @error('precio') is-invalid @enderror" placeholder="0.00">
                            @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Desde <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_desde" class="form-control @error('fecha_desde') is-invalid @enderror">
                            @error('fecha_desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Hasta</label>
                            <input type="date" wire:model="fecha_hasta" class="form-control @error('fecha_hasta') is-invalid @enderror">
                            @error('fecha_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($precio_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-precios', 'editar-precios'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $precio_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @else
        <div class="card shadow">
            <div class="card-body">
                <x-search-input placeholder="Buscar por cliente, categoría..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Categoría</th>
                                <th>Precio/Ton</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($precios as $precioItem)
                                <tr wire:key="row-{{ $precioItem->id }}">
                                    <td><span class="badge bg-secondary">{{ $precioItem->id }}</span></td>
                                    <td><span class="fw-semibold">{{ $precioItem->cliente->razon_social ?? 'N/A' }}</span></td>
                                    <td>{{ $precioItem->categoriaMadera->nombre ?? 'N/A' }}</td>
                                    <td>${{ number_format($precioItem->precio, 2, ',', '.') }}</td>
                                    <td>{{ $precioItem->fecha_desde ? \Carbon\Carbon::parse($precioItem->fecha_desde)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $precioItem->fecha_hasta ? \Carbon\Carbon::parse($precioItem->fecha_hasta)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $precioItem->id }})"
                                                deleteWireClick="eliminar({{ $precioItem->id }})"
                                                deleteMessage="¿Está seguro de eliminar este precio? Esta acción no se puede deshacer."
                                                :canEdit="auth()->user()->can('editar-precios')"
                                                :canDelete="auth()->user()->can('eliminar-precios')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay precios registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
