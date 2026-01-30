<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-clipboard-check"></i> Partes Diarios</h1>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="partesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-parte" type="button" role="tab" aria-controls="nuevo-parte" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Parte Diario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-partes" type="button" role="tab" aria-controls="listado-partes" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Partes Diarios
            </button>
        </li>
    </ul>

    <div class="tab-content" id="partesTabContent">
        <!-- Pestaña 1: Nuevo Parte Diario -->
        <div class="tab-pane fade show active" id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab">
            
            <!-- SECCIÓN 1: Datos Maestros -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $parte_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario' }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Fila 1: Lote, Fecha, Día Caído -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select wire:model.live="id_lote" class="form-select @error('id_lote') is-invalid @enderror">
                                <option value="">Seleccione un lote...</option>
                                @foreach($this->lotes as $lote)
                                    <option value="{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="id_lote" class="text-muted small mt-1">
                                <i class="bi bi-arrow-repeat"></i> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" 
                                   wire:model="fecha" 
                                   max="{{ date('Y-m-d') }}" 
                                   min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                   class="form-control @error('fecha') is-invalid @enderror">
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Día Caído</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="diaCaidoSwitch" wire:model.live="es_dia_caido">
                                <label class="form-check-label" for="diaCaidoSwitch">
                                    <span class="badge {{ $es_dia_caido ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                        {{ $es_dia_caido ? 'SÍ - Jornal' : 'NO - Destajo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Observaciones -->
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Registro de Producción (Si NO es día caído) -->
            @if(!$es_dia_caido)
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Registro de Producción</h5>
                    </div>
                    <div class="card-body">
                        <!-- Errores generales de validación de carga -->
                        @if ($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias'))
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Errores en la carga:</strong>
                                <ul class="mb-0 mt-2">
                                    @error('carga_id_categoria_madera') <li>{{ $message }}</li> @enderror
                                    @error('carga_ticket') <li>{{ $message }}</li> @enderror
                                    @error('carga_peso_bruto') <li>{{ $message }}</li> @enderror
                                    @error('carga_tara') <li>{{ $message }}</li> @enderror
                                    @error('carga_peso_neto') <li>{{ $message }}</li> @enderror
                                    @error('carga_id_chofer') <li>{{ $message }}</li> @enderror
                                    @error('carga_destino') <li>{{ $message }}</li> @enderror
                                    @error('carga_empleados') <li>{{ $message }}</li> @enderror
                                    @error('carga_maquinarias') <li>{{ $message }}</li> @enderror
                                </ul>
                            </div>
                        @endif

                        <!-- Formulario agregar carga -->
                        <div class="border rounded p-3 mb-3 bg-light" x-data="{
                            bruto: @entangle('carga_peso_bruto').live,
                            tara: @entangle('carga_tara').live,
                            get neto() {
                                return (parseFloat(this.bruto) || 0) - (parseFloat(this.tara) || 0);
                            }
                        }" x-init="$watch('neto', value => $wire.set('carga_peso_neto', value))">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle-fill"></i> Registrar Carga</h6>
                            
                            <!-- Fila 1: Categoría, Maquinaria -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="form-select @error('carga_id_categoria_madera') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($this->categoriasMadera as $cat)
                                            <option value="{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('carga_id_categoria_madera') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Maquinarias <span class="text-danger">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm"></span> Cargando maquinarias...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border rounded p-2 @error('carga_maquinarias') border-danger @enderror" style="max-height: 120px; overflow-y: auto;">
                                            @forelse($this->maquinariasFiltrada as $maq)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $maq->id_maquinaria }}" id="maq-{{ $maq->id_maquinaria }}" wire:model="carga_maquinarias">
                                                    <label class="form-check-label" for="maq-{{ $maq->id_maquinaria }}">
                                                        {{ $maq->modelo }} - <small class="text-muted">{{ $maq->tipoMaquinaria->nombre ?? 'Sin tipo' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="text-muted small p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver maquinarias disponibles
                                                </div>
                                            @endforelse
                                        </div>
                                        @error('carga_maquinarias') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente con búsqueda -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chofer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="form-control mb-1 @error('carga_id_chofer') is-invalid @enderror" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="form-select" size="3">
                                        @forelse($this->choferesFiltrados as $chofer)
                                            <option value="{{ $chofer->id_chofer }}">{{ $chofer->apellido }}, {{ $chofer->nombre }}</option>
                                        @empty
                                            <option value="" disabled>No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_id_chofer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Destino (Cliente) <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="form-control mb-1 @error('carga_destino') is-invalid @enderror" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="form-select" size="3">
                                        @forelse($this->clientesFiltrados as $cliente)
                                            <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                        @empty
                                            <option value="" disabled>No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_destino') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Fila 3: Pesajes con cálculo reactivo Alpine -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Ticket <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="carga_ticket" class="form-control @error('carga_ticket') is-invalid @enderror" placeholder="TKT-12345">
                                    @error('carga_ticket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Bruto (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" x-model.number="bruto" step="0.1" min="0" class="form-control @error('carga_peso_bruto') is-invalid @enderror" placeholder="0.00">
                                    @error('carga_peso_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tara (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" x-model.number="tara" step="0.1" min="0" class="form-control @error('carga_tara') is-invalid @enderror" placeholder="0.00">
                                    @error('carga_tara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Neto (Ton) <span class="text-info">Calculado</span></label>
                                    <input type="text" x-text="neto.toFixed(2)" class="form-control bg-light" readonly>
                                    @error('carga_peso_neto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Fila 4: Empleados -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Empleados <span class="text-danger">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm"></span> Cargando empleados...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border rounded p-2 @error('carga_empleados') border-danger @enderror" style="max-height: 150px; overflow-y: auto;">
                                            @forelse($this->empleadosFiltrados as $emp)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $emp->id_empleado }}" id="emp-{{ $emp->id_empleado }}" wire:model="carga_empleados">
                                                    <label class="form-check-label" for="emp-{{ $emp->id_empleado }}">
                                                        {{ $emp->apellido }}, {{ $emp->nombre }} - <small class="text-muted">{{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="text-muted small p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver empleados disponibles
                                                </div>
                                            @endforelse
                                        </div>
                                        @error('carga_empleados') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" wire:click.prevent="agregarCarga" class="btn btn-primary" wire:loading.attr="disabled" wire:target="agregarCarga">
                                    <span wire:loading.remove wire:target="agregarCarga"><i class="bi bi-plus-circle"></i> Agregar Carga</span>
                                    <span wire:loading wire:target="agregarCarga"><span class="spinner-border spinner-border-sm"></span> Agregando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Listado cargas -->
                        @if(count($cargas) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Categoría</th>
                                            <th>Neto (Ton)</th>
                                            <th>Chofer</th>
                                            <th>Destino</th>
                                            <th>Empleados</th>
                                            <th>Maquinarias</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cargas as $index => $carga)
                                            <tr>
                                                <td><span class="badge bg-secondary">{{ $carga['ticket'] }}</span></td>
                                                <td>
                                                    @php
                                                        $cat = $this->categoriasMadera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                                    @endphp
                                                    {{ $cat->nombre ?? '-' }}
                                                </td>
                                                <td><strong>{{ number_format($carga['peso_neto'], 2) }}</strong></td>
                                                <td>
                                                    @php
                                                        $chofer = $this->choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                                    @endphp
                                                    {{ $chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-' }}
                                                </td>
                                                <td>{{ $carga['destino_nombre'] ?? '-' }}</td>
                                                <td><small>{{ count($carga['empleados'] ?? []) }} emp</small></td>
                                                <td><small>{{ count($carga['maquinarias'] ?? []) }} maq</small></td>
                                                <td class="text-center">
                                                    <button type="button" wire:click.prevent="eliminarCarga({{ $index }})" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total:</td>
                                            <td class="text-center fw-bold text-primary">{{ number_format($total_toneladas, 2) }} Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Sin cargas registradas. Agregue al menos una carga.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECCIÓN 3: Jornales (Si ES día caído) -->
            @if($es_dia_caido)
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Asignación de Jornales</h5>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-person-plus-fill"></i> Agregar Empleado al Jornal</h6>
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="form-select @error('jornal_id_empleado') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($this->empleadosFiltrados as $emp)
                                            <option value="{{ $emp->id_empleado }}">
                                                {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                                @if(isset($jornal_por_empleado[$emp->id_empleado]))
                                                    (Jornal: ${{ number_format($jornal_por_empleado[$emp->id_empleado], 2) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jornal_id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" wire:click.prevent="agregarJornal" class="btn btn-warning w-100" wire:loading.attr="disabled" wire:target="agregarJornal">
                                        <span wire:loading.remove wire:target="agregarJornal"><i class="bi bi-plus-circle"></i> Agregar</span>
                                        <span wire:loading wire:target="agregarJornal"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(count($jornales) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Rol</th>
                                            <th>Jornal</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jornales as $index => $jornal)
                                            <tr>
                                                <td>{{ $jornal['nombre_completo'] ?? '-' }}</td>
                                                <td><span class="badge bg-secondary">{{ $jornal['rol'] ?? '-' }}</span></td>
                                                <td class="text-success fw-bold">${{ number_format($jornal['jornal_diario'] ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" wire:click.prevent="eliminarJornal({{ $index }})" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total Jornales:</td>
                                            <td class="text-center fw-bold text-warning">${{ number_format(array_sum(array_column($jornales, 'jornal_diario')), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle"></i> Sin empleados asignados. Agregue al menos un empleado.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECCIÓN 4: Movimientos de Insumos -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Movimientos de Insumos</h5>
                </div>
                <div class="card-body">
                    <div id="alertaMovimiento"></div>
                    
                    <!-- Errores generales de validación de movimiento -->
                    @if ($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo'))
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Errores en el movimiento:</strong>
                            <ul class="mb-0 mt-2">
                                @error('movimiento_id_insumo') <li>{{ $message }}</li> @enderror
                                @error('movimiento_cantidad') <li>{{ $message }}</li> @enderror
                                @error('movimiento_motivo') <li>{{ $message }}</li> @enderror
                            </ul>
                        </div>
                    @endif
                    
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-box-arrow-right"></i> Registrar Consumo</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                <select wire:model.live="movimiento_id_insumo" class="form-select @error('movimiento_id_insumo') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($this->insumos as $insumo)
                                        <option value="{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                                    @endforeach
                                </select>
                                @if($stock_disponible_insumo !== null)
                                    <small class="text-muted mt-1 d-block">
                                        Stock disponible: <strong class="{{ $stock_disponible_insumo > 0 ? 'text-success' : 'text-danger' }}">{{ $stock_disponible_insumo }}</strong>
                                    </small>
                                @endif
                                @error('movimiento_id_insumo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" wire:model="movimiento_cantidad" step="0.1" min="0" 
                                       @if($stock_disponible_insumo !== null) max="{{ $stock_disponible_insumo }}" @endif
                                       class="form-control @error('movimiento_cantidad') is-invalid @enderror" placeholder="0.00">
                                @error('movimiento_cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                                <select wire:model="movimiento_motivo" class="form-select @error('movimiento_motivo') is-invalid @enderror">
                                    <option value="Producción">Producción</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                    <option value="Varios">Varios</option>
                                </select>
                                @error('movimiento_motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" wire:click.prevent="agregarMovimiento" class="btn btn-success w-100" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                                    <span wire:loading.remove wire:target="agregarMovimiento"><i class="bi bi-plus-circle"></i> Agregar</span>
                                    <span wire:loading wire:target="agregarMovimiento"><span class="spinner-border spinner-border-sm"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if(count($movimientos) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movimientos as $index => $mov)
                                        <tr>
                                            <td><strong>{{ $mov['nombre_insumo'] }}</strong></td>
                                            <td>{{ number_format($mov['cantidad'], 2) }} {{ $mov['unidad'] ?? '' }}</td>
                                            <td><span class="badge bg-secondary">{{ $mov['motivo'] }}</span></td>
                                            <td><small>{{ $mov['observaciones'] ?? '-' }}</small></td>
                                            <td class="text-center">
                                                <button type="button" wire:click.prevent="eliminarMovimiento({{ $index }})" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle"></i> Sin movimientos registrados. Esta sección es opcional.
                        </div>
                    @endif
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" wire:click.prevent="cancelarEdicion" class="btn btn-secondary btn-lg" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="button" wire:click.prevent="guardar" class="btn btn-primary btn-lg" wire:loading.attr="disabled" wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar"><i class="bi bi-check-circle"></i> Guardar Parte Diario</span>
                            <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm"></span> Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado -->
        <div class="tab-pane fade" id="listado-partes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Partes Diarios Registrados</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Buscar por Propietario</label>
                            <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control" placeholder="Ej: Juan Pérez...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Buscar por Fecha</label>
                            <input type="date" wire:model.live="busqueda_fecha" class="form-control">
                        </div>
                    </div>
                    
                    @if($partes && count($partes) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Lote</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($partes as $parte)
                                        <tr>
                                            <td><span class="badge bg-secondary">#{{ $parte->id_parte_diario }}</span></td>
                                            <td>{{ $parte->lote?->propietario ?? '-' }}</td>
                                            <td>{{ $parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-' }}</td>
                                            <td>
                                                @if($parte->es_dia_caido)
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-calendar-x"></i> Día Caído</span>
                                                @else
                                                    <span class="badge bg-success"><i class="bi bi-truck"></i> Producción</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-' }}</small></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" wire:click.prevent="editar({{ $parte->id_parte_diario }})" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" wire:click.prevent="eliminar({{ $parte->id_parte_diario }})" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @isset($partes)
                            <div class="mt-3" wire:key="pagination-{{ now() }}">
                                {{ $partes->links() }}
                            </div>
                        @endisset
                    @else
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mb-0 mt-2">No hay partes diarios registrados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function mostrarTabPartes(tabId) {
        const nuevoTab = document.getElementById('nuevo-tab');
        const listadoTab = document.getElementById('listado-tab');
        const nuevoPane = document.getElementById('nuevo-parte');
        const listadoPane = document.getElementById('listado-partes');

        const activarTab = (tabButton, tabPane) => {
            [nuevoTab, listadoTab].forEach(btn => btn?.classList.remove('active'));
            [nuevoPane, listadoPane].forEach(pane => pane?.classList.remove('show', 'active'));
            tabButton?.classList.add('active');
            tabPane?.classList.add('show', 'active');
        };

        if (tabId === 'listado') {
            activarTab(listadoTab, listadoPane);
        } else {
            activarTab(nuevoTab, nuevoPane);
        }
    }

    // Evento para cambiar a la pestaña de listado después de guardar
    document.addEventListener('livewire:init', () => {
        Livewire.on('parteDiarioGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            if(window.bootstrap?.Tab && listadoTab) {
                const tabInstance = new bootstrap.Tab(listadoTab);
                tabInstance.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                mostrarTabPartes('listado');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        Livewire.on('parteDiarioCancelado', () => {
            const listadoTab = document.getElementById('listado-tab');
            if(window.bootstrap?.Tab && listadoTab) {
                const tabInstance = new bootstrap.Tab(listadoTab);
                tabInstance.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                mostrarTabPartes('listado');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        // Evento para cambiar a la pestaña de formulario al editar
        Livewire.on('editandoParteDiario', () => {
            const nuevoTab = document.getElementById('nuevo-tab');
            if(window.bootstrap?.Tab && nuevoTab) {
                const tabInstance = new bootstrap.Tab(nuevoTab);
                tabInstance.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                mostrarTabPartes('nuevo');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const listadoTab = document.getElementById('listado-tab');
        const nuevoTab = document.getElementById('nuevo-tab');

        listadoTab?.addEventListener('click', (e) => {
            if (!window.bootstrap?.Tab) {
                e.preventDefault();
                mostrarTabPartes('listado');
            }
        });

        nuevoTab?.addEventListener('click', (e) => {
            if (!window.bootstrap?.Tab) {
                e.preventDefault();
                mostrarTabPartes('nuevo');
            }
        });
    });
</script>
@endpush
