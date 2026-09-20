<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza-suave border-b border-arena px-6 py-4">
            <h3 class="text-lg font-semibold text-tinta">Gestion de Mantenimientos</h3>
        </div>
        <div class="p-6">

            @if (session()->has('message'))
                <x-ui.alert variant="success" class="mb-6">
                    {{ session('message') }}
                </x-ui.alert>
            @endif

            @if (session()->has('error'))
                <x-ui.alert variant="danger" class="mb-6">
                    {{ session('error') }}
                </x-ui.alert>
            @endif

            <div class="flex border-b border-arena mb-4">
                <a class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'ordenes' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
                   wire:click="cambiarTab('ordenes')"
                   href="javascript:void(0)">
                    <flux:icon.list-bullet class="size-4 inline mr-1" />
                    Ordenes Activas
                </a>
                <a class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'completadas' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
                   wire:click="cambiarTab('completadas')"
                   href="javascript:void(0)">
                    <flux:icon.check class="size-4 inline mr-1" />
                    Completadas
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-tinta mb-1.5">Maquinaria</label>
                    <select wire:model="filtro_maquinaria" class="form-input">
                        <option value="">Todas</option>
                        @foreach($maquinarias as $maq)
                            <option value="{{ $maq->id }}" wire:key="option-{{ $maq->id }}">
                                {{ $maq->modelo }} ({{ $maq->tipoMaquinaria->nombre }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-tinta mb-1.5">Tipo</label>
                    <select wire:model="filtro_tipo" class="form-input">
                        <option value="">Todos</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>
                </div>

                @if($tab_activo === 'ordenes')
                <div>
                    <label class="block text-sm font-semibold text-tinta mb-1.5">Estado</label>
                    <select wire:model="filtro_estado" class="form-input">
                        <option value="">Todos</option>
                        <option value="programado">Programado</option>
                        <option value="en curso">En Curso</option>
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-tinta mb-1.5">Desde</label>
                    <input type="date" wire:model="filtro_fecha_desde" class="form-input">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-tinta mb-1.5">Hasta</label>
                    <input type="date" wire:model="filtro_fecha_hasta" class="form-input">
                </div>

                <div class="flex items-end">
                    <x-ui.button type="button" variant="secondary" icon="arrow-path" wire:click="resetearFiltros" title="Restablecer filtros" />
                </div>
            </div>

            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Maquinaria</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Toneladas</th>
                            <th>Fecha Creacion</th>
                            @if($tab_activo === 'completadas')
                                <th>Costo Total</th>
                                <th>Fecha Completado</th>
                            @endif
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordenes as $orden)
                        <tr wire:key="row-{{ $orden->id }}">
                            <td class="text-tinta">{{ $orden->id }}</td>
                            <td>
                                <strong class="text-tinta">{{ $orden->maquinaria->modelo }}</strong><br>
                                <small class="text-tinta-suave">{{ $orden->maquinaria->tipoMaquinaria->nombre }}</small>
                            </td>
                            <td>
                                <x-ui.badge variant="{{ $orden->tipo_mantenimiento === 'preventivo' ? 'info' : 'warning' }}">
                                    {{ ucfirst($orden->tipo_mantenimiento) }}
                                </x-ui.badge>
                            </td>
                            <td>
                                @php
                                    $estadoVariant = match($orden->estado) {
                                        'programado' => 'neutral',
                                        'en curso' => 'info',
                                        'completado' => 'success',
                                        default => 'neutral'
                                    };
                                @endphp
                                <x-ui.badge variant="{{ $estadoVariant }}">{{ ucfirst($orden->estado) }}</x-ui.badge>
                            </td>
                            <td class="text-tinta-suave">{{ number_format($orden->toneladas_snapshot ?? 0, 2) }}</td>
                            <td class="text-tinta-suave">{{ $orden->created_at->format('d/m/Y H:i') }}</td>

                            @if($tab_activo === 'completadas')
                                <td class="text-tinta-suave">${{ number_format($orden->costo_total ?? 0, 2) }}</td>
                                <td class="text-tinta-suave">{{ $orden->fecha_completado ? $orden->fecha_completado->format('d/m/Y H:i') : '-' }}</td>
                            @endif

                            <td>
                                <div class="inline-flex rounded-sm shadow-xs">
                                    <x-ui.button size="sm" variant="secondary" icon="eye" wire:click="verDetalle({{ $orden->id }})" title="Ver Detalle" class="rounded-r-none" />

                                    @if($orden->estado === 'programado')
                                        <x-ui.button size="sm" icon="check" wire:click="abrirModalAprobar({{ $orden->id }})" title="Aprobar" class="rounded-none {{ ($orden->estado === 'en curso') ? 'rounded-r-sm' : '' }}" />
                                    @endif

                                    @if($orden->estado === 'en curso')
                                        <x-ui.button size="sm" icon="flag" wire:click="abrirModalCompletar({{ $orden->id }})" title="Completar" class="rounded-l-none" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="text-center py-8 text-tinta-suave">
                                No hay ordenes para mostrar
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ui.table-container>
        </div>
    </x-ui.card>

    @if($modal_aprobar && $orden_seleccionada)
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-sm shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto border border-arena">
            <div class="bg-pino text-white px-6 py-4 rounded-t-sm flex justify-between items-center">
                <h5 class="text-lg font-semibold flex items-center gap-2">
                    <flux:icon.check class="size-5" />
                    Aprobar Orden de Mantenimiento #{{ $orden_seleccionada->id }}
                </h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalAprobar"><flux:icon.x-mark class="size-5" /></button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> {{ $orden_seleccionada->maquinaria->modelo }}
                    </div>
                    <div>
                        <strong>Tipo:</strong> {{ ucfirst($orden_seleccionada->tipo_mantenimiento) }}
                    </div>
                </div>

                @if($verificacion_stock)
                    <h6 class="font-semibold text-tinta mt-4">Verificacion de Stock:</h6>

                    @if($verificacion_stock['puede_aprobar'])
                        <x-ui.alert variant="success" class="my-3" :dismissible="false">
                            Todos los insumos estan disponibles
                        </x-ui.alert>
                    @else
                        <x-ui.alert variant="danger" class="my-3" :dismissible="false">
                            Stock insuficiente para aprobar
                        </x-ui.alert>
                    @endif

                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Requerido</th>
                                    <th>Disponible</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($verificacion_stock['kit'] as $item)
                                <tr class="{{ in_array($item['insumo_id'], array_column($verificacion_stock['insuficientes'], 'insumo_id')) ? 'bg-tierra-suave' : 'bg-musgo-suave' }}" wire:key="row-{{ $item['insumo_id'] }}">
                                    <td>{{ $item['nombre'] }}</td>
                                    <td>{{ $item['cantidad_requerida'] }}</td>
                                    <td>{{ $item['stock_disponible'] }}</td>
                                    <td>
                                        @if($item['stock_disponible'] >= $item['cantidad_requerida'])
                                            <span class="text-musgo flex items-center gap-1"><flux:icon.check class="size-3" /> OK</span>
                                        @else
                                            <span class="text-tierra flex items-center gap-1"><flux:icon.x-mark class="size-3" /> Faltan {{ $item['cantidad_requerida'] - $item['stock_disponible'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>
                @endif
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-corteza-suave border-t border-arena rounded-b-sm">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarModalAprobar">
                    Cancelar
                </x-ui.button>
                @if($verificacion_stock && $verificacion_stock['puede_aprobar'])
                    <x-ui.button type="button" icon="check" wire:click="aprobarOrden">
                        Aprobar Orden
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($modal_completar && $orden_seleccionada)
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-sm shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto border border-arena">
            <div class="bg-pino text-white px-6 py-4 rounded-t-sm flex justify-between items-center">
                <h5 class="text-lg font-semibold flex items-center gap-2">
                    <flux:icon.flag class="size-5" />
                    Completar Mantenimiento #{{ $orden_seleccionada->id }}
                </h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalCompletar"><flux:icon.x-mark class="size-5" /></button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> {{ $orden_seleccionada->maquinaria->modelo }}
                    </div>
                    <div>
                        <strong>Tipo:</strong> {{ ucfirst($orden_seleccionada->tipo_mantenimiento) }}
                    </div>
                    <div>
                        <strong>Toneladas:</strong> {{ number_format($orden_seleccionada->toneladas_snapshot, 2) }}
                    </div>
                </div>

                <h6 class="font-semibold text-tinta">Insumos Utilizados:</h6>
                <x-ui.table-container class="mb-4">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Cantidad</th>
                                <th>Stock Disponible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insumos_usados as $index => $insumo)
                            <tr wire:key="row-{{ $index }}">
                                <td>
                                    <select wire:model="insumos_usados.{{ $index }}.insumo_id"
                                            wire:change="actualizarInsumo({{ $index }}, $event.target.value)"
                                            class="form-input"
                                            @if($insumo['es_obligatorio']) disabled @endif>
                                        <option value="">Seleccionar...</option>
                                        @foreach($insumos_disponibles as $ins)
                                            <option value="{{ $ins->id_insumo }}" wire:key="option-{{ $ins->id_insumo }}">{{ $ins->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error("insumos_usados.{$index}.insumo_id")
                                        <span class="text-tierra text-xs">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number"
                                           wire:model="insumos_usados.{{ $index }}.cantidad"
                                           class="form-input"
                                           step="0.1" min="0">
                                    @error("insumos_usados.{$index}.cantidad")
                                        <span class="text-tierra text-xs">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    @php
                                        $stockDispo = $insumo['stock_disponible'] ?? 0;
                                        $cantidad = $insumo['cantidad'] ?? 0;
                                    @endphp
                                    <x-ui.badge variant="{{ $stockDispo >= $cantidad ? 'success' : 'danger' }}">
                                        {{ number_format($stockDispo, 2) }}
                                    </x-ui.badge>
                                    @if($stockDispo < $cantidad && $cantidad > 0)
                                        <br><small class="text-tierra">Faltan {{ number_format($cantidad - $stockDispo, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!$insumo['es_obligatorio'])
                                        <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminarInsumo({{ $index }})" />
                                    @else
                                        <x-ui.badge variant="info">Requerido</x-ui.badge>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-container>

                <x-ui.button type="button" variant="secondary" icon="plus" wire:click="agregarInsumo" class="mb-4">
                    Agregar Insumo
                </x-ui.button>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-1.5">Costo Mano de Obra</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 py-2.5 bg-corteza-suave border border-r-0 border-arena rounded-l-sm text-sm text-tinta-suave">$</span>
                            <input type="number"
                                   wire:model="costo_mano_obra"
                                   class="flex-1 form-input rounded-l-none"
                                   step="0.1" min="0">
                        </div>
                        @error('costo_mano_obra')
                            <span class="text-tierra text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-corteza-suave border-t border-arena rounded-b-sm">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarModalCompletar">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="button" icon="flag" wire:click="completarMantenimiento">
                    Completar Mantenimiento
                </x-ui.button>
            </div>
        </div>
    </div>
    @endif

    @if($modal_detalle && $detalle_orden)
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-sm shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto border border-arena">
            <div class="bg-corteza text-white px-6 py-4 rounded-t-sm flex justify-between items-center">
                <h5 class="text-lg font-semibold flex items-center gap-2">
                    <flux:icon.eye class="size-5" />
                    Detalle Orden #{{ $detalle_orden->id }}
                </h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalDetalle"><flux:icon.x-mark class="size-5" /></button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> {{ $detalle_orden->maquinaria->modelo }}<br>
                        <strong>Tipo Maquinaria:</strong> {{ $detalle_orden->maquinaria->tipoMaquinaria->nombre }}
                    </div>
                    <div>
                        <strong>Tipo Mantenimiento:</strong> {{ ucfirst($detalle_orden->tipo_mantenimiento) }}<br>
                        <strong>Estado:</strong>
                        <x-ui.badge variant="{{ $detalle_orden->estado === 'completado' ? 'success' : 'info' }}">
                            {{ ucfirst($detalle_orden->estado) }}
                        </x-ui.badge>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Toneladas Snapshot:</strong> {{ number_format($detalle_orden->toneladas_snapshot, 2) }}
                    </div>
                    <div>
                        <strong>Fecha Creacion:</strong> {{ $detalle_orden->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>

                @if($detalle_orden->estado === 'completado')
                    <hr class="border-arena my-4">
                    <h6 class="font-semibold text-tinta mb-3">Insumos Utilizados:</h6>
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalle_orden->mantenimientoInsumos as $item)
                                <tr wire:key="row-{{ $item->id }}">
                                    <td class="text-tinta">{{ $item->insumo->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $item->cantidad }}</td>
                                    <td class="text-tinta-suave">${{ number_format($item->costo_unitario, 2) }}</td>
                                    <td class="text-tinta-suave">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <strong>Costo Mano de Obra:</strong> ${{ number_format($detalle_orden->costo_mano_obra, 2) }}
                        </div>
                        <div>
                            <strong>Costo Total:</strong>
                            <span class="text-xl font-bold text-pino">
                                ${{ number_format($detalle_orden->costo_total, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>Fecha Completado:</strong> {{ $detalle_orden->fecha_completado->format('d/m/Y H:i') }}
                    </div>
                @endif

                @if($detalle_orden->descripcion)
                    <hr class="border-arena my-4">
                    <strong>Descripcion:</strong>
                    <p class="text-tinta-suave">{{ $detalle_orden->descripcion }}</p>
                @endif
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-corteza-suave border-t border-arena rounded-b-sm">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarModalDetalle">
                    Cerrar
                </x-ui.button>
            </div>
        </div>
    </div>
    @endif

</div>
