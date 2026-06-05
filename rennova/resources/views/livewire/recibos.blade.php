<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-receipt"></i> Recibos</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Recibo', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-recibos', 'editar-recibos'])],
        ['value' => 'listado', 'label' => 'Listado de Recibos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-recibos', 'editar-recibos'])
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-{{ $recibo_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $recibo_id ? 'Editar Recibo' : 'Nuevo Recibo' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                            <select wire:model="id_empleado" class="form-select @error('id_empleado') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">{{ $emp->apellido }}, {{ $emp->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fecha Emisión <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_emision" class="form-control @error('fecha_emision') is-invalid @enderror">
                            @error('fecha_emision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Monto Bruto <span class="text-danger">*</span></label>
                            <input type="number" wire:model.live="monto_bruto" step="0.01" class="form-control @error('monto_bruto') is-invalid @enderror" placeholder="0.00">
                            @error('monto_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Descuentos</label>
                            <input type="number" wire:model.live="descuentos" step="0.01" class="form-control @error('descuentos') is-invalid @enderror" placeholder="0.00" value="0">
                            @error('descuentos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Monto Neto</label>
                            <input type="text" class="form-control bg-light" value="{{ isset($monto) ? '$' . number_format($monto, 2, ',', '.') : '' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea wire:model="observaciones" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Observaciones" rows="1"></textarea>
                            @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($recibo_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-recibos', 'editar-recibos'])
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ $recibo_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por empleado..." />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th>Bruto</th>
                                <th>Descuentos</th>
                                <th>Neto</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recibos as $recibo)
                                <tr wire:key="row-{{ $recibo->id_recibo }}">
                                    <td><span class="badge bg-secondary">{{ $recibo->id_recibo }}</span></td>
                                    <td><span class="fw-semibold">{{ $recibo->empleado?->apellido }}, {{ $recibo->empleado?->nombre }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</td>
                                    <td>${{ number_format($recibo->monto_bruto, 2, ',', '.') }}</td>
                                    <td>${{ number_format($recibo->descuentos ?? 0, 2, ',', '.') }}</td>
                                    <td><strong>${{ number_format($recibo->monto, 2, ',', '.') }}</strong></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <x-action-buttons
                                                editWireClick="editar({{ $recibo->id_recibo }})"
                                                deleteWireClick="eliminar({{ $recibo->id_recibo }})"
                                                deleteMessage="¿Está seguro de eliminar este recibo?"
                                                :canEdit="auth()->user()->can('editar-recibos')"
                                                :canDelete="auth()->user()->can('eliminar-recibos')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay recibos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
