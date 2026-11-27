<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.document-text class="size-6" />
            Ventas
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Facturá las cargas pendientes y consultá el historial con su comprobante.
        </p>
    </div>

    {{-- Los avisos de los paneles se muestran dentro de cada panel; aca solo
         cuando no hay ningun panel abierto, para que no queden detras. --}}
    @if (! $panel_nueva && ! ($mostrar_modal && $venta_seleccionada))
        @if (session()->has('message'))
            <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
        @endif

        @if (session()->has('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
        @endif
    @endif

    <!-- Barra de herramientas -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-1 flex-wrap items-center gap-2">
            <div class="flex min-w-[240px] flex-1 items-center gap-2 rounded-sm border border-arena bg-white px-3 py-2 sm:max-w-sm">
                <flux:icon.magnifying-glass class="size-4 shrink-0 text-tinta-suave" />
                <input
                    autocomplete="off"
                    type="text"
                    wire:model.live.debounce.400ms="busqueda"
                    placeholder="Buscar por cliente, número o monto..."
                    class="flex-1 border-0 bg-white text-sm text-tinta placeholder-tinta-suave focus:ring-0 focus:outline-none"
                >
                @if ($busqueda !== '')
                    <button type="button" class="text-tinta-suave hover:text-tinta" wire:click="$set('busqueda', '')" title="Limpiar búsqueda">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                @endif
            </div>

            <select wire:model.live="filtro_estado" class="form-input w-auto">
                <option value="">Todas</option>
                <option value="facturadas">Facturadas</option>
                <option value="bajas">Dadas de baja</option>
            </select>
        </div>

        @can('crear-ventas')
            <x-ui.button variant="primary" icon="plus" wire:click="abrirNuevaVenta">
                Nueva venta
            </x-ui.button>
        @endcan
    </div>

    <!-- Listado -->
    <x-ui.card class="overflow-hidden">
        @if ($ventas->count() > 0)
            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">N.º</th>
                            <th style="width: 14%;">Fecha</th>
                            <th style="width: 28%;">Cliente</th>
                            <th style="width: 10%;" class="text-center">Cargas</th>
                            <th style="width: 12%;" class="text-right">Toneladas</th>
                            <th style="width: 14%;" class="text-right">Total</th>
                            <th style="width: 12%;">Estado</th>
                            <th style="width: 8%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventas as $venta)
                            @php
                                $toneladas = $venta->cargas->sum(fn ($carga) => (float) $carga->pivot->peso_toneladas);
                            @endphp
                            <tr wire:key="venta-{{ $venta->id_recibo }}">
                                <td class="font-medium text-tinta">
                                    #{{ str_pad((string) $venta->id_recibo, 6, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                                <td class="text-tinta">{{ $venta->cliente?->razon_social ?? '—' }}</td>
                                <td class="text-center"><x-ui.badge variant="neutral">{{ $venta->cargas->count() }}</x-ui.badge></td>
                                <td class="text-right text-tinta-suave">{{ number_format($toneladas, 3, ',', '.') }}</td>
                                <td class="text-right font-medium text-tinta">$ {{ number_format((float) $venta->monto, 2, ',', '.') }}</td>
                                <td>
                                    <x-ui.badge variant="{{ $venta->trashed() ? 'danger' : 'success' }}">
                                        {{ $venta->trashed() ? 'Dada de baja' : 'Facturada' }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-right">
                                    <x-ui.button variant="secondary" size="sm" icon="eye"
                                        wire:click="verDetalle({{ $venta->id_recibo }})" title="Abrir venta" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.table-container>

            <div class="border-t border-arena px-4 py-3">
                {{ $ventas->links() }}
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <flux:icon.document-text class="mx-auto size-8 text-arena-oscura" />
                <p class="mt-3 font-medium text-tinta-suave">No hay ventas para mostrar.</p>
                <p class="mt-1 text-xs text-arena-oscura">
                    {{ $busqueda !== '' || $filtro_estado !== '' ? 'Probá con otra búsqueda o quitá el filtro.' : 'Cuando factures cargas pendientes van a aparecer acá.' }}
                </p>
            </div>
        @endif
    </x-ui.card>

    {{-- Panel: nueva venta --}}
    @if ($panel_nueva)
        <div class="fixed inset-0 z-50 flex justify-end bg-tinta/40 backdrop-blur-[1px]">
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-arena px-5 py-4">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-tinta">
                            <flux:icon.document-text class="size-5" />
                            Nueva venta
                        </h2>
                        <p class="mt-0.5 text-xs text-tinta-suave">
                            {{ $paso_nueva === 1 ? 'Paso 1 de 2 · Elegí el cliente y el período' : 'Paso 2 de 2 · Revisá las cargas y facturá' }}
                        </p>
                    </div>
                    <button type="button" class="text-tinta-suave transition-colors hover:text-tinta" wire:click="cerrarNuevaVenta" title="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    @if (session()->has('message'))
                        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
                    @endif

                    @if (session()->has('error'))
                        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">{{ session('error') }}</x-ui.alert>
                    @endif

                    @if ($paso_nueva === 1)
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-tinta">Cliente <span class="text-tierra">*</span></label>
                                <x-ui.combobox modelo="id_cliente" :opciones="$opcionesClientes" :seleccion="$id_cliente" placeholder="Elegí el cliente..." />
                                @error('id_cliente') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-tinta">Desde <span class="text-tierra">*</span></label>
                                    <input autocomplete="off" type="date" wire:model="fecha_desde" class="form-input @error('fecha_desde') border-tierra bg-tierra-suave @enderror">
                                    @error('fecha_desde') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-tinta">Hasta <span class="text-tierra">*</span></label>
                                    <input autocomplete="off" type="date" wire:model="fecha_hasta" class="form-input @error('fecha_hasta') border-tierra bg-tierra-suave @enderror">
                                    @error('fecha_hasta') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <p class="text-xs text-tinta-suave">
                                Solo se muestran las cargas pendientes del cliente en ese período, con el precio de su lista.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="rounded-sm border border-arena bg-hueso px-4 py-3 text-sm">
                                <div class="flex justify-between gap-3">
                                    <span class="text-tinta-suave">Cliente</span>
                                    <span class="font-medium text-tinta">{{ collect($opcionesClientes)->firstWhere('value', $id_cliente)['label'] ?? '—' }}</span>
                                </div>
                                <div class="mt-1 flex justify-between gap-3">
                                    <span class="text-tinta-suave">Período</span>
                                    <span class="text-tinta">{{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <x-ui.table-container>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Fecha</th>
                                            <th>Categoría</th>
                                            <th class="text-right">Kg</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detalle_cargas as $carga)
                                            <tr wire:key="carga-{{ $loop->index }}">
                                                <td class="font-medium text-tinta">{{ $carga['ticket'] }}</td>
                                                <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($carga['fecha_carga'])->format('d/m/Y') }}</td>
                                                <td class="text-tinta-suave">{{ $carga['categoria'] }}</td>
                                                <td class="text-right text-tinta-suave">{{ number_format((float) $carga['peso_kg'], 0, ',', '.') }}</td>
                                                <td class="text-right text-tinta">$ {{ number_format((float) $carga['subtotal'], 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-ui.table-container>

                            <div class="flex items-center justify-between rounded-sm border border-pino/30 bg-pino-suave px-4 py-3">
                                <span class="text-sm font-medium text-tinta">{{ count($detalle_cargas) }} {{ count($detalle_cargas) === 1 ? 'carga' : 'cargas' }} a facturar</span>
                                <span class="text-lg font-bold text-pino">$ {{ number_format((float) $total_venta, 2, ',', '.') }}</span>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-tinta">Observaciones (opcional)</label>
                                <textarea wire:model="observaciones" rows="3" class="form-input" placeholder="Ej: entrega en planta, retiro con transporte propio"></textarea>
                                @error('observaciones') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-arena bg-hueso px-5 py-3">
                    @if ($paso_nueva === 1)
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarNuevaVenta">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" icon="magnifying-glass" wire:click="buscarCargasPendientes">Buscar cargas</x-ui.button>
                    @else
                        <x-ui.button variant="secondary" icon="arrow-left" wire:click="volverPaso1">Volver</x-ui.button>
                        <x-ui.button variant="primary" icon="check" wire:click="guardarVenta">Registrar venta</x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Panel: detalle de la venta --}}
    @if ($mostrar_modal && $venta_seleccionada)
        <div class="fixed inset-0 z-50 flex justify-end bg-tinta/40 backdrop-blur-[1px]">
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-arena px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-tinta">
                            Venta #{{ str_pad((string) $venta_seleccionada->id_recibo, 6, '0', STR_PAD_LEFT) }}
                        </h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-tinta-suave">
                            <x-ui.badge variant="{{ $venta_seleccionada->trashed() ? 'danger' : 'success' }}">
                                {{ $venta_seleccionada->trashed() ? 'Dada de baja' : 'Facturada' }}
                            </x-ui.badge>
                            <span>{{ \Carbon\Carbon::parse($venta_seleccionada->fecha_emision)->format('d/m/Y') }}</span>
                            <span>·</span>
                            <span>{{ $venta_seleccionada->cliente?->razon_social ?? 'Sin cliente' }}</span>
                        </div>
                    </div>
                    <button type="button" class="text-tinta-suave transition-colors hover:text-tinta" wire:click="cerrarModal" title="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    @if (session()->has('message'))
                        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
                    @endif

                    @if (session()->has('error'))
                        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">{{ session('error') }}</x-ui.alert>
                    @endif

                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Fecha</th>
                                    <th>Categoría</th>
                                    <th class="text-right">Ton</th>
                                    <th class="text-right">Precio/tn</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detalle_venta as $det)
                                    <tr wire:key="det-{{ $loop->index }}">
                                        <td class="font-medium text-tinta">{{ $det['ticket'] }}</td>
                                        <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($det['fecha_carga'])->format('d/m/Y') }}</td>
                                        <td class="text-tinta-suave">{{ $det['categoria'] }}</td>
                                        <td class="text-right text-tinta-suave">{{ number_format((float) $det['peso_toneladas'], 3, ',', '.') }}</td>
                                        <td class="text-right text-tinta-suave">$ {{ number_format((float) $det['precio_unitario'], 2, ',', '.') }}</td>
                                        <td class="text-right text-tinta">$ {{ number_format((float) $det['subtotal'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>

                    <div class="mt-4 flex items-center justify-between rounded-sm border border-arena bg-hueso px-4 py-3">
                        <span class="text-sm text-tinta-suave">Total</span>
                        @if ($modo_edicion)
                            <input autocomplete="off" type="number" wire:model="monto_edicion" step="0.01" min="0" class="form-input w-32 text-right">
                        @else
                            <span class="text-lg font-bold text-pino">$ {{ number_format((float) $venta_seleccionada->monto, 2, ',', '.') }}</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-semibold text-tinta">Observaciones</label>
                        @if ($modo_edicion)
                            <textarea wire:model="obs_edicion" rows="2" class="form-input"></textarea>
                            @error('obs_edicion') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            @error('monto_edicion') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        @else
                            <p class="text-sm text-tinta-suave">{{ $venta_seleccionada->observaciones ?: 'Sin observaciones.' }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-arena bg-hueso px-5 py-3">
                    @if ($modo_edicion)
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cancelarEdicion">Cancelar</x-ui.button>
                        @can('editar-ventas')
                            <x-ui.button variant="primary" icon="check" wire:click="guardarEdicion">Guardar cambios</x-ui.button>
                        @endcan
                    @else
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarModal">Cerrar</x-ui.button>
                        @can('ver-ventas')
                            <x-ui.button variant="secondary" icon="document-arrow-down"
                                href="{{ route('ventas.comprobante', ['venta' => $venta_seleccionada->id_recibo]) }}"
                                target="_blank" rel="noopener">
                                Comprobante
                            </x-ui.button>
                        @endcan
                        @if (! $venta_seleccionada->trashed())
                            @can('editar-ventas')
                                <x-ui.button variant="secondary" icon="pencil-square" wire:click="activarEdicion">Editar</x-ui.button>
                            @endcan
                            @can('eliminar-ventas')
                                <x-ui.confirmador accion="darDeBaja" :parametros="[$venta_seleccionada->id_recibo]"
                                    mensaje="¿Dar de baja la venta #{{ $venta_seleccionada->id_recibo }}?"
                                    detalle="Las cargas vuelven a quedar pendientes de venta."
                                    etiqueta="Dar de baja"
                                    texto="Dar de baja" />
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
