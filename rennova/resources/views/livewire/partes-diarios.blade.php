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
        <!-- Pestaña 1: Nuevo Parte Diario (Formulario Maestro-Detalle) -->
        <div class="tab-pane fade show active" id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab">
            
            <!-- 1. TARJETA PRINCIPAL: Datos Maestros -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $parte_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select wire:model="id_lote" class="form-select @error('id_lote') is-invalid @enderror">
                                <option value="">Seleccione un lote...</option>
                                @foreach($lotes as $lote)
                                    <option value="{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha" class="form-control @error('fecha') is-invalid @enderror">
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Actividad Realizada <span class="text-danger">*</span></label>
                            <textarea wire:model="actividad_realizada" class="form-control @error('actividad_realizada') is-invalid @enderror" rows="2" placeholder="Descripción de la actividad realizada"></textarea>
                            @error('actividad_realizada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="esDiaCaido" wire:model.live="es_dia_caido">
                                <label class="form-check-label fw-semibold" for="esDiaCaido">
                                    <i class="bi bi-calendar-x"></i> ¿Día Caído?
                                    <span class="badge {{ $es_dia_caido ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                        {{ $es_dia_caido ? 'SÍ - Pago por Jornal' : 'NO - Pago por Destajo' }}
                                    </span>
                                </label>
                            </div>
                            <small class="text-muted">Si es día caído, se pagará jornal fijo. Si no, se paga por tonelada extraída.</small>
                        </div>
                    </div>

                    @if($es_dia_caido)
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Motivo del Día Caído <span class="text-danger">*</span></label>
                                <textarea wire:model="motivo_dia_caido" class="form-control @error('motivo_dia_caido') is-invalid @enderror" rows="3" placeholder="Explique el motivo del día caído (lluvia, falla mecánica, etc.)"></textarea>
                                @error('motivo_dia_caido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observaciones Generales</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales del parte diario"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2A. SECCIÓN CONDICIONAL: REGISTRO DE PRODUCCIÓN (Cargas) - Solo si NO es día caído -->
            @if(!$es_dia_caido)
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Registro de Producción (Cargas)</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulario para agregar carga -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle-fill"></i> Registrar Nueva Carga</h6>
                            
                            <!-- Fila 1: Lote, Categoría, Maquinaria -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                                    <select disabled class="form-select" title="El lote se establece en los datos maestros del Parte Diario">
                                        <option value="">{{ $id_lote ? $lotes->firstWhere('id_lote', $id_lote)->propietario . ' - ' . $lotes->firstWhere('id_lote', $id_lote)->ubicacion : 'Seleccione lote arriba' }}</option>
                                    </select>
                                    <small class="text-muted">El lote se configura en la sección principal</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Categoría Madera <span class="text-danger">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="form-select @error('carga_id_categoria_madera') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($categorias_madera as $cat)
                                            <option value="{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('carga_id_categoria_madera') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Maquinarias Utilizadas
                                        @if(!empty($maquinarias_asignadas_ids) && count($maquinarias_asignadas_ids) === 1)
                                            <span class="badge bg-success ms-1" title="Auto-seleccionada (única asignada al lote)">
                                                <i class="bi bi-check-circle"></i> Auto
                                            </span>
                                        @endif
                                    </label>
                                    <div class="border rounded p-2 @error('carga_maquinarias') border-danger @enderror" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                                        @foreach($this->maquinariasFiltrada as $maq)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $maq->id_maquinaria }}" id="maq-{{ $maq->id_maquinaria }}" wire:model="carga_maquinarias">
                                                <label class="form-check-label" for="maq-{{ $maq->id_maquinaria }}">
                                                    {{ $maq->modelo }} - <small class="text-muted">{{ $maq->tipoMaquinaria->nombre ?? 'Sin tipo' }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('carga_maquinarias') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @if(empty($maquinarias_asignadas_ids))
                                        <small class="text-muted"><i class="bi bi-info-circle"></i> Sin lote seleccionado. Se muestran todas las maquinarias.</small>
                                    @elseif(count($maquinarias_asignadas_ids) > 0)
                                        <small class="text-success"><i class="bi bi-funnel"></i> Mostrando solo maquinarias asignadas al lote ({{ count($maquinarias_asignadas_ids) }})</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chofer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="busqueda_chofer" class="form-control mb-1" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="form-select @error('carga_id_chofer') is-invalid @enderror" size="3" style="height: auto;">
                                        @forelse($this->choferesFiltrados as $chofer)
                                            <option value="{{ $chofer->id_chofer }}">{{ $chofer->apellido }}, {{ $chofer->nombre }}</option>
                                        @empty
                                            <option value="">No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_id_chofer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Destino (Cliente) <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="busqueda_cliente" class="form-control mb-1" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="form-select @error('carga_destino') is-invalid @enderror" size="3" style="height: auto;">
                                        @forelse($this->clientesFiltrados as $cliente)
                                            <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                        @empty
                                            <option value="">No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_destino') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Fila 3: Pesajes y Tickets -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Ticket <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="carga_ticket" class="form-control @error('carga_ticket') is-invalid @enderror" placeholder="Ej: TKT-12345">
                                    @error('carga_ticket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Peso Bruto (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="carga_peso_bruto" step="0.01" class="form-control @error('carga_peso_bruto') is-invalid @enderror" placeholder="0.00">
                                    @error('carga_peso_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tara (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="carga_tara" step="0.01" class="form-control @error('carga_tara') is-invalid @enderror" placeholder="0.00">
                                    @error('carga_tara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Peso Neto (Ton) <span class="text-info">Calculado</span></label>
                                    <input type="text" value="{{ $carga_peso_neto ? number_format($carga_peso_neto, 2) : '0.00' }}" class="form-control bg-light" readonly>
                                    <small class="text-muted">Peso Bruto - Tara</small>
                                </div>
                            </div>
                            
                            <!-- Sección de Personal Involucrado -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-12">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="bi bi-people-fill"></i> Personal Involucrado</h6>
                                    <label class="form-label fw-semibold">Empleados que participaron en esta carga <span class="text-danger">*</span></label>
                                    <div class="border rounded p-2 @error('carga_empleados') border-danger @enderror" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                                        @foreach($this->empleadosFiltrados as $emp)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $emp->id_empleado }}" id="emp-{{ $emp->id_empleado }}" wire:model="carga_empleados">
                                                <label class="form-check-label" for="emp-{{ $emp->id_empleado }}">
                                                    {{ $emp->apellido }}, {{ $emp->nombre }} - <small class="text-muted">{{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('carga_empleados') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted d-block mt-1"><i class="bi bi-info-circle"></i> Seleccione todos los empleados que trabajaron en la extracción de esta carga. El pago se calculará por destajo (toneladas extraídas).</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" wire:click="agregarCarga" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Agregar Carga
                                </button>
                            </div>
                        </div>

                        <!-- Listado de cargas registradas -->
                        @if(count($cargas) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Categoría</th>
                                            <th>Peso Neto (Ton)</th>
                                            <th>Chofer</th>
                                            <th>Destino (Cliente)</th>
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
                                                        $cat = $categorias_madera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                                    @endphp
                                                    {{ $cat->nombre ?? '-' }}
                                                </td>
                                                <td><strong>{{ number_format($carga['peso_neto'], 2) }}</strong> <small class="text-muted">(B: {{ number_format($carga['peso_bruto'], 2) }} - T: {{ number_format($carga['tara'], 2) }})</small></td>
                                                <td>
                                                    @php
                                                        $chofer = $choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                                    @endphp
                                                    {{ $chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-' }}
                                                </td>
                                                <td>
                                                    {{ $carga['destino_nombre'] ?? '-' }}
                                                </td>
                                                <td>
                                                    <small>
                                                        @foreach($carga['empleados'] as $emp_id)
                                                            @php
                                                                $emp = $empleados->firstWhere('id_empleado', $emp_id);
                                                            @endphp
                                                            @if($emp)
                                                                <span class="badge bg-info">{{ $emp->apellido }}</span>
                                                            @endif
                                                        @endforeach
                                                    </small>
                                                </td>
                                                <td>
                                                    <small>
                                                        @if(isset($carga['maquinarias']))
                                                            @foreach($carga['maquinarias'] as $maq_id)
                                                                @php
                                                                    $m = $maquinarias->firstWhere('id_maquinaria', $maq_id);
                                                                @endphp
                                                                @if($m)
                                                                    <span class="badge bg-secondary">{{ $m->modelo }}</span>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarCarga({{ $index }})" class="btn btn-sm btn-outline-danger" title="Eliminar carga">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total de Toneladas Extraídas Hoy:</td>
                                            <td class="text-center fw-bold text-primary fs-5">{{ number_format($total_toneladas, 2) }} Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No hay cargas registradas. Agregue al menos una carga para continuar.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 2B. SECCIÓN CONDICIONAL: ASIGNACIÓN DE JORNAL (Día Caído) - Solo si ES día caído -->
            @if($es_dia_caido)
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Asignación de Jornal por Día Caído</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulario para agregar empleado al jornal -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-person-plus-fill"></i> Añadir Empleado</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="form-select">
                                        <option value="">Seleccione un empleado...</option>
                                        @foreach($this->empleadosFiltrados as $emp)
                                            <option value="{{ $emp->id_empleado }}">
                                                {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }} 
                                                (Jornal: ${{ number_format($jornal_por_empleado[$emp->id_empleado] ?? ($emp->rolLaboral->jornal_diario ?? 0), 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <input type="text" wire:model="jornal_observaciones" class="form-control" placeholder="Obs. de pago">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" wire:click="agregarJornal" class="btn btn-warning">
                                    <i class="bi bi-plus-circle"></i> Agregar Empleado
                                </button>
                            </div>
                        </div>

                        <!-- Listado de jornales -->
                        @if(count($jornales) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Rol Laboral</th>
                                            <th>Valor Jornal Diario</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jornales as $index => $jornal)
                                            <tr>
                                                <td>{{ $jornal['nombre_completo'] }}</td>
                                                <td><span class="badge bg-secondary">{{ $jornal['rol'] }}</span></td>
                                                <td class="text-end fw-bold text-success">${{ number_format($jornal['jornal_diario'], 2) }}</td>
                                                <td>{{ $jornal['observaciones'] ?? '-' }}</td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarJornal({{ $index }})" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> No hay empleados asignados al jornal. Agregue al menos un empleado.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 3. SECCIÓN FIJA: MOVIMIENTO DE INSUMOS -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Movimiento de Insumos</h5>
                </div>
                <div class="card-body">
                    <!-- Alertas de feedback -->
                    <div id="alertaMovimiento"></div>
                    
                    <!-- Formulario para agregar movimiento -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-box-arrow-right"></i> Registrar Consumo de Insumos</h6>
                        <div class="alert alert-info alert-sm mb-3">
                            <i class="bi bi-info-circle"></i> Los insumos consumidos se descontarán automáticamente del stock usando FIFO. Para entradas, use <strong>Gestión de Stock</strong>.
                        </div>
                        
                        <!-- Fila 1: Identificación y Cantidad -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                <select wire:model.live="movimiento_id_insumo" class="form-select @error('movimiento_id_insumo') is-invalid @enderror">
                                    <option value="">Seleccione un insumo...</option>
                                    @foreach($insumos as $insumo)
                                        <option value="{{ $insumo->id_insumo }}">
                                            {{ $insumo->nombre }}
                                            @if($insumo->unidadMedida)
                                                ({{ $insumo->unidadMedida->abreviatura }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('movimiento_id_insumo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($stock_disponible_insumo !== null)
                                    <small class="text-muted">
                                        Stock disponible: <strong class="{{ $stock_disponible_insumo > 0 ? 'text-success' : 'text-danger' }}">{{ $stock_disponible_insumo }}</strong>
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    wire:model="movimiento_cantidad" 
                                    step="0.01" 
                                    class="form-control @error('movimiento_cantidad') is-invalid @enderror" 
                                    placeholder="0.00"
                                    @if($stock_disponible_insumo !== null) max="{{ $stock_disponible_insumo }}" @endif>
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
                                <button 
                                    type="button" 
                                    wire:click.prevent="agregarMovimiento" 
                                    class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Fila 2: Observaciones -->
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Observaciones</label>
                                <input type="text" wire:model="movimiento_observaciones" class="form-control" placeholder="Notas específicas (opcional)">
                            </div>
                        </div>
                    </div>

                    <!-- Listado de movimientos -->
                    @if(count($movimientos) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
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
                                            <td>
                                                <strong>{{ $mov['nombre_insumo'] }}</strong>
                                                <br><small class="text-muted">{{ $mov['unidad'] }}</small>
                                            </td>
                                            <td class="fw-bold">
                                                {{ number_format($mov['cantidad'], 2) }} {{ $mov['unidad'] }}
                                            </td>
                                            <td>
                                                @if($mov['motivo'] == 'Producción')
                                                    <span class="badge bg-info">{{ $mov['motivo'] }}</span>
                                                @elseif($mov['motivo'] == 'Mantenimiento')
                                                    <span class="badge bg-warning text-dark">{{ $mov['motivo'] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $mov['motivo'] }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $mov['observaciones'] ?? '-' }}</td>
                                            <td class="text-center">
                                                <button type="button" wire:click="eliminarMovimiento({{ $index }})" class="btn btn-sm btn-outline-danger" title="Eliminar movimiento">
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
                            <i class="bi bi-info-circle"></i> No hay movimientos de insumos registrados. Esta sección es opcional.
                        </div>
                    @endif
                </div>
            </div>

            <!-- BOTÓN FINAL PARA GUARDAR TODO -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        @if ($parte_id)
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        <button type="button" wire:click="guardar" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> {{ $parte_id ? 'Actualizar Parte Diario' : 'Guardar Parte Diario Completo' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Partes Diarios (Tabla) -->
        <div class="tab-pane fade" id="listado-partes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Partes Diarios</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por lote o fecha...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Lote</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Observaciones</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partes as $parte)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $parte->id_parte_diario }}</span></td>
                                        <td class="fw-semibold">{{ $parte->lote?->propietario ?? 'N/A' }}</td>
                                        <td>{{ $parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @if($parte->es_dia_caido)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-calendar-x"></i> Día Caído</span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-truck"></i> Producción</span>
                                            @endif
                                        </td>
                                        <td>{{ $parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 30) : '-' }}</td>
                                        <td>
                                            @if($parte->activo)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar({{ $parte->id_parte_diario }})" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar({{ $parte->id_parte_diario }})" onclick="return confirm('¿Está seguro de eliminar este parte diario?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay partes diarios registrados.</p>
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
</div>

<!-- JavaScript para cambiar de pestaña al editar/guardar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('parteDiarioGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Manejo de alertas para movimientos de insumos
        Livewire.on('mostrarExito', (event) => {
            const alertaDiv = document.getElementById('alertaMovimiento');
            alertaDiv.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> ${event.mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => { alertaDiv.innerHTML = ''; }, 3000);
        });
        
        Livewire.on('mostrarError', (event) => {
            const alertaDiv = document.getElementById('alertaMovimiento');
            alertaDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> ${event.mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        });
    });
</script>
