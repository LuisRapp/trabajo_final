<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Tipos de Mantenimiento</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Tipo', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])],
        ['value' => 'listado', 'label' => 'Listado de Tipos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $tipo_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $tipo_id ? 'Editar Tipo' : 'Nuevo Tipo' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Preventivo, Correctivo, Predictivo">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($tipo_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $tipo_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tipos as $tipo)
                                <tr wire:key="row-{{ $tipo->id_tipo_mantenimiento }}">
                                    <td><span class="badge bg-secondary">{{ $tipo->id_tipo_mantenimiento }}</span></td>
                                    <td><span class="fw-semibold">{{ $tipo->nombre }}</span></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $tipo->id_tipo_mantenimiento }})"
                                                deleteWireClick="eliminar({{ $tipo->id_tipo_mantenimiento }})"
                                                deleteMessage="¿Está seguro de dar de baja este tipo?"
                                                :canEdit="auth()->user()->can('editar-mantenimiento-tipos')"
                                                :canDelete="auth()->user()->can('eliminar-mantenimiento-tipos')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="3" message="No hay tipos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
