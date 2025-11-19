<div class="container py-4"> <!-- INICIO: ÚNICO ELEMENTO RAÍZ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Mantenimientos</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="mantenimientosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'nuevo' ? 'active' : '' }}" id="nuevo-tab" type="button" role="tab" aria-controls="nuevo-mantenimiento" aria-selected="{{ $activeTab === 'nuevo' ? 'true' : 'false' }}" wire:click="$set('activeTab','nuevo')">
                <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'listado' ? 'active' : '' }}" id="listado-tab" type="button" role="tab" aria-controls="listado-mantenimientos" aria-selected="{{ $activeTab === 'listado' ? 'true' : 'false' }}" wire:click="$set('activeTab','listado')">
                <i class="bi bi-list-ul"></i> Listado de Mantenimientos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="mantenimientosTabContent">
        <!-- Pestaña 1: Nuevo Mantenimiento (Formulario) -->
        <div class="tab-pane fade {{ $activeTab === 'nuevo' ? 'show active' : '' }}" id="nuevo-mantenimiento" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-{{ $mantenimiento_id ? 'pencil-square' : 'plus-circle' }}"></i> {{ $mantenimiento_id ? 'Editar Orden' : 'Nueva Orden de Mantenimiento' }}</h5>
                </div>
                <div class="card-body">
                    <!-- Alerta para tipo preventivo -->
                    @if(count($kitPreventivo) > 0)
                        <div class="alert alert-info border-info" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-box-seam"></i> Kit de Mantenimiento Preventivo
                            </h6>
                            <small>Se utilizarán los siguientes insumos del kit configurado:</small>
                            <ul class="mb-0 mt-2 small">
                                @foreach($kitPreventivo as $item)
                                    <li>{{ $item['nombre'] ?? 'N/A' }}: {{ number_format($item['cantidad_requerida'], 2) }} unidades</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif($id_maquinaria && $id_tipo_mantenimiento)
                        @php
                            $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                            $esPreventivo = $tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo');
                        @endphp
                        @if($esPreventivo)
                            <div class="alert alert-warning border-warning" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Advertencia:</strong> No hay kit de mantenimiento preventivo configurado para esta maquinaria.
                                <a href="/kits-mantenimiento" class="alert-link">Configurar kit</a>
                            </div>
                        @endif
                    @endif

                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Maquinaria <span class="text-danger">*</span></label>
                                <select wire:model.live="id_maquinaria" class="form-select @error('id_maquinaria') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($maquinarias as $maquinaria)
                                        <option value="{{ $maquinaria->id_maquinaria }}">
                                            {{ $maquinaria->modelo }} - {{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }}
                                            @if($maquinaria->umbral_toneladas)
                                                ({{ number_format($maquinaria->toneladas_acumuladas ?? 0, 0) }}/{{ number_format($maquinaria->umbral_toneladas, 0) }} ton)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_maquinaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                                <select wire:model.live="id_tipo_mantenimiento" class="form-select @error('id_tipo_mantenimiento') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id_tipo_mantenimiento }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_tipo_mantenimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($id_tipo_mantenimiento)
                                    @php
                                        $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                                    @endphp
                                    @if($tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo'))
                                        <small class="text-info">
                                            <i class="bi bi-info-circle"></i> Se utilizará el kit de mantenimiento preventivo configurado
                                        </small>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror">
                                @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select wire:model="estado" class="form-select @error('estado') is-invalid @enderror">
                                    <option value="programado">Programado</option>
                                    <option value="en curso">En Curso</option>
                                </select>
                                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">La orden se completará desde el listado</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            @if($mantenimiento_id)
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ $mantenimiento_id ? 'Actualizar' : 'Crear Orden' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 		<!-- Pestaña 2: Listado de Mantenimientos (Tabla) -->
        <div class="tab-pane fade {{ $activeTab === 'listado' ? 'show active' : '' }}" id="listado-mantenimientos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Mantenimientos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por maquinaria, tipo, estado o costo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Maquinaria</th>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mantenimientos as $mantenimiento)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $mantenimiento->id_mantenimiento }}</span></td>
                                    <td class="fw-semibold">{{ $mantenimiento->maquinaria?->modelo ?? 'N/A' }}</td>
                                    <td>{{ $mantenimiento->tipoMantenimiento?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>${{ number_format($mantenimiento->costo_total, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $mantenimiento->estado == 'completado' ? 'success' : ($mantenimiento->estado == 'en curso' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($mantenimiento->estado) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php $isCompletado = strtolower(trim($mantenimiento->estado ?? '')) === 'completado'; @endphp
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-success" wire:click.prevent="abrirModalCompletar({{ $mantenimiento->id_mantenimiento }})" title="Completar" @if($isCompletado) disabled @endif>
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" wire:click="editar({{ $mantenimiento->id_mantenimiento }})" title="Editar" @if($isCompletado) disabled @endif>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" wire:click="eliminar({{ $mantenimiento->id_mantenimiento }})" onclick="return confirm('¿Está seguro de eliminar este mantenimiento?')" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mb-0 mt-2">No hay mantenimientos registrados.</p>
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

    <!-- ... (Aquí estaba el </div> que causaba el error) ... -->
    <!-- Todo lo que sigue (CSS, Modal, Script) AHORA ESTÁ DENTRO del div principal -->

    @once
        <style>
            .lw-modal-overlay {
                position: fixed;
                inset: 0;
                background-color: rgba(15, 23, 42, 0.6);
                z-index: 2050;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 2rem 1rem;
                overflow-y: auto;
            }
            .lw-modal-card {
                width: min(900px, 100%);
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
                overflow: hidden;
            }
            .lw-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.5rem;
                background: #198754;
                color: #fff;
            }
            .lw-modal-body { padding: 1.5rem; }
            .lw-modal-footer {
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: .75rem;
                background: #f8f9fa;
            }
            .lw-close {
                background: transparent;
                border: none;
                color: inherit;
                font-size: 1.25rem;
                line-height: 1;
                cursor: pointer;
            }
        </style>
    @endonce

    @if($mostrarModalCompletar)
        <div class="lw-modal-overlay" wire:key="modal-overlay">
            <div class="lw-modal-card" wire:key="modal-card-{{ $orden_completar_id }}">
                <div class="lw-modal-header">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Completar Orden de Mantenimiento</h5>
                    <button type="button" class="lw-close" wire:click="cerrarModalCompletar" aria-label="Cerrar">&times;</button>
                </div>
                <div class="lw-modal-body">
                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @error('general')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @enderror

                    <div class="alert alert-info">
                        <strong>Orden #{{ $orden_completar_info['id'] ?? 'N/A' }}</strong><br>
                        <strong>Maquinaria:</strong> {{ $orden_completar_info['maquinaria'] ?? 'N/A' }}<br>
                        <strong>Tipo:</strong> {{ $orden_completar_info['tipo'] ?? 'N/A' }}<br>
                        <strong>Fecha Inicio:</strong> {{ isset($orden_completar_info['fecha_inicio']) ? \Carbon\Carbon::parse($orden_completar_info['fecha_inicio'])->format('d/m/Y') : 'N/A' }}
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Finalización <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_fin_completar" class="form-control @error('fecha_fin_completar') is-invalid @enderror">
                            @error('fecha_fin_completar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Costo Total (opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" wire:model="costo_total_completar" step="0.01" class="form-control @error('costo_total_completar') is-invalid @enderror" placeholder="0.00">
                            </div>
                            @error('costo_total_completar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Se sumará automáticamente el costo de los insumos</small>
                        </div>
                    </div>

                    @if($orden_es_correctivo)
                        <hr>
                        <h6 class="mb-3"><i class="bi bi-box-seam"></i> Insumos Utilizados (Opcional)</h6>

                        @foreach($insumos_usados as $index => $insumo)
                            <div class="row g-2 mb-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small">Insumo</label>
                                    <select wire:model="insumos_usados.{{ $index }}.id_insumo" class="form-select form-select-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach(\App\Models\Insumo::orderBy('nombre')->get() as $ins)
                                            <option value="{{ $ins->id_insumo }}">{{ $ins->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Cantidad</label>
                                    <input type="number" wire:model="insumos_usados.{{ $index }}.cantidad" step="0.01" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Precio Unit.</label>
                                    <input type="number" wire:model="insumos_usados.{{ $index }}.precio_unitario" step="0.01" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                                <div class="col-md-1 text-end">
                                    @if($index > 0)
                                        <button type="button" wire:click="eliminarInsumo({{ $index }})" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <button type="button" wire:click="agregarInsumo" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Agregar Insumo
                        </button>
                    @endif
                </div>
                <div class="lw-modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cerrarModalCompletar">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" wire:click="completarOrden" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="completarOrden">
                            <i class="bi bi-check-circle"></i> Completar Orden
                        </span>
                        <span wire:loading wire:target="completarOrden">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript: auto-ocultar alertas + logs de depuración -->
    <script>
        // Auto-ocultar mensaje de éxito después de 3 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                    alert.close();
                }, 3000);
            }
        });

        // Log: al abrir el modal desde Livewire
        window.addEventListener('modal-completar-opened', (e) => {
            console.log('[Livewire] Modal Completar abierto para ID:', e.detail?.id);
            // Opcional: hacer scroll al inicio para evitar que el modal quede fuera de vista
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</div> <!-- FIN: ÚNICO ELEMENTO RAÍZ -->