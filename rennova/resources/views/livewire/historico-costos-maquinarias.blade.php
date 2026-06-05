<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-currency-dollar"></i> Histórico de Costos de Maquinarias</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Histórico', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-historico-costos', 'editar-historico-costos'])],
        ['value' => 'listado', 'label' => 'Listado de Históricos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-costos', 'editar-historico-costos'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $historico_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $historico_id ? 'Editar Histórico' : 'Nuevo Histórico' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Maquinaria <span class="text-danger">*</span></label>
                            <select wire:model="id_maquinaria" class="form-select @error('id_maquinaria') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maq)
                                    <option value="{{ $maq->id_maquinaria }}" wire:key="option-{{ $maq->id_maquinaria }}">{{ $maq->modelo }}</option>
                                @endforeach
                            </select>
                            @error('id_maquinaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Costo por Tonelada <span class="text-danger">*</span></label>
                            <input type="number" wire:model="costo_por_tonelada" step="0.01" class="form-control @error('costo_por_tonelada') is-invalid @enderror" placeholder="0.00">
                            @error('costo_por_tonelada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Inicio Vigencia <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_inicio_vigencia" class="form-control @error('fecha_inicio_vigencia') is-invalid @enderror">
                            @error('fecha_inicio_vigencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin Vigencia</label>
                            <input type="date" wire:model="fecha_fin_vigencia" class="form-control @error('fecha_fin_vigencia') is-invalid @enderror">
                            @error('fecha_fin_vigencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Opcional — dejar en blanco si está vigente actualmente</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($historico_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-historico-costos', 'editar-historico-costos'])
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
                <x-search-input placeholder="Buscar por modelo de maquinaria..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Maquinaria</th>
                                <th>Costo/Ton</th>
                                <th>Inicio Vig.</th>
                                <th>Fin Vig.</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id_costo }}">
                                    <td><span class="badge bg-secondary">{{ $historico->id_costo }}</span></td>
                                    <td><span class="fw-semibold">{{ $historico->maquinaria->modelo ?? 'N/A' }}</span></td>
                                    <td>${{ number_format($historico->costo_por_tonelada, 2, ',', '.') }}</td>
                                    <td>{{ $historico->fecha_inicio_vigencia ? \Carbon\Carbon::parse($historico->fecha_inicio_vigencia)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $historico->fecha_fin_vigencia ? \Carbon\Carbon::parse($historico->fecha_fin_vigencia)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $historico->id_costo }})"
                                                deleteWireClick="eliminar({{ $historico->id_costo }})"
                                                deleteMessage="¿Eliminar este histórico?"
                                                :canEdit="auth()->user()->can('editar-historico-costos')"
                                                :canDelete="auth()->user()->can('eliminar-historico-costos')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay históricos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
