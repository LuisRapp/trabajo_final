<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Kits de Mantenimiento Preventivo</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Kit', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-kits-preventivos', 'editar-kits-preventivos'])],
        ['value' => 'listado', 'label' => 'Listado de Kits', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-kits-preventivos', 'editar-kits-preventivos'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $kit_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $kit_id ? 'Editar Kit' : 'Nuevo Kit' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre del Kit <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre del kit">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea wire:model="descripcion" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Descripción del kit" rows="1"></textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($kit_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-kits-preventivos', 'editar-kits-preventivos'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $kit_id ? 'Actualizar' : 'Guardar' }}
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
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kits as $kit)
                                <tr wire:key="row-{{ $kit->id_kit_preventivo }}">
                                    <td><span class="badge bg-secondary">{{ $kit->id_kit_preventivo }}</span></td>
                                    <td><span class="fw-semibold">{{ $kit->nombre }}</span></td>
                                    <td>{{ $kit->descripcion ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $kit->id_kit_preventivo }})"
                                                deleteWireClick="eliminar({{ $kit->id_kit_preventivo }})"
                                                deleteMessage="¿Está seguro de eliminar este kit?"
                                                :canEdit="auth()->user()->can('editar-kits-preventivos')"
                                                :canDelete="auth()->user()->can('eliminar-kits-preventivos')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay kits registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
