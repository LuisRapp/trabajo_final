<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
                <flux:icon.archive-box class="size-6" />
                Control de stock
            </h1>
            <p class="mt-1 text-sm text-tinta-suave">
                Lotes por costo (FIFO): lo que sale consume primero lo más antiguo.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="secondary" icon="document-arrow-down" wire:click="exportarReporte">
                Exportar
            </x-ui.button>
            @can('crear-gestion-stock')
                <x-ui.button variant="primary" icon="plus" wire:click="abrirModal">
                    Registrar movimiento
                </x-ui.button>
            @endcan
        </div>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="{{ session('alert-type', 'success') === 'danger' ? 'danger' : 'success' }}" class="mb-4">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <!-- Indicadores -->
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.card class="px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Lotes activos</p>
            <p class="mt-1 text-2xl font-bold text-tinta">{{ $estadisticas['total_lotes'] }}</p>
        </x-ui.card>
        <x-ui.card class="px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Stock total</p>
            <p class="mt-1 text-2xl font-bold text-tinta">{{ number_format($estadisticas['stock_total'], 2) }}</p>
        </x-ui.card>
        <x-ui.card class="px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Valor del inventario</p>
            <p class="mt-1 text-2xl font-bold text-pino">$ {{ number_format($estadisticas['valor_inventario'], 2, ',', '.') }}</p>
        </x-ui.card>
        <x-ui.card class="px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Próximos a agotar</p>
            <p class="mt-1 text-2xl font-bold {{ $estadisticas['lotes_proximos_agotar'] > 0 ? 'text-tierra' : 'text-arena-oscura' }}">
                {{ $estadisticas['lotes_proximos_agotar'] }}
            </p>
        </x-ui.card>
    </div>

    <!-- Filtros -->
    <x-ui.card class="mb-4">
        <div class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[170px] flex-1">
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Insumo</label>
                <select class="form-input" wire:model.live="filtro_insumo">
                    <option value="">Todos</option>
                    @foreach ($insumos as $insumo)
                        <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[170px] flex-1">
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Proveedor</label>
                <select class="form-input" wire:model.live="filtro_proveedor">
                    <option value="">Todos</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Tipo</label>
                <select class="form-input" wire:model.live="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="compra">Compra</option>
                    <option value="ajuste_entrada">Ajuste de entrada</option>
                    <option value="ajuste_salida">Ajuste de salida</option>
                    <option value="devolucion">Devolución</option>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Estado</label>
                <select class="form-input" wire:model.live="filtro_estado">
                    <option value="disponibles">Disponibles</option>
                    <option value="agotados">Agotados</option>
                    <option value="todos">Todos</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Desde</label>
                <input autocomplete="off" type="date" class="form-input" wire:model.live="filtro_fecha_inicio">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-tinta">Hasta</label>
                <input autocomplete="off" type="date" class="form-input" wire:model.live="filtro_fecha_fin">
            </div>
            <x-ui.button variant="secondary" icon="x-mark" wire:click="limpiarFiltros">
                Limpiar
            </x-ui.button>
        </div>
    </x-ui.card>

    <!-- Tabla -->
    <x-ui.card class="overflow-hidden">
        @if ($lotes->count() > 0)
            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 7%;">Lote</th>
                            <th style="width: 17%;">Insumo</th>
                            <th style="width: 12%;">Proveedor</th>
                            <th style="width: 9%;">Fecha</th>
                            <th style="width: 13%;">Tipo</th>
                            <th style="width: 14%;" class="text-right">Disponible</th>
                            <th style="width: 9%;" class="text-right">Precio unit.</th>
                            <th style="width: 10%;" class="text-right">Valor</th>
                            <th style="width: 9%;">Estado</th>
                            <th style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lotes as $lote)
                            @php
                                $proximo = \App\Services\InventarioService::estaProximoAgotar($lote);
                                $tipoLabel = match ($lote->tipo_movimiento) {
                                    'compra' => 'Compra',
                                    'ajuste_entrada' => 'Ajuste entrada',
                                    'ajuste_salida' => 'Ajuste salida',
                                    'devolucion' => 'Devolución',
                                    default => ucfirst(str_replace('_', ' ', $lote->tipo_movimiento)),
                                };
                            @endphp
                            <tr wire:key="lote-{{ $lote->id_lote_inventario }}">
                                <td class="font-medium text-tinta">#{{ $lote->id_lote_inventario }}</td>
                                <td class="text-tinta">{{ $lote->insumo->nombre ?? '—' }}</td>
                                <td class="text-tinta-suave">{{ $lote->proveedor->razon_social ?? '—' }}</td>
                                <td class="text-tinta-suave">{{ $lote->fecha_compra->format('d/m/Y') }}</td>
                                <td>
                                    <x-ui.badge variant="{{ $lote->tipo_movimiento === 'compra' ? 'info' : 'neutral' }}">
                                        {{ $tipoLabel }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-right {{ $proximo ? 'font-semibold text-tierra' : 'text-tinta' }}">
                                    {{ number_format($lote->cantidad_disponible, 2) }}
                                    <span class="text-xs text-arena-oscura">de {{ number_format($lote->cantidad_inicial, 2) }}</span>
                                </td>
                                <td class="text-right text-tinta-suave">$ {{ number_format($lote->precio_unitario, 2, ',', '.') }}</td>
                                <td class="text-right text-tinta-suave">$ {{ number_format($lote->valor_disponible, 2, ',', '.') }}</td>
                                <td>
                                    @if ($lote->agotado)
                                        <x-ui.badge variant="neutral">Agotado</x-ui.badge>
                                    @elseif ($proximo)
                                        <x-ui.badge variant="warning">Bajo</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="success">Disponible</x-ui.badge>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <x-ui.button variant="secondary" size="sm" icon="eye"
                                        wire:click="verDetalle({{ $lote->id_lote_inventario }})" title="Ver lote" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.table-container>

            <div class="border-t border-arena px-4 py-3">
                {{ $lotes->links() }}
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <flux:icon.archive-box class="mx-auto size-8 text-arena-oscura" />
                <p class="mt-3 font-medium text-tinta-suave">No hay lotes con esos filtros.</p>
                <p class="mt-1 text-xs text-arena-oscura">Probá limpiando los filtros o registrá un ingreso.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Panel: registrar movimiento --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 flex justify-end bg-tinta/40 backdrop-blur-[1px]">
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-arena px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-tinta">Registrar movimiento</h2>
                        <p class="mt-0.5 text-xs text-tinta-suave">Elegí el tipo: define qué datos pide el formulario.</p>
                    </div>
                    <button type="button" class="text-tinta-suave transition-colors hover:text-tinta" wire:click="cerrarModal" title="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    @php $esSalida = $tipo_movimiento === 'ajuste_salida'; @endphp

                    <form id="movimiento-stock-form" autocomplete="off" wire:submit.prevent="guardar" class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-tinta">Tipo de movimiento <span class="text-tierra">*</span></label>
                            <select class="form-input @error('tipo_movimiento') border-tierra bg-tierra-suave @enderror" wire:model.live="tipo_movimiento">
                                <option value="">Seleccione un tipo</option>
                                <option value="compra">Compra (ingresa stock)</option>
                                <option value="ajuste_entrada">Ajuste de entrada (suma)</option>
                                <option value="ajuste_salida">Ajuste de salida (descuenta por FIFO)</option>
                                <option value="devolucion">Devolución (ingresa stock)</option>
                            </select>
                            @error('tipo_movimiento') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-tinta">Insumo <span class="text-tierra">*</span></label>
                            <select class="form-input @error('id_insumo') border-tierra bg-tierra-suave @enderror" wire:model="id_insumo">
                                <option value="">Seleccione un insumo</option>
                                @foreach ($insumos as $insumo)
                                    <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                        {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_insumo') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        </div>

                        <div class="{{ $esSalida ? '' : 'grid grid-cols-2 gap-3' }}">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-tinta">Cantidad <span class="text-tierra">*</span></label>
                                <input autocomplete="off" type="number" step="0.1" min="0"
                                    class="form-input @error('cantidad') border-tierra bg-tierra-suave @enderror" wire:model.live="cantidad">
                                @error('cantidad') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>

                            @unless ($esSalida)
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-tinta">Precio unitario <span class="text-tierra">*</span></label>
                                    <input autocomplete="off" type="number" step="0.1" min="0"
                                        class="form-input @error('precio_unitario') border-tierra bg-tierra-suave @enderror" wire:model.live="precio_unitario">
                                    @error('precio_unitario') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                </div>
                            @endunless
                        </div>

                        @if ($esSalida)
                            <div class="rounded-sm border border-pino/30 bg-pino-suave px-4 py-2.5 text-xs text-tinta">
                                Se descuenta del lote más antiguo primero (FIFO) y no puede superar el stock disponible.
                            </div>
                        @else
                            <div class="flex items-center justify-between rounded-sm border border-pino/30 bg-pino-suave px-4 py-2.5">
                                <span class="text-xs font-medium text-tinta">Costo total</span>
                                <span class="text-base font-bold text-pino">$ {{ number_format((float) ($cantidad ?? 0) * (float) ($precio_unitario ?? 0), 2, ',', '.') }}</span>
                            </div>
                        @endif

                        @unless ($esSalida)
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-tinta">Proveedor</label>
                                    <select class="form-input @error('id_proveedor') border-tierra bg-tierra-suave @enderror" wire:model="id_proveedor">
                                        <option value="">Sin proveedor</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_proveedor') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-tinta">N.º de factura</label>
                                    <input autocomplete="off" type="text" class="form-input @error('numero_factura') border-tierra bg-tierra-suave @enderror" wire:model="numero_factura">
                                    @error('numero_factura') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endunless

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-tinta">Fecha <span class="text-tierra">*</span></label>
                            <input autocomplete="off" type="date" class="form-input @error('fecha_compra') border-tierra bg-tierra-suave @enderror" wire:model="fecha_compra">
                            @error('fecha_compra') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-tinta">Observaciones</label>
                            <textarea class="form-input @error('observaciones') border-tierra bg-tierra-suave @enderror" rows="3"
                                wire:model="observaciones" placeholder="Opcional"></textarea>
                            @error('observaciones') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        </div>
                    </form>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-arena bg-hueso px-5 py-3">
                    <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarModal">Cancelar</x-ui.button>
                    @can('crear-gestion-stock')
                        <x-ui.button variant="primary" icon="check" type="submit" form="movimiento-stock-form">
                            Guardar movimiento
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    {{-- Panel: detalle del lote --}}
    @if ($loteSeleccionado)
        @php
            $lote = $loteSeleccionado;
            $proximo = \App\Services\InventarioService::estaProximoAgotar($lote);
        @endphp
        <div class="fixed inset-0 z-50 flex justify-end bg-tinta/40 backdrop-blur-[1px]">
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-arena px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-tinta">Lote #{{ $lote->id_lote_inventario }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-tinta-suave">
                            @if ($lote->agotado)
                                <x-ui.badge variant="neutral">Agotado</x-ui.badge>
                            @elseif ($proximo)
                                <x-ui.badge variant="warning">Bajo</x-ui.badge>
                            @else
                                <x-ui.badge variant="success">Disponible</x-ui.badge>
                            @endif
                            <span>{{ $lote->insumo?->nombre ?? 'Sin insumo' }}</span>
                            <span>·</span>
                            <span>{{ $lote->fecha_compra?->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <button type="button" class="text-tinta-suave transition-colors hover:text-tinta" wire:click="cerrarDetalle" title="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto px-5 py-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-sm border border-arena px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Disponible</p>
                            <p class="mt-1 text-xl font-bold {{ $proximo ? 'text-tierra' : 'text-tinta' }}">
                                {{ number_format($lote->cantidad_disponible, 2) }}
                            </p>
                            <p class="text-xs text-arena-oscura">de {{ number_format($lote->cantidad_inicial, 2) }} iniciales</p>
                        </div>
                        <div class="rounded-sm border border-arena px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Valor disponible</p>
                            <p class="mt-1 text-xl font-bold text-pino">$ {{ number_format($lote->valor_disponible, 2, ',', '.') }}</p>
                            <p class="text-xs text-arena-oscura">$ {{ number_format($lote->precio_unitario, 2, ',', '.') }} por unidad</p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-tinta-suave">Consumido</span>
                            <span class="text-tinta">{{ number_format($lote->porcentaje_consumido, 1, ',', '.') }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-arena">
                            <div class="h-full rounded-full {{ $lote->porcentaje_consumido > 80 ? 'bg-tierra' : 'bg-pino' }}"
                                style="width: {{ min(100, $lote->porcentaje_consumido) }}%"></div>
                        </div>
                    </div>

                    <div class="rounded-sm border border-arena">
                        <dl class="divide-y divide-arena text-sm">
                            <div class="flex justify-between gap-3 px-4 py-2">
                                <dt class="text-tinta-suave">Proveedor</dt>
                                <dd class="text-right text-tinta">{{ $lote->proveedor?->razon_social ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3 px-4 py-2">
                                <dt class="text-tinta-suave">Tipo</dt>
                                <dd class="text-right text-tinta">{{ match ($lote->tipo_movimiento) {
                                    'compra' => 'Compra',
                                    'ajuste_entrada' => 'Ajuste de entrada',
                                    'ajuste_salida' => 'Ajuste de salida',
                                    'devolucion' => 'Devolución',
                                    default => ucfirst(str_replace('_', ' ', $lote->tipo_movimiento)),
                                } }}</dd>
                            </div>
                            <div class="flex justify-between gap-3 px-4 py-2">
                                <dt class="text-tinta-suave">N.º factura</dt>
                                <dd class="text-right text-tinta">{{ $lote->numero_factura ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if ($lote->observaciones)
                        <x-ui.alert variant="info" :dismissible="false">{{ $lote->observaciones }}</x-ui.alert>
                    @endif

                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-tinta">Movimientos del lote</h3>

                        @if ($lote->movimientos->isEmpty())
                            <p class="rounded-sm border border-arena bg-hueso py-6 text-center text-sm text-tinta-suave">
                                Sin movimientos registrados.
                            </p>
                        @else
                            <x-ui.table-container>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th class="text-right">Cantidad</th>
                                            <th class="text-right">Costo</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lote->movimientos as $mov)
                                            <tr wire:key="mov-{{ $mov->id_movimiento_stock }}">
                                                <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                                                <td>
                                                    <x-ui.badge variant="{{ $mov->tipo === 'entrada' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($mov->tipo) }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="text-right text-tinta-suave">{{ number_format($mov->cantidad, 2) }}</td>
                                                <td class="text-right text-tinta-suave">$ {{ number_format($mov->costo_total_movimiento ?? 0, 2, ',', '.') }}</td>
                                                <td class="text-tinta-suave">{{ $mov->motivo ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-ui.table-container>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-arena bg-hueso px-5 py-3">
                    <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarDetalle">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
