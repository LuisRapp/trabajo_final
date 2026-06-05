<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tree"></i> Categorías de Madera</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Categoría', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-categorias-madera', 'editar-categorias-madera'])],
        ['value' => 'listado', 'label' => 'Listado de Categorías', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-categorias-madera', 'editar-categorias-madera'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $categoria_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $categoria_id ? 'Editar Categoría' : 'Nueva Categoría' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre de la categoría">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea wire:model="descripcion" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Descripción de la categoría" rows="1"></textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($categoria_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-categorias-madera', 'editar-categorias-madera'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $categoria_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre o descripción..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categorias as $categoria)
                                <tr wire:key="row-{{ $categoria->id_categoria_madera }}">
                                    <td><span class="badge bg-secondary">{{ $categoria->id_categoria_madera }}</span></td>
                                    <td><span class="fw-semibold">{{ $categoria->nombre }}</span></td>
                                    <td>{{ $categoria->descripcion ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $categoria->id_categoria_madera }})"
                                                deleteWireClick="eliminar({{ $categoria->id_categoria_madera }})"
                                                deleteMessage="¿Está seguro de eliminar esta categoría?"
                                                :canEdit="auth()->user()->can('editar-categorias-madera')"
                                                :canDelete="auth()->user()->can('eliminar-categorias-madera')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay categorías registradas." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
