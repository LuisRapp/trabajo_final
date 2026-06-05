<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-receipt"></i> Ventas
        </h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="flex-1 font-medium">{{ session('error') }}</span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Venta', 'icon' => 'plus-circle', 'can' => auth()->user()->can('crear-ventas')],
        ['value' => 'historial', 'label' => 'Historial de Ventas', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
    @can('crear-ventas')
    <div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">Buscar cargas pendientes</h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="buscarCargasPendientes" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Cliente</label>
                        <select wire:model="id_cliente" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors">
                            <option value="">Seleccione cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Desde</label>
                        <input type="date" wire:model="fecha_desde" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_hasta" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors">
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (!empty($detalle_cargas))
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="text-lg font-semibold text-slate-800 mb-0">Resultados ({{ count($detalle_cargas) }})</h5>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Ticket</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Categoría</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Peso Neto (kg)</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Precio (por tn)</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($detalle_cargas as $c)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $loop->index }}">
                                        <td class="px-3 py-3 text-slate-600">{{ \Carbon\Carbon::parse($c['fecha_carga'])->format('d/m/Y') }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $c['ticket'] }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $c['categoria'] }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($c['peso_kg'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($c['precio_unitario'], 2, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($c['subtotal'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                                <tr>
                                    <th colspan="5" class="px-3 py-3 text-right text-sm font-semibold text-slate-800">Total</th>
                                    <th class="px-3 py-3 text-right text-sm font-semibold text-slate-800">{{ number_format($total_venta, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="border-t border-slate-200 px-6 py-4 bg-slate-50">
                    <form wire:submit.prevent="guardarVenta">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Observaciones (opcional)</label>
                            <textarea wire:model="observaciones" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors" rows="3"></textarea>
                        </div>
                        <div class="flex gap-2 justify-between">
                            <button type="button" wire:click="limpiar" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-save"></i> Guardar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
    @endcan
    @elseif($tab_activo === 'historial')
    <div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">Historial de Ventas</h5>
                <div class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg bg-white" style="max-width: 300px;">
                    <i class="bi bi-search text-slate-500"></i>
                    <input type="text" wire:model.live="busqueda" placeholder="Buscar..." class="flex-1 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400 bg-white">
                    <button class="text-slate-500 hover:text-slate-700" wire:click="$set('busqueda', '')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID Recibo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Cliente</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Monto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Cargas</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($ventas as $venta)
                                <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $venta->id_recibo }}">
                                    <td class="px-3 py-3 text-slate-600">{{ $venta->id_recibo }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $venta->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="px-3 py-3 text-right text-slate-600">${{ number_format($venta->monto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3">
                                        @if($venta->activo)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Activa</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Baja</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $venta->cargas->count() }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200 text-sm font-medium" wire:click="verDetalle({{ $venta->id_recibo }})">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                    <x-empty-state :colspan="7" message="No hay ventas registradas." />
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
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="w-full max-w-4xl bg-white rounded-lg shadow-xl">
                <div class="text-white px-6 py-4 flex items-center justify-between rounded-t-lg" style="background-color: #2d7a4f;">
                    <h5 class="flex items-center gap-2 text-lg font-semibold mb-0">
                        <i class="bi bi-receipt"></i> Detalle de Venta #{{ $venta_seleccionada->id_recibo }}
                    </h5>
                    <button type="button" class="text-white hover:text-gray-200 transition-colors" wire:click="cerrarModal">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <strong class="block text-sm font-semibold text-slate-700 mb-1">Cliente:</strong>
                            <p class="text-slate-600">{{ $venta_seleccionada->cliente->razon_social ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-slate-700 mb-1">Fecha:</strong>
                            <p class="text-slate-600">{{ \Carbon\Carbon::parse($venta_seleccionada->fecha_emision)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-slate-700 mb-1">Estado:</strong>
                            @if($venta_seleccionada->activo)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Activa</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Dada de Baja</span>
                            @endif
                        </div>
                        <div>
                            @if($modo_edicion)
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><strong>Monto:</strong></label>
                                <input type="number" wire:model="monto_edicion" step="0.1" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors">
                            @else
                                <strong class="block text-sm font-semibold text-slate-700 mb-1">Monto Total:</strong>
                                <p class="text-slate-600">${{ number_format($venta_seleccionada->monto, 2, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-6">
                        <strong class="block text-sm font-semibold text-slate-700 mb-2">Observaciones:</strong>
                        @if($modo_edicion)
                            <textarea wire:model="obs_edicion" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors" rows="2"></textarea>
                        @else
                            <p class="text-slate-500 text-sm">{{ $venta_seleccionada->observaciones ?: 'Sin observaciones' }}</p>
                        @endif
                    </div>

                    <div>
                        <h6 class="border-b border-slate-200 pb-2 mb-3 font-semibold text-slate-800">Cargas Asociadas</h6>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50">
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600">Ticket</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600">Fecha</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600">Categoría</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-600">Peso (kg)</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-600">Ton</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-600">Precio/tn</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-600">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($detalle_venta as $det)
                                        <tr class="hover:bg-slate-50" wire:key="row-{{ $loop->index }}">
                                            <td class="px-3 py-2 text-slate-600">{{ $det['ticket'] }}</td>
                                            <td class="px-3 py-2 text-slate-600">{{ \Carbon\Carbon::parse($det['fecha_carga'])->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 text-slate-600">{{ $det['categoria'] }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">{{ number_format($det['peso_kg'], 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">{{ number_format($det['peso_toneladas'], 3, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">${{ number_format($det['precio_unitario'], 2, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">${{ number_format($det['subtotal'], 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 flex gap-2 justify-end">
                    @if($modo_edicion)
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm" wire:click="cancelarEdicion">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        @can('editar-ventas')
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'" wire:click="guardarEdicion">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                        @endcan
                    @else
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm" wire:click="cerrarModal">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                        @if($venta_seleccionada->activo)
                            @can('editar-ventas')
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium text-sm" wire:click="activarEdicion">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            @endcan
                            @can('eliminar-ventas')
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm" wire:click="darDeBaja({{ $venta_seleccionada->id_recibo }})" wire:confirm="¿Está seguro de dar de baja esta venta?">
                                <i class="bi bi-trash"></i> Dar de Baja
                            </button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
