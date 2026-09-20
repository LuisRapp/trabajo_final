<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-tinta">Control de Stock</h1>
        @can('crear-gestion-stock')
            <x-ui.button variant="primary" icon="plus" wire:click="abrirModal">
                Registrar Entrada
            </x-ui.button>
        @endcan
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="{{ session('alert-type', 'success') === 'danger' ? 'danger' : 'success' }}" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <!-- Estadisticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card class="p-6 text-center">
            <h6 class="text-sm text-tinta-suave mb-2">Lotes Activos</h6>
            <h3 class="text-2xl font-bold text-pino">{{ $estadisticas['total_lotes'] }}</h3>
        </x-ui.card>
        <x-ui.card class="p-6 text-center">
            <h6 class="text-sm text-tinta-suave mb-2">Stock Total</h6>
            <h3 class="text-2xl font-bold text-tinta">{{ number_format($estadisticas['stock_total'], 2) }}</h3>
        </x-ui.card>
        <x-ui.card class="p-6 text-center">
            <h6 class="text-sm text-tinta-suave mb-2">Valor Inventario</h6>
            <h3 class="text-2xl font-bold text-musgo">${{ number_format($estadisticas['valor_inventario'], 2) }}</h3>
        </x-ui.card>
        <x-ui.card class="p-6 text-center">
            <h6 class="text-sm text-tinta-suave mb-2">Proximos a Agotar</h6>
            <h3 class="text-2xl font-bold {{ $estadisticas['lotes_proximos_agotar'] > 0 ? 'text-resina' : 'text-arena-oscura' }}">
                {{ $estadisticas['lotes_proximos_agotar'] }}
            </h3>
        </x-ui.card>
    </div>

    <!-- Filtros -->
    <x-ui.card class="p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Insumo</label>
                <select class="form-input" wire:model.live="filtro_insumo">
                    <option value="">Todos los insumos</option>
                    @foreach($insumos as $insumo)
                        <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Proveedor</label>
                <select class="form-input" wire:model.live="filtro_proveedor">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Tipo Movimiento</label>
                <select class="form-input" wire:model.live="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="compra">Compra</option>
                    <option value="ajuste_entrada">Ajuste Entrada</option>
                    <option value="devolucion">Devolucion</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Estado</label>
                <select class="form-input" wire:model.live="filtro_estado">
                    <option value="disponibles">Disponibles</option>
                    <option value="agotados">Agotados</option>
                    <option value="todos">Todos</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Fecha Desde</label>
                <input type="date" class="form-input" wire:model.live="filtro_fecha_inicio">
            </div>
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Fecha Hasta</label>
                <input type="date" class="form-input" wire:model.live="filtro_fecha_fin">
            </div>
            <div>
                <x-ui.button variant="secondary" size="sm" icon="x-mark" wire:click="limpiarFiltros" class="w-full">
                    Limpiar
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <!-- Tabla de lotes -->
    <x-ui.card>
        <div class="px-4 py-3 border-b border-arena">
            <h5 class="text-sm font-semibold text-tinta">Lotes de Inventario</h5>
        </div>
        <x-ui.table-container>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Lote</th>
                        <th>Insumo</th>
                        <th>Proveedor</th>
                        <th>Fecha Compra</th>
                        <th>Tipo</th>
                        <th class="text-right">Cant. Inicial</th>
                        <th class="text-right">Disponible</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Valor Disp.</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                        <tr class="{{ $lote->agotado ? 'bg-corteza-suave/50' : '' }}" wire:key="row-{{ $lote->id_lote_inventario }}">
                            <td><strong>{{ $lote->id_lote_inventario }}</strong></td>
                            <td class="text-tinta">{{ $lote->insumo->nombre ?? 'N/A' }}</td>
                            <td class="text-tinta">{{ $lote->proveedor->razon_social ?? 'N/A' }}</td>
                            <td class="text-tinta-suave">{{ $lote->fecha_compra->format('d/m/Y') }}</td>
                            <td>
                                <x-ui.badge variant="{{ $lote->tipo_movimiento === 'compra' ? 'info' : 'neutral' }}">
                                    {{ ucfirst(str_replace('_', ' ', $lote->tipo_movimiento)) }}
                                </x-ui.badge>
                            </td>
                            <td class="text-right text-tinta-suave">{{ number_format($lote->cantidad_inicial, 2) }}</td>
                            <td class="text-right">
                                <span class="{{ \App\Services\InventarioService::estaProximoAgotar($lote) ? 'text-resina font-bold' : 'text-tinta' }}">
                                    {{ number_format($lote->cantidad_disponible, 2) }}
                                </span>
                            </td>
                            <td class="text-right text-tinta-suave">${{ number_format($lote->precio_unitario, 2) }}</td>
                            <td class="text-right text-tinta-suave">${{ number_format($lote->valor_disponible, 2) }}</td>
                            <td class="text-center">
                                @if($lote->agotado)
                                    <x-ui.badge variant="neutral">Agotado</x-ui.badge>
                                @elseif(\App\Services\InventarioService::estaProximoAgotar($lote))
                                    <x-ui.badge variant="warning">Bajo</x-ui.badge>
                                @else
                                    <x-ui.badge variant="success">Disponible</x-ui.badge>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.button variant="secondary" size="sm" icon="eye" wire:click="verDetalle({{ $lote->id_lote_inventario }})" title="Ver detalle">
                                    Ver
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="11" message="No hay lotes de inventario registrados con los filtros aplicados" icon="archive-box" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.table-container>
        <div class="px-4 py-3 bg-hueso border-t border-arena">
            {{ $lotes->links() }}
        </div>
    </x-ui.card>

    <!-- Modal: Registrar Entrada -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white border border-arena rounded-sm shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-pino text-white px-4 py-3 flex justify-between items-center">
                    <h5 class="text-sm font-semibold">Registrar Entrada de Stock</h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModal" aria-label="Cerrar">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Insumo <span class="text-tierra">*</span></label>
                                <select class="form-input @error('id_insumo') border-tierra bg-tierra-suave @enderror" wire:model="id_insumo">
                                    <option value="">Seleccione un insumo</option>
                                    @foreach($insumos as $insumo)
                                        <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                            {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_insumo') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Proveedor</label>
                                <select class="form-input @error('id_proveedor') border-tierra bg-tierra-suave @enderror" wire:model="id_proveedor">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('id_proveedor') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Cantidad <span class="text-tierra">*</span></label>
                                <input type="number" step="0.1" min="0" class="form-input @error('cantidad') border-tierra bg-tierra-suave @enderror" wire:model.live="cantidad">
                                @error('cantidad') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Precio Unitario <span class="text-tierra">*</span></label>
                                <input type="number" step="0.1" min="0" class="form-input @error('precio_unitario') border-tierra bg-tierra-suave @enderror" wire:model.live="precio_unitario">
                                @error('precio_unitario') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-end">
                                <div class="w-full rounded-sm border border-pino bg-pino-suave px-3 py-2">
                                    <span class="block text-xs font-semibold text-pino mb-0.5">Costo Total</span>
                                    <span class="text-base font-bold text-pino-oscuro">${{ number_format(floatval($cantidad ?? 0) * floatval($precio_unitario ?? 0), 2) }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Numero de Factura</label>
                                <input type="text" class="form-input @error('numero_factura') border-tierra bg-tierra-suave @enderror" wire:model="numero_factura">
                                @error('numero_factura') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Fecha de Compra <span class="text-tierra">*</span></label>
                                <input type="date" class="form-input @error('fecha_compra') border-tierra bg-tierra-suave @enderror" wire:model="fecha_compra">
                                @error('fecha_compra') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Tipo de Movimiento <span class="text-tierra">*</span></label>
                                <select class="form-input @error('tipo_movimiento') border-tierra bg-tierra-suave @enderror" wire:model="tipo_movimiento">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="compra">Compra</option>
                                    <option value="ajuste_entrada">Ajuste de Entrada</option>
                                    <option value="devolucion">Devolucion</option>
                                </select>
                                @error('tipo_movimiento') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Observaciones</label>
                                <textarea class="form-input @error('observaciones') border-tierra bg-tierra-suave @enderror" rows="3" wire:model="observaciones" placeholder="Observaciones adicionales..."></textarea>
                                @error('observaciones') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="flex justify-end gap-2 px-4 py-3 bg-hueso border-t border-arena">
                    <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarModal">
                        Cancelar
                    </x-ui.button>
                    @can('crear-gestion-stock')
                        <x-ui.button variant="primary" icon="check" wire:click="guardar">
                            Guardar Entrada
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Detalle de Lote -->
    @if($loteSeleccionado)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white border border-arena rounded-sm shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-corteza text-white px-4 py-3 flex justify-between items-center">
                    <h5 class="text-sm font-semibold">Detalle del Lote #{{ $loteSeleccionado?->id_lote_inventario }}</h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarDetalle" aria-label="Cerrar">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
                <div class="p-4">
                    <!-- Informacion del Lote -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-ui.card>
                            <div class="px-4 py-3 border-b border-arena">
                                <h6 class="text-sm font-semibold text-tinta">Informacion General</h6>
                            </div>
                            <div class="p-4">
                                <table class="w-full text-sm">
                                    <tr class="border-b border-arena">
                                        <th class="text-left py-2 w-1/2 text-tinta-suave font-medium">Insumo:</th>
                                        <td class="py-2 text-tinta">{{ $loteSeleccionado?->insumo?->nombre ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-b border-arena">
                                        <th class="text-left py-2 text-tinta-suave font-medium">Proveedor:</th>
                                        <td class="py-2 text-tinta">{{ $loteSeleccionado?->proveedor?->razon_social ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-b border-arena">
                                        <th class="text-left py-2 text-tinta-suave font-medium">Fecha Compra:</th>
                                        <td class="py-2 text-tinta">{{ optional($loteSeleccionado?->fecha_compra)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-tinta-suave font-medium">Numero Factura:</th>
                                        <td class="py-2 text-tinta">{{ $loteSeleccionado?->numero_factura ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </x-ui.card>
                        <x-ui.card>
                            <div class="px-4 py-3 border-b border-arena">
                                <h6 class="text-sm font-semibold text-tinta">Cantidades y Valores</h6>
                            </div>
                            <div class="p-4">
                                <table class="w-full text-sm mb-4">
                                    <tr class="border-b border-arena">
                                        <th class="text-left py-2 w-1/2 text-tinta-suave font-medium">Cantidad Inicial:</th>
                                        <td class="py-2 text-right text-tinta">{{ number_format($loteSeleccionado?->cantidad_inicial ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="border-b border-arena">
                                        <th class="text-left py-2 text-tinta-suave font-medium">Cantidad Disponible:</th>
                                        <td class="py-2 text-right font-bold text-musgo">{{ number_format($loteSeleccionado?->cantidad_disponible ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-tinta-suave font-medium">Precio Unitario:</th>
                                        <td class="py-2 text-right text-tinta">${{ number_format($loteSeleccionado?->precio_unitario ?? 0, 2) }}</td>
                                    </tr>
                                </table>
                                <div>
                                    <label class="block text-xs font-semibold text-tinta mb-1.5">Porcentaje Consumido:</label>
                                    <div class="w-full bg-arena rounded-sm h-5">
                                        <div class="h-full rounded-sm text-xs text-white text-center leading-5 {{ ($loteSeleccionado?->porcentaje_consumido ?? 0) > 80 ? 'bg-resina' : 'bg-pino' }}"
                                             style="width: {{ $loteSeleccionado?->porcentaje_consumido ?? 0 }}%">
                                            {{ number_format($loteSeleccionado?->porcentaje_consumido ?? 0, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    @if(($loteSeleccionado?->observaciones ?? null))
                        <x-ui.alert variant="info" class="mb-4">
                            <strong>Observaciones:</strong> {{ $loteSeleccionado?->observaciones }}
                        </x-ui.alert>
                    @endif

                    <x-ui.card>
                        <div class="px-4 py-3 border-b border-arena">
                            <h6 class="text-sm font-semibold text-tinta">Historial de Movimientos</h6>
                        </div>
                        <x-ui.table-container>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Precio Unit.</th>
                                        <th class="text-right">Costo Total</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($loteSeleccionado?->movimientos ?? []) as $mov)
                                        <tr wire:key="row-{{ $mov->id }}">
                                            <td class="text-tinta-suave">{{ optional(\Carbon\Carbon::parse($mov->fecha))->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <x-ui.badge variant="{{ $mov->tipo === 'entrada' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($mov->tipo) }}
                                                </x-ui.badge>
                                            </td>
                                            <td class="text-right text-tinta-suave">{{ number_format($mov->cantidad, 2) }}</td>
                                            <td class="text-right text-tinta-suave">${{ number_format($mov->precio_unitario, 2) }}</td>
                                            <td class="text-right text-tinta-suave">${{ number_format($mov->costo_total_movimiento ?? 0, 2) }}</td>
                                            <td class="text-tinta-suave">{{ $mov->motivo ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-8 text-arena-oscura">
                                                No hay movimientos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </x-ui.table-container>
                    </x-ui.card>
                </div>
                <div class="flex justify-end gap-2 px-4 py-3 bg-hueso border-t border-arena">
                    <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarDetalle">
                        Cerrar
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
