<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestión de Mantenimientos</h3>
        </div>
        <div class="card-body">
            
            {{-- Mensajes de feedback --}}
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $tab_activo === 'ordenes' ? 'active' : '' }}" 
                       wire:click="cambiarTab('ordenes')" 
                       href="javascript:void(0)">
                        <i class="bi bi-clipboard-check"></i> Órdenes Activas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab_activo === 'completadas' ? 'active' : '' }}" 
                       wire:click="cambiarTab('completadas')" 
                       href="javascript:void(0)">
                        <i class="bi bi-check-circle"></i> Completadas
                    </a>
                </li>
            </ul>
            
            {{-- Filtros --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Maquinaria</label>
                    <select wire:model="filtro_maquinaria" class="form-select">
                        <option value="">Todas</option>
                        @foreach($maquinarias as $maq)
                            <option value="{{ $maq->id }}">
                                {{ $maq->modelo }} ({{ $maq->tipoMaquinaria->nombre }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label>Tipo</label>
                    <select wire:model="filtro_tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>
                </div>
                
                @if($tab_activo === 'ordenes')
                <div class="col-md-2">
                    <label>Estado</label>
                    <select wire:model="filtro_estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="programado">Programado</option>
                        <option value="en curso">En Curso</option>
                    </select>
                </div>
                @endif
                
                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date" wire:model="filtro_fecha_desde" class="form-control">
                </div>
                
                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date" wire:model="filtro_fecha_hasta" class="form-control">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="resetearFiltros" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
            
            {{-- Tabla de órdenes --}}
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Maquinaria</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Toneladas</th>
                            <th>Fecha Creación</th>
                            @if($tab_activo === 'completadas')
                                <th>Costo Total</th>
                                <th>Fecha Completado</th>
                            @endif
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordenes as $orden)
                        <tr>
                            <td>{{ $orden->id }}</td>
                            <td>
                                <strong>{{ $orden->maquinaria->modelo }}</strong><br>
                                <small class="text-muted">{{ $orden->maquinaria->tipoMaquinaria->nombre }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $orden->tipo_mantenimiento === 'preventivo' ? 'info' : 'warning' }}">
                                    {{ ucfirst($orden->tipo_mantenimiento) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($orden->estado) {
                                        'programado' => 'secondary',
                                        'en curso' => 'primary',
                                        'completado' => 'success',
                                        default => 'dark'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">
                                    {{ ucfirst($orden->estado) }}
                                </span>
                            </td>
                            <td>{{ number_format($orden->toneladas_snapshot ?? 0, 2) }}</td>
                            <td>{{ $orden->created_at->format('d/m/Y H:i') }}</td>
                            
                            @if($tab_activo === 'completadas')
                                <td>${{ number_format($orden->costo_total ?? 0, 2) }}</td>
                                <td>{{ $orden->fecha_completado ? $orden->fecha_completado->format('d/m/Y H:i') : '-' }}</td>
                            @endif
                            
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="verDetalle({{ $orden->id }})" 
                                            class="btn btn-info" 
                                            title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    @if($orden->estado === 'programado')
                                        <button wire:click="abrirModalAprobar({{ $orden->id }})" 
                                                class="btn btn-success" 
                                                title="Aprobar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif
                                    
                                    @if($orden->estado === 'en curso')
                                        <button wire:click="abrirModalCompletar({{ $orden->id }})" 
                                                class="btn btn-primary" 
                                                title="Completar">
                                            <i class="bi bi-flag-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="text-center text-muted">
                                No hay órdenes para mostrar
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Modal Aprobar --}}
    @if($modal_aprobar && $orden_seleccionada)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Aprobar Orden de Mantenimiento #{{ $orden_seleccionada->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalAprobar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Maquinaria:</strong> {{ $orden_seleccionada->maquinaria->modelo }}
                        </div>
                        <div class="col-md-6">
                            <strong>Tipo:</strong> {{ ucfirst($orden_seleccionada->tipo_mantenimiento) }}
                        </div>
                    </div>
                    
                    @if($verificacion_stock)
                        <h6 class="mt-3">Verificación de Stock:</h6>
                        
                        @if($verificacion_stock['puede_aprobar'])
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 
                                Todos los insumos están disponibles
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Stock insuficiente para aprobar
                            </div>
                        @endif
                        
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Requerido</th>
                                    <th>Disponible</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($verificacion_stock['kit'] as $item)
                                <tr class="{{ in_array($item['insumo_id'], array_column($verificacion_stock['insuficientes'], 'insumo_id')) ? 'table-danger' : 'table-success' }}">
                                    <td>{{ $item['nombre'] }}</td>
                                    <td>{{ $item['cantidad_requerida'] }}</td>
                                    <td>{{ $item['stock_disponible'] }}</td>
                                    <td>
                                        @if($item['stock_disponible'] >= $item['cantidad_requerida'])
                                            <i class="bi bi-check-circle text-success"></i> OK
                                        @else
                                            <i class="bi bi-x-circle text-danger"></i> 
                                            Faltan {{ $item['cantidad_requerida'] - $item['stock_disponible'] }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalAprobar">
                        Cancelar
                    </button>
                    @if($verificacion_stock && $verificacion_stock['puede_aprobar'])
                        <button type="button" class="btn btn-success" wire:click="aprobarOrden">
                            <i class="bi bi-check-lg"></i> Aprobar Orden
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Modal Completar --}}
    @if($modal_completar && $orden_seleccionada)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Completar Mantenimiento #{{ $orden_seleccionada->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalCompletar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Maquinaria:</strong> {{ $orden_seleccionada->maquinaria->modelo }}
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong> {{ ucfirst($orden_seleccionada->tipo_mantenimiento) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Toneladas:</strong> {{ number_format($orden_seleccionada->toneladas_snapshot, 2) }}
                        </div>
                    </div>
                    
                    <h6>Insumos Utilizados:</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Stock Disponible</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insumos_usados as $index => $insumo)
                                <tr>
                                    <td>
                                        <select wire:model="insumos_usados.{{ $index }}.insumo_id" 
                                                wire:change="actualizarInsumo({{ $index }}, $event.target.value)"
                                                class="form-select form-select-sm"
                                                @if($insumo['es_obligatorio']) disabled @endif>
                                            <option value="">Seleccionar...</option>
                                            @foreach($insumos_disponibles as $ins)
                                                <option value="{{ $ins->id_insumo }}">{{ $ins->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error("insumos_usados.{$index}.insumo_id")
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" 
                                               wire:model="insumos_usados.{{ $index }}.cantidad" 
                                               class="form-control form-control-sm" 
                                               step="0.01" 
                                               min="0">
                                        @error("insumos_usados.{$index}.cantidad")
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td>
                                        @php
                                            $stockDispo = $insumo['stock_disponible'] ?? 0;
                                            $cantidad = $insumo['cantidad'] ?? 0;
                                        @endphp
                                        <span class="badge bg-{{ $stockDispo >= $cantidad ? 'success' : 'danger' }}">
                                            {{ number_format($stockDispo, 2) }}
                                        </span>
                                        @if($stockDispo < $cantidad && $cantidad > 0)
                                            <br><small class="text-danger">Faltan {{ number_format($cantidad - $stockDispo, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$insumo['es_obligatorio'])
                                            <button type="button" 
                                                    wire:click="eliminarInsumo({{ $index }})" 
                                                    class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-info">Requerido</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <button type="button" wire:click="agregarInsumo" class="btn btn-sm btn-outline-primary mb-3">
                        <i class="bi bi-plus-circle"></i> Agregar Insumo
                    </button>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Costo Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       wire:model="costo_mano_obra" 
                                       class="form-control" 
                                       step="0.01" 
                                       min="0">
                            </div>
                            @error('costo_mano_obra')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalCompletar">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="completarMantenimiento">
                        <i class="bi bi-flag-fill"></i> Completar Mantenimiento
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Modal Detalle --}}
    @if($modal_detalle && $detalle_orden)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalle Orden #{{ $detalle_orden->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalDetalle"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Maquinaria:</strong> {{ $detalle_orden->maquinaria->modelo }}<br>
                            <strong>Tipo Maquinaria:</strong> {{ $detalle_orden->maquinaria->tipoMaquinaria->nombre }}
                        </div>
                        <div class="col-md-6">
                            <strong>Tipo Mantenimiento:</strong> {{ ucfirst($detalle_orden->tipo_mantenimiento) }}<br>
                            <strong>Estado:</strong> 
                            <span class="badge bg-{{ $detalle_orden->estado === 'completado' ? 'success' : 'primary' }}">
                                {{ ucfirst($detalle_orden->estado) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Toneladas Snapshot:</strong> {{ number_format($detalle_orden->toneladas_snapshot, 2) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha Creación:</strong> {{ $detalle_orden->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    @if($detalle_orden->estado === 'completado')
                        <hr>
                        <h6>Insumos Utilizados:</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalle_orden->mantenimientoInsumos as $item)
                                <tr>
                                    <td>{{ $item->insumo->nombre }}</td>
                                    <td>{{ $item->cantidad }}</td>
                                    <td>${{ number_format($item->costo_unitario, 2) }}</td>
                                    <td>${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Costo Mano de Obra:</strong> ${{ number_format($detalle_orden->costo_mano_obra, 2) }}
                            </div>
                            <div class="col-md-6">
                                <strong>Costo Total:</strong> 
                                <span class="h5 text-primary">
                                    ${{ number_format($detalle_orden->costo_total, 2) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Fecha Completado:</strong> {{ $detalle_orden->fecha_completado->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endif
                    
                    @if($detalle_orden->descripcion)
                        <hr>
                        <strong>Descripción:</strong>
                        <p>{{ $detalle_orden->descripcion }}</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalDetalle">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
</div>
