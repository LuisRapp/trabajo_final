<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-truck"></i> Cargas</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="cargasTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-carga" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nueva Carga
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-cargas" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Cargas
            </button>
        </li>
    </ul>

    <div class="tab-content" id="cargasTabContent">
        <!-- Tab 1: Formulario Nueva/Editar Carga -->
        <div class="tab-pane fade show active" id="nuevo-carga" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $carga_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $carga_id ? 'Editar Carga' : 'Nueva Carga' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                                <select wire:model="id_lote" class="form-select @error('id_lote') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($lotes as $lote)
                                        <option value="{{ $lote->id_lote }}">{{ $lote->id_lote }}</option>
                                    @endforeach
                                </select>
                                @error('id_lote') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Categoría Madera</label>
                                <select wire:model="id_categoria_madera" class="form-select @error('id_categoria_madera') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_categoria_madera') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Chofer (ID)</label>
                                <input type="number" wire:model="id_chofer" class="form-control @error('id_chofer') is-invalid @enderror">
                                @error('id_chofer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Parte Diario</label>
                                <select wire:model="id_parte_diario" class="form-select @error('id_parte_diario') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($partes as $parte)
                                        <option value="{{ $parte->id_parte_diario }}">{{ $parte->id_parte_diario }}</option>
                                    @endforeach
                                </select>
                                @error('id_parte_diario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Ticket</label>
                                <input type="text" wire:model="ticket" class="form-control @error('ticket') is-invalid @enderror" maxlength="20">
                                @error('ticket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Peso Bruto</label>
                                <input type="number" wire:model="peso_bruto" step="0.1" min="0" class="form-control @error('peso_bruto') is-invalid @enderror">
                                @error('peso_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tara</label>
                                <input type="number" wire:model="tara" step="0.1" min="0" class="form-control @error('tara') is-invalid @enderror">
                                @error('tara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Peso Neto</label>
                                <input type="number" wire:model="peso_neto" step="0.1" min="0" class="form-control @error('peso_neto') is-invalid @enderror">
                                @error('peso_neto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Destino</label>
                                <input type="text" wire:model="destino" class="form-control @error('destino') is-invalid @enderror" maxlength="100">
                                @error('destino') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Fecha Carga <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_carga" class="form-control @error('fecha_carga') is-invalid @enderror" max="{{ now()->toDateString() }}">
                                @error('fecha_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            @if ($carga_id)
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ $carga_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab 2: Listado de Cargas -->
        <div class="tab-pane fade" id="listado-cargas" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por lote, ticket, destino, chofer o fecha...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Lote</th>
                                    <th>Categoría</th>
                                    <th>Chofer</th>
                                    <th>Parte Diario</th>
                                    <th>Ticket</th>
                                    <th class="text-end">Bruto</th>
                                    <th class="text-end">Tara</th>
                                    <th class="text-end">Neto</th>
                                    <th>Destino</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cargas as $carga)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $carga->id_carga }}</span></td>
                                        <td>{{ $carga->lote->id_lote ?? $carga->id_lote }}</td>
                                        <td>{{ $carga->categoriaMadera->nombre ?? '-' }}</td>
                                        <td>
                                            @if($carga->chofer)
                                                {{ $carga->chofer->apellido }} {{ $carga->chofer->nombre }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $carga->id_parte_diario ?? '-' }}</td>
                                        <td>{{ $carga->ticket ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($carga->peso_bruto ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($carga->tara ?? 0, 2) }}</td>
                                        <td class="text-end"><strong>{{ number_format($carga->peso_neto ?? 0, 2) }}</strong></td>
                                        <td>{{ $carga->destino ?? '-' }}</td>
                                        <td>{{ 
                                            \Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y')
                                        }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar({{ $carga->id_carga }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar({{ $carga->id_carga }})" onclick="return confirm('¿Eliminar esta carga?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No hay cargas registradas.</p>
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
        Livewire.on('cargaGuardada', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
