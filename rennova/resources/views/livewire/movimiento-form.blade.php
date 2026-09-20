<div class="w-full">
    <x-ui.card class="mb-6 overflow-hidden">
        <div class="bg-pino text-white px-4 py-3">
            <h5 class="text-sm font-semibold">Movimientos de Insumos</h5>
        </div>
        <div class="p-4">
            <div id="alertaMovimiento"></div>

            <!-- Errores generales de validacion de movimiento -->
            @if ($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo'))
                <x-ui.alert variant="danger" class="mb-4" dismissible="false">
                    <strong>Errores en el movimiento:</strong>
                    <ul class="list-disc list-inside mt-2 text-sm">
                        @error('movimiento_id_insumo') <li>{{ $message }}</li> @enderror
                        @error('movimiento_cantidad') <li>{{ $message }}</li> @enderror
                        @error('movimiento_motivo') <li>{{ $message }}</li> @enderror
                    </ul>
                </x-ui.alert>
            @endif

            <div class="border border-arena rounded-sm p-4 mb-4 bg-hueso">
                <h6 class="text-sm font-semibold mb-4 text-tinta">Registrar Consumo</h6>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Insumo <span class="text-tierra">*</span></label>
                        <select wire:model.live="movimiento_id_insumo" class="form-input @error('movimiento_id_insumo') border-tierra bg-tierra-suave @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                            @endforeach
                        </select>
                        @if($stock_disponible_insumo !== null)
                            <small class="text-tinta-suave mt-1 block">
                                Stock disponible: <strong class="{{ $stock_disponible_insumo > 0 ? 'text-musgo' : 'text-tierra' }}">{{ $stock_disponible_insumo }}</strong>
                            </small>
                        @endif
                        @error('movimiento_id_insumo') <div class="text-tierra text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Cantidad <span class="text-tierra">*</span></label>
                        <input type="number" wire:model="movimiento_cantidad" step="0.1" min="0"
                               @if($stock_disponible_insumo !== null) max="{{ $stock_disponible_insumo }}" @endif
                               class="form-input @error('movimiento_cantidad') border-tierra bg-tierra-suave @enderror" placeholder="0.00">
                        @error('movimiento_cantidad') <div class="text-tierra text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Motivo <span class="text-tierra">*</span></label>
                        <select wire:model="movimiento_motivo" class="form-input @error('movimiento_motivo') border-tierra bg-tierra-suave @enderror">
                            <option value="Produccion">Produccion</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Varios">Varios</option>
                        </select>
                        @error('movimiento_motivo') <div class="text-tierra text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="flex items-end">
                        <x-ui.button variant="primary" icon="plus" wire:click.prevent="agregarMovimiento" class="w-full" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                            <span wire:loading.remove wire:target="agregarMovimiento">Agregar</span>
                            <span wire:loading wire:target="agregarMovimiento">
                                <flux:icon.arrow-path class="size-4 animate-spin" />
                            </span>
                        </x-ui.button>
                    </div>
                </div>
            </div>

            @if(count($movimientos) > 0)
                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Observaciones</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movimientos as $index => $mov)
                                <tr wire:key="row-{{ $index }}">
                                    <td><strong>{{ $mov['nombre_insumo'] }}</strong></td>
                                    <td>{{ number_format($mov['cantidad'], 2) }} {{ $mov['unidad'] ?? '' }}</td>
                                    <td><x-ui.badge variant="neutral">{{ $mov['motivo'] }}</x-ui.badge></td>
                                    <td><span class="text-tinta-suave">{{ $mov['observaciones'] ?? '-' }}</span></td>
                                    <td class="text-center">
                                        <x-ui.button variant="danger" size="sm" icon="trash" wire:click.prevent="eliminarMovimiento({{ $index }})" title="Eliminar" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-container>
            @else
                <div class="p-4 bg-hueso border border-arena rounded-sm text-tinta-suave text-sm">
                    Sin movimientos registrados. Esta seccion es opcional.
                </div>
            @endif
        </div>
    </x-ui.card>
</div>
