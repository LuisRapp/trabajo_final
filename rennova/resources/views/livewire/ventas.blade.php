<div class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.document-text class="size-6" />
            Ventas
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-5">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-5">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        @can('crear-ventas')
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nueva Venta
        </button>
        @endcan
        <button type="button" wire:click="$set('tab_activo','historial')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'historial' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.list-bullet class="size-4" />
            Historial de Ventas
        </button>
    </div>

    @if($tab_activo === 'nuevo')
    @can('crear-ventas')
    <div>
        <x-ui.card class="overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta mb-0">Buscar cargas pendientes</h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="buscarCargasPendientes" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Cliente</label>
                        <select wire:model="id_cliente" class="form-input">
                            <option value="">Seleccione cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Desde</label>
                        <input type="date" wire:model="fecha_desde" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_hasta" class="form-input">
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <x-ui.button variant="primary" icon="magnifying-glass" type="submit" class="flex-1">
                            Buscar
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>

        @if (!empty($detalle_cargas))
            <x-ui.card class="mt-5 overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                    <h5 class="text-lg font-semibold text-tinta mb-0">Resultados ({{ count($detalle_cargas) }})</h5>
                </div>
                <div class="p-0">
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Ticket</th>
                                    <th>Categoría</th>
                                    <th class="text-right">Peso Neto (kg)</th>
                                    <th class="text-right">Precio (por tn)</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalle_cargas as $c)
                                    <tr wire:key="row-{{ $loop->index }}">
                                        <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($c['fecha_carga'])->format('d/m/Y') }}</td>
                                        <td class="text-tinta-suave">{{ $c['ticket'] }}</td>
                                        <td class="text-tinta-suave">{{ $c['categoria'] }}</td>
                                        <td class="text-right text-tinta-suave">{{ number_format($c['peso_kg'], 0, ',', '.') }}</td>
                                        <td class="text-right text-tinta-suave">{{ number_format($c['precio_unitario'], 2, ',', '.') }}</td>
                                        <td class="text-right text-tinta-suave">{{ number_format($c['subtotal'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right text-sm font-semibold text-tinta">Total</th>
                                    <th class="text-right text-sm font-semibold text-tinta">{{ number_format($total_venta, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </x-ui.table-container>
                </div>
                <div class="border-t border-arena px-6 py-4 bg-corteza-suave">
                    <form wire:submit.prevent="guardarVenta">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-tinta mb-2">Observaciones (opcional)</label>
                            <textarea wire:model="observaciones" class="form-input" rows="3"></textarea>
                        </div>
                        <div class="flex gap-2 justify-between">
                            <x-ui.button variant="secondary" icon="x-mark" type="button" wire:click="limpiar">
                                Limpiar
                            </x-ui.button>
                            <x-ui.button variant="primary" icon="check" type="submit">
                                Guardar Venta
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @endif
    </div>
    @endcan
    @elseif($tab_activo === 'historial')
    <div>
        <x-ui.card class="overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-tinta mb-0">Historial de Ventas</h5>
                <div class="flex items-center gap-2 px-4 py-2 border border-arena rounded-sm bg-blanco max-w-[300px]">
                    <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                    <input type="text" wire:model.live="busqueda" placeholder="Buscar..." class="flex-1 border-0 focus:ring-0 focus:outline-none text-tinta placeholder-tinta-suave bg-blanco">
                    <button class="text-tinta-suave hover:text-tinta" wire:click="$set('busqueda', '')">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            </div>
            <div class="p-0">
                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Recibo</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-right">Monto</th>
                                <th>Estado</th>
                                <th class="text-center">Cargas</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $venta)
                                <tr wire:key="row-{{ $venta->id_recibo }}">
                                    <td class="text-tinta-suave">{{ $venta->id_recibo }}</td>
                                    <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                                    <td class="text-tinta-suave">{{ $venta->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($venta->monto, 2, ',', '.') }}</td>
                                    <td>
                                        @if($venta->activo)
                                            <x-ui.badge variant="success">Activa</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">Baja</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <x-ui.badge variant="info">{{ $venta->cargas->count() }}</x-ui.badge>
                                    </td>
                                    <td class="text-center">
                                        <x-ui.button variant="ghost" size="sm" icon="eye" wire:click="verDetalle({{ $venta->id_recibo }})" title="Ver" />
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-12 text-tinta-suave">
                                            No hay ventas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4 p-6">
                    {{ $ventas->links() }}
                </div>
            </div>
        </x-ui.card>
    </div>
    @endif

    {{-- Modal de Detalles --}}
    @if($mostrar_modal && $venta_seleccionada)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 bg-black/50">
            <div class="w-full max-w-4xl bg-blanco rounded-sm shadow-xl border border-arena">
                <div class="text-blanco px-6 py-4 flex items-center justify-between bg-pino">
                    <h5 class="flex items-center gap-2 text-lg font-semibold mb-0">
                        <flux:icon.document-text class="size-5" />
                        Detalle de Venta #{{ $venta_seleccionada->id_recibo }}
                    </h5>
                    <button type="button" class="text-blanco hover:text-corteza-suave transition-colors" wire:click="cerrarModal">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <strong class="block text-sm font-semibold text-tinta mb-1">Cliente:</strong>
                            <p class="text-tinta-suave">{{ $venta_seleccionada->cliente->razon_social ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-tinta mb-1">Fecha:</strong>
                            <p class="text-tinta-suave">{{ \Carbon\Carbon::parse($venta_seleccionada->fecha_emision)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-tinta mb-1">Estado:</strong>
                            @if($venta_seleccionada->activo)
                                <x-ui.badge variant="success">Activa</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">Dada de Baja</x-ui.badge>
                            @endif
                        </div>
                        <div>
                            @if($modo_edicion)
                                <label class="block text-sm font-semibold text-tinta mb-1"><strong>Monto:</strong></label>
                                <input type="number" wire:model="monto_edicion" step="0.1" min="0" class="form-input">
                            @else
                                <strong class="block text-sm font-semibold text-tinta mb-1">Monto Total:</strong>
                                <p class="text-tinta-suave">${{ number_format($venta_seleccionada->monto, 2, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-6">
                        <strong class="block text-sm font-semibold text-tinta mb-2">Observaciones:</strong>
                        @if($modo_edicion)
                            <textarea wire:model="obs_edicion" class="form-input" rows="2"></textarea>
                        @else
                            <p class="text-tinta-suave text-sm">{{ $venta_seleccionada->observaciones ?: 'Sin observaciones' }}</p>
                        @endif
                    </div>

                    <div>
                        <h6 class="border-b border-arena pb-2 mb-3 font-semibold text-tinta">Cargas Asociadas</h6>
                        <x-ui.table-container>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ticket</th>
                                        <th>Fecha</th>
                                        <th>Categoría</th>
                                        <th class="text-right">Peso (kg)</th>
                                        <th class="text-right">Ton</th>
                                        <th class="text-right">Precio/tn</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detalle_venta as $det)
                                        <tr wire:key="row-{{ $loop->index }}">
                                            <td class="text-tinta-suave">{{ $det['ticket'] }}</td>
                                            <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($det['fecha_carga'])->format('d/m/Y') }}</td>
                                            <td class="text-tinta-suave">{{ $det['categoria'] }}</td>
                                            <td class="text-right text-tinta-suave">{{ number_format($det['peso_kg'], 0, ',', '.') }}</td>
                                            <td class="text-right text-tinta-suave">{{ number_format($det['peso_toneladas'], 3, ',', '.') }}</td>
                                            <td class="text-right text-tinta-suave">${{ number_format($det['precio_unitario'], 2, ',', '.') }}</td>
                                            <td class="text-right text-tinta-suave">${{ number_format($det['subtotal'], 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-ui.table-container>
                    </div>
                </div>
                <div class="border-t border-arena px-6 py-4 bg-corteza-suave flex gap-2 justify-end">
                    @if($modo_edicion)
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cancelarEdicion">
                            Cancelar
                        </x-ui.button>
                        @can('editar-ventas')
                        <x-ui.button variant="primary" icon="check" wire:click="guardarEdicion">
                            Guardar Cambios
                        </x-ui.button>
                        @endcan
                    @else
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarModal">
                            Cerrar
                        </x-ui.button>
                        @if($venta_seleccionada->activo)
                            @can('editar-ventas')
                            <x-ui.button variant="secondary" icon="pencil-square" wire:click="activarEdicion">
                                Editar
                            </x-ui.button>
                            @endcan
                            @can('eliminar-ventas')
                            <x-ui.button variant="danger" icon="trash" wire:click="darDeBaja({{ $venta_seleccionada->id_recibo }})" wire:confirm="¿Está seguro de dar de baja esta venta?">
                                Dar de Baja
                            </x-ui.button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
