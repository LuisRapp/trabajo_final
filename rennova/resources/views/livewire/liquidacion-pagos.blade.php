<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-calculator"></i> Liquidación de Pagos a Empleados
            </h4>
        </div>
        
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (!$mostrar_liquidacion)
                {{-- Formulario de selección --}}
                <form wire:submit.prevent="calcularLiquidacion">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_empleado" class="form-label">Empleado *</label>
                                <select wire:model="id_empleado" id="id_empleado" class="form-select @error('id_empleado') is-invalid @enderror">
                                    <option value="">Seleccione un empleado</option>
                                    @foreach ($empleados as $emp)
                                        <option value="{{ $emp->id_empleado }}">
                                            {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio *</label>
                                <input type="date" wire:model="fecha_inicio" id="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror">
                                @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin *</label>
                                <input type="date" wire:model="fecha_fin" id="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror">
                                @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-calculator"></i> Calcular
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                {{-- Pantalla de liquidación calculada --}}
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5 class="mb-2">
                                <i class="fas fa-user"></i> 
                                {{ $empleado_seleccionado->apellido }}, {{ $empleado_seleccionado->nombre }}
                            </h5>
                            <p class="mb-0">
                                <strong>Rol:</strong> {{ $empleado_seleccionado->rolLaboral->nombre ?? 'N/A' }} |
                                <strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                @if (!$recibo_generado)
                    {{-- Detalle del cálculo --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Detalle del Cálculo</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            <tr>
                                                <td><strong>Días caídos trabajados:</strong></td>
                                                <td class="text-end">{{ $calculo['cantidad_dias_caidos'] }} días</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jornal diario:</strong></td>
                                                <td class="text-end">${{ number_format($calculo['valor_jornal'], 2) }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <td><strong>Subtotal jornales:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($calculo['total_pagar_jornales'], 2) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><hr class="my-2"></td>
                                            </tr>
                                       
                                            <tr>
                                                <td><strong>Toneladas producidas:</strong></td>
                                                <td class="text-end">{{ number_format($calculo['total_peso_toneladas'] ?? 0, 2) }} ton</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tarifa por tonelada:</strong></td>
                                                <td class="text-end">${{ number_format($calculo['tarifa_fija_por_tonelada'], 2) }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <td><strong>Subtotal producción:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($calculo['total_pagar_produccion'], 2) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><hr class="my-2"></td>
                                            </tr>
                                            <tr class="table-success">
                                                <td><strong>TOTAL CALCULADO:</strong></td>
                                                <td class="text-end"><h5 class="mb-0">${{ number_format($calculo['total_pagar_final'], 2) }}</h5></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            {{-- Adelantos pendientes --}}
                            @if(count($adelantos_pendientes) > 0)
                                <div class="card border-warning mt-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-hand-holding-usd"></i> Adelantos Pendientes</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th class="text-end">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($adelantos_pendientes as $adelanto)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') }}</td>
                                                        <td class="text-end">${{ number_format($adelanto->monto, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-warning">
                                                    <th>TOTAL ADELANTOS:</th>
                                                    <th class="text-end">${{ number_format($total_adelantos, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small><i class="fas fa-info-circle"></i> Estos adelantos se descontarán automáticamente y se marcarán como "pagados" al generar el recibo.</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-edit"></i> Datos del Recibo (Editable)</h6>
                                </div>
                                <div class="card-body">
                                    <form wire:submit.prevent="generarRecibo">
                                        <div class="mb-3">
                                            <label for="monto_bruto" class="form-label">Monto Bruto *</label>
                                            <input type="number" 
                                                   wire:model.live="monto_bruto" 
                                                   id="monto_bruto" 
                                                   step="0.01" 
                                                   class="form-control @error('monto_bruto') is-invalid @enderror">
                                            @error('monto_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <div class="form-text">Puedes modificar el monto calculado si es necesario</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descuentos" class="form-label">
                                                Descuentos
                                                @if($total_adelantos > 0)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-hand-holding-usd"></i> Incluye adelantos
                                                    </span>
                                                @endif
                                            </label>
                                            <input type="number" 
                                                   wire:model.live="descuentos" 
                                                   id="descuentos" 
                                                   step="0.01" 
                                                   class="form-control @error('descuentos') is-invalid @enderror">
                                            @error('descuentos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <div class="form-text">
                                                @if($total_adelantos > 0)
                                                    Adelantos del período: ${{ number_format($total_adelantos, 2) }} (se marcarán como pagados)
                                                @else
                                                    Otros descuentos: retenciones, etc.
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Monto Neto a Pagar</label>
                                            <div class="alert alert-success mb-0">
                                                <h4 class="mb-0">${{ number_format($monto_neto, 2) }}</h4>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="observaciones" class="form-label">Observaciones</label>
                                            <textarea wire:model="observaciones" 
                                                      id="observaciones" 
                                                      rows="3" 
                                                      class="form-control @error('observaciones') is-invalid @enderror" 
                                                      maxlength="150"></textarea>
                                            @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-check-circle"></i> Generar Recibo
                                            </button>
                                            <button type="button" wire:click="nuevaLiquidacion" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Recibo generado exitosamente --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success text-center py-5">
                                <i class="fas fa-check-circle fa-5x mb-3"></i>
                                <h3>¡Recibo generado correctamente!</h3>
                                <p class="lead mb-4">El pago ha sido registrado en el sistema.</p>
                                <button type="button" wire:click="nuevaLiquidacion" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Nueva Liquidación
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
