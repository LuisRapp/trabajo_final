<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Histórico de Roles Laborales</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Histórico', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-historico-roles', 'editar-historico-roles'])],
        ['value' => 'listado', 'label' => 'Listado de Históricos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-roles', 'editar-historico-roles'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $historico_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $historico_id ? 'Editar Histórico' : 'Nuevo Histórico' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rol Laboral <span class="text-danger">*</span></label>
                            <select wire:model="rol_laboral_id" class="form-select @error('rol_laboral_id') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($rolesLaborales as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('rol_laboral_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio/Ton <span class="text-danger">*</span></label>
                            <input type="number" wire:model="precio_tonelada" step="0.01" class="form-control @error('precio_tonelada') is-invalid @enderror" placeholder="0.00">
                            @error('precio_tonelada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jornal Diario <span class="text-danger">*</span></label>
                            <input type="number" wire:model="jornal_diario" step="0.01" class="form-control @error('jornal_diario') is-invalid @enderror" placeholder="0.00">
                            @error('jornal_diario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror">
                            @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Fin</label>
                            <input type="date" wire:model="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror">
                            @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Motivo del Cambio</label>
                            <input type="text" wire:model="motivo_cambio" class="form-control @error('motivo_cambio') is-invalid @enderror" placeholder="Motivo">
                            @error('motivo_cambio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($historico_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-historico-roles', 'editar-historico-roles'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $historico_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por rol..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Rol</th>
                                <th>Precio/Ton</th>
                                <th>Jornal</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id }}">
                                    <td><span class="badge bg-secondary">{{ $historico->id }}</span></td>
                                    <td><span class="fw-semibold">{{ $historico->rolLaboral->nombre ?? 'N/A' }}</span></td>
                                    <td>${{ number_format($historico->precio_tonelada, 2, ',', '.') }}</td>
                                    <td>${{ number_format($historico->jornal_diario, 2, ',', '.') }}</td>
                                    <td>{{ $historico->fecha_inicio ? \Carbon\Carbon::parse($historico->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $historico->fecha_fin ? \Carbon\Carbon::parse($historico->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $historico->id }})"
                                                deleteWireClick="eliminar({{ $historico->id }})"
                                                deleteMessage="¿Está seguro de eliminar este histórico?"
                                                :canEdit="auth()->user()->can('editar-historico-roles')"
                                                :canDelete="auth()->user()->can('eliminar-historico-roles')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay históricos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
