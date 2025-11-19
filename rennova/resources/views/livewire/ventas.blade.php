<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><i class="bi bi-receipt"></i> Ventas</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pestañas controladas por Livewire --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $tab_activo === 'nuevo' ? 'active' : '' }}" 
                    type="button" 
                    wire:click="$set('tab_activo','nuevo')">
                <i class="bi bi-plus-circle"></i> Nueva Venta
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $tab_activo === 'historial' ? 'active' : '' }}" 
                    type="button" 
                    wire:click="$set('tab_activo','historial')">
                <i class="bi bi-list-ul"></i> Historial de Ventas
            </button>
        </li>
    </ul>

    @if($tab_activo === 'nuevo')
    {{-- Pestaña Nueva Venta --}}
    <div>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <strong>Buscar cargas pendientes</strong>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="buscarCargasPendientes" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Cliente</label>
                        <select wire:model="id_cliente" class="form-select">
                            <option value="">Seleccione cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" wire:model="fecha_desde" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" wire:model="fecha_hasta" class="form-control">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (!empty($detalle_cargas))
            <div class="card mt-3">
                <div class="card-header">Resultados ({{ count($detalle_cargas) }})</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Ticket</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Peso Neto (kg)</th>
                                    <th class="text-end">Precio (por tn)</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalle_cargas as $c)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($c['fecha_carga'])->format('d/m/Y') }}</td>
                                        <td>{{ $c['ticket'] }}</td>
                                        <td>{{ $c['categoria'] }}</td>
                                        <td class="text-end">{{ number_format($c['peso_kg'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($c['precio_unitario'], 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($c['subtotal'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th class="text-end">{{ number_format($total_venta, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <form wire:submit.prevent="guardarVenta">
                        <div class="mb-3">
                            <label class="form-label">Observaciones (opcional)</label>
                            <textarea wire:model="observaciones" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" wire:click="limpiar" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
    @elseif($tab_activo === 'historial')
    {{-- Pestaña Historial --}}
    <div>
        <div class="card">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <strong>Historial de Ventas</strong>
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" wire:model.live="busqueda" class="form-control form-control-sm" placeholder="Buscar...">
                    <button class="btn btn-sm btn-light" wire:click="$set('busqueda', '')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID Recibo</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-end">Monto</th>
                                <th>Estado</th>
                                <th class="text-center">Cargas</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $venta)
                                <tr>
                                    <td>{{ $venta->id_recibo }}</td>
                                    <td>{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                                    <td>{{ $venta->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="text-end">${{ number_format($venta->monto, 2, ',', '.') }}</td>
                                    <td>
                                        @if($venta->activo)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-danger">Baja</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $venta->cargas->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary" wire:click="verDetalle({{ $venta->id_recibo }})">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mb-0">No hay ventas registradas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Detalles --}}
    @if($mostrar_modal && $venta_seleccionada)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-receipt"></i> Detalle de Venta #{{ $venta_seleccionada->id_recibo }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong>Cliente:</strong><br>
                                {{ $venta_seleccionada->cliente->razon_social ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Fecha:</strong><br>
                                {{ \Carbon\Carbon::parse($venta_seleccionada->fecha_emision)->format('d/m/Y') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Estado:</strong><br>
                                @if($venta_seleccionada->activo)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-danger">Dada de Baja</span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                @if($modo_edicion)
                                    <label><strong>Monto:</strong></label>
                                    <input type="number" wire:model="monto_edicion" step="0.01" class="form-control form-control-sm">
                                @else
                                    <strong>Monto Total:</strong><br>
                                    ${{ number_format($venta_seleccionada->monto, 2, ',', '.') }}
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <strong>Observaciones:</strong>
                            @if($modo_edicion)
                                <textarea wire:model="obs_edicion" class="form-control" rows="2"></textarea>
                            @else
                                <p class="text-muted mb-0">{{ $venta_seleccionada->observaciones ?: 'Sin observaciones' }}</p>
                            @endif
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Cargas Asociadas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Ticket</th>
                                        <th>Fecha</th>
                                        <th>Categoría</th>
                                        <th class="text-end">Peso (kg)</th>
                                        <th class="text-end">Ton</th>
                                        <th class="text-end">Precio/tn</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detalle_venta as $det)
                                        <tr>
                                            <td>{{ $det['ticket'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($det['fecha_carga'])->format('d/m/Y') }}</td>
                                            <td>{{ $det['categoria'] }}</td>
                                            <td class="text-end">{{ number_format($det['peso_kg'], 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($det['peso_toneladas'], 3, ',', '.') }}</td>
                                            <td class="text-end">${{ number_format($det['precio_unitario'], 2, ',', '.') }}</td>
                                            <td class="text-end">${{ number_format($det['subtotal'], 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if($modo_edicion)
                            <button type="button" class="btn btn-secondary" wire:click="cancelarEdicion">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-success" wire:click="guardarEdicion">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                                <i class="bi bi-x-circle"></i> Cerrar
                            </button>
                            @if($venta_seleccionada->activo)
                                <button type="button" class="btn btn-warning" wire:click="activarEdicion">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button type="button" class="btn btn-danger" wire:click="darDeBaja({{ $venta_seleccionada->id_recibo }})" onclick="return confirm('¿Está seguro de dar de baja esta venta?')">
                                    <i class="bi bi-trash"></i> Dar de Baja
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
