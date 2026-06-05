<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">📦 Gestión de Stock (FIFO)</h1>
        @can('crear-gestion-stock')
        <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="abrirModal">
            ➕ Registrar Entrada
        </button>
        @endcan
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border {{ session('alert-type', 'success') === 'danger' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} px-5 py-3 shadow-sm" role="alert">
            <span class="{{ session('alert-type', 'success') === 'danger' ? 'text-red-600' : 'text-emerald-600' }}">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="{{ session('alert-type', 'success') === 'danger' ? 'text-red-600 hover:text-red-800' : 'text-emerald-600 hover:text-emerald-800' }}" @click="open = false">✕</button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">📦 Lotes Activos</h6>
            <h3 class="text-2xl font-bold text-brand">{{ $estadisticas['total_lotes'] }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">📦 Stock Total</h6>
            <h3 class="text-2xl font-bold text-cyan-600">{{ number_format($estadisticas['stock_total'], 2) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">💰 Valor Inventario</h6>
            <h3 class="text-2xl font-bold text-emerald-600">${{ number_format($estadisticas['valor_inventario'], 2) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">⚠ Próximos a Agotar</h6>
            <h3 class="text-2xl font-bold {{ $estadisticas['lotes_proximos_agotar'] > 0 ? 'text-amber-500' : 'text-slate-400' }}">{{ $estadisticas['lotes_proximos_agotar'] }}</h3>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-slate-800">🔍 Filtros</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Insumo</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_insumo">
                        <option value="">Todos los insumos</option>
                        @foreach($insumos as $insumo)
                            <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Proveedor</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_proveedor">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo Movimiento</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_tipo">
                        <option value="">Todos</option>
                        <option value="compra">Compra</option>
                        <option value="ajuste_entrada">Ajuste Entrada</option>
                        <option value="devolucion">Devolución</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_estado">
                        <option value="disponibles">Disponibles</option>
                        <option value="agotados">Agotados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors w-full justify-center" wire:click="limpiarFiltros">
                        ✕ Limpiar
                    </button>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Desde</label>
                    <input type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_fecha_inicio">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Hasta</label>
                    <input type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_fecha_fin">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de lotes -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-slate-800">📋 Lotes de Inventario</h5>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insumo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Compra</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cant. Inicial</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Disponible</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor Disp.</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lotes as $lote)
                            <tr class="{{ $lote->agotado ? 'bg-slate-50' : '' }} hover:bg-slate-50 transition-colors" wire:key="row-{{ $lote->id_lote_inventario }}">
                                <td class="px-4 py-2.5"><strong>{{ $lote->id_lote_inventario }}</strong></td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $lote->insumo->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $lote->proveedor->razon_social ?? 'N/A' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $lote->fecha_compra->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lote->tipo_movimiento === 'compra' ? 'bg-brand/10 text-brand' : 'bg-cyan-100 text-cyan-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $lote->tipo_movimiento)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-600">{{ number_format($lote->cantidad_inicial, 2) }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="{{ \App\Services\InventarioService::estaProximoAgotar($lote) ? 'text-amber-500 font-bold' : 'text-slate-700' }}">
                                        {{ number_format($lote->cantidad_disponible, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($lote->precio_unitario, 2) }}</td>
                                <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($lote->valor_disponible, 2) }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($lote->agotado)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">✖ Agotado</span>
                                    @elseif(\App\Services\InventarioService::estaProximoAgotar($lote))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">⚠ Bajo</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">✓ Disponible</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-xs font-medium shadow-sm transition-colors"
                                        wire:click="verDetalle({{ $lote->id_lote_inventario }})" title="Ver detalle">
                                        👁️
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-8 text-slate-400">
                                    <div class="text-3xl mb-2">📥</div>
                                    No hay lotes de inventario registrados con los filtros aplicados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $lotes->links() }}
        </div>
    </div>

    <!-- Modal: Registrar Entrada -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-brand text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold">
                        📦 Registrar Entrada de Stock
                    </h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModal">✕</button>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Insumo <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_insumo') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror" wire:model="id_insumo">
                                    <option value="">Seleccione un insumo</option>
                                    @foreach($insumos as $insumo)
                                        <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                            {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_insumo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Proveedor</label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_proveedor') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror" wire:model="id_proveedor">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('id_proveedor') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cantidad <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" min="0"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('cantidad') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    wire:model.live="cantidad">
                                @error('cantidad') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Precio Unitario <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" min="0"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('precio_unitario') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    wire:model.live="precio_unitario">
                                @error('precio_unitario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Costo Total</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-slate-300 bg-slate-50 rounded-lg text-sm text-slate-500 cursor-not-allowed"
                                    value="${{ number_format(floatval($cantidad ?? 0) * floatval($precio_unitario ?? 0), 2) }}"
                                    disabled readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Número de Factura</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('numero_factura') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    wire:model="numero_factura">
                                @error('numero_factura') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Compra <span class="text-red-500">*</span></label>
                                <input type="date"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_compra') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    wire:model="fecha_compra">
                                @error('fecha_compra') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Movimiento <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('tipo_movimiento') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror" wire:model="tipo_movimiento">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="compra">Compra</option>
                                    <option value="ajuste_entrada">Ajuste de Entrada</option>
                                    <option value="devolucion">Devolución</option>
                                </select>
                                @error('tipo_movimiento') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Observaciones</label>
                                <textarea class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('observaciones') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                          rows="3" wire:model="observaciones" placeholder="Observaciones adicionales..."></textarea>
                                @error('observaciones') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModal">
                        ✕ Cancelar
                    </button>
                    @can('crear-gestion-stock')
                    <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="guardar">
                        💾 Guardar Entrada
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Detalle de Lote -->
    @if($loteSeleccionado)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-cyan-600 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold">
                        ℹ️ Detalle del Lote #{{ $loteSeleccionado?->id_lote_inventario }}
                    </h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarDetalle">✕</button>
                </div>
                <div class="p-6">
                    <!-- Información del Lote -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                                <h6 class="font-semibold text-slate-800">📄 Información General</h6>
                            </div>
                            <div class="p-6">
                                <table class="w-full text-sm">
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 w-1/2 text-slate-500 font-medium">Insumo:</th>
                                        <td class="py-2">{{ $loteSeleccionado?->insumo?->nombre ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Proveedor:</th>
                                        <td class="py-2">{{ $loteSeleccionado?->proveedor?->razon_social ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Fecha Compra:</th>
                                        <td class="py-2">{{ optional($loteSeleccionado?->fecha_compra)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-slate-500 font-medium">Número Factura:</th>
                                        <td class="py-2">{{ $loteSeleccionado?->numero_factura ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                                <h6 class="font-semibold text-slate-800">📈 Cantidades y Valores</h6>
                            </div>
                            <div class="p-6">
                                <table class="w-full text-sm mb-4">
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 w-1/2 text-slate-500 font-medium">Cantidad Inicial:</th>
                                        <td class="py-2 text-right">{{ number_format($loteSeleccionado?->cantidad_inicial ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Cantidad Disponible:</th>
                                        <td class="py-2 text-right font-bold text-emerald-600">{{ number_format($loteSeleccionado?->cantidad_disponible ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-slate-500 font-medium">Precio Unitario:</th>
                                        <td class="py-2 text-right">${{ number_format($loteSeleccionado?->precio_unitario ?? 0, 2) }}</td>
                                    </tr>
                                </table>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Porcentaje Consumido:</label>
                                    <div class="w-full bg-slate-200 rounded-full h-6">
                                        <div class="h-full rounded-full text-xs text-white text-center leading-6 {{ ($loteSeleccionado?->porcentaje_consumido ?? 0) > 80 ? 'bg-amber-500' : 'bg-brand' }}"
                                             style="width: {{ $loteSeleccionado?->porcentaje_consumido ?? 0 }}%">
                                            {{ number_format($loteSeleccionado?->porcentaje_consumido ?? 0, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(($loteSeleccionado?->observaciones ?? null))
                        <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mb-6">
                            <span>💬</span> <strong>Observaciones:</strong> {{ $loteSeleccionado?->observaciones }}
                        </div>
                    @endif

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                            <h6 class="font-semibold text-slate-800">🕐 Historial de Movimientos</h6>
                        </div>
                        <div class="p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Unit.</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse(($loteSeleccionado?->movimientos ?? []) as $mov)
                                            <tr wire:key="row-{{ $mov->id }}" class="hover:bg-slate-50 transition-colors">
                                                <td class="px-4 py-2.5 text-slate-600">{{ optional(\Carbon\Carbon::parse($mov->fecha))->format('d/m/Y H:i') }}</td>
                                                <td class="px-4 py-2.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $mov->tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                        {{ ucfirst($mov->tipo) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 text-right text-slate-600">{{ number_format($mov->cantidad, 2) }}</td>
                                                <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($mov->precio_unitario, 2) }}</td>
                                                <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($mov->costo_total_movimiento ?? 0, 2) }}</td>
                                                <td class="px-4 py-2.5 text-slate-600">{{ $mov->motivo ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-8 text-slate-400">
                                                    <div class="text-xl mb-2">📥</div> No hay movimientos registrados
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarDetalle">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
