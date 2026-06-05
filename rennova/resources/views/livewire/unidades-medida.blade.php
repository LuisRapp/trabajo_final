<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-rulers"></i> Unidades de Medida</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Unidad', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-unidades-medida', 'editar-unidades-medida'])],
        ['value' => 'listado', 'label' => 'Listado de Unidades', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-unidades-medida', 'editar-unidades-medida'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $unidad_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $unidad_id ? 'Editar Unidad' : 'Nueva Unidad' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre de la unidad de medida">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Abreviatura <span class="text-danger">*</span></label>
                            <input type="text" wire:model="abreviatura" class="form-control @error('abreviatura') is-invalid @enderror" placeholder="Ej: kg, lt, m3">
                            @error('abreviatura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($unidad_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-unidades-medida', 'editar-unidades-medida'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $unidad_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre o abreviatura..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Abreviatura</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unidades as $unidad)
                                <tr wire:key="row-{{ $unidad->id_unidad_medida }}">
                                    <td><span class="badge bg-secondary">{{ $unidad->id_unidad_medida }}</span></td>
                                    <td><span class="fw-semibold">{{ $unidad->nombre }}</span></td>
                                    <td><span class="badge bg-info">{{ $unidad->abreviatura }}</span></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $unidad->id_unidad_medida }})"
                                                deleteWireClick="eliminar({{ $unidad->id_unidad_medida }})"
                                                deleteMessage="¿Está seguro de eliminar esta unidad?"
                                                :canEdit="auth()->user()->can('editar-unidades-medida')"
                                                :canDelete="auth()->user()->can('eliminar-unidades-medida')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay unidades registradas." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
