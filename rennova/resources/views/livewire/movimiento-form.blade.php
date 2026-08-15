<div>
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
        <div class="bg-green-600 text-white px-6 py-4">
            <h5 class="text-lg font-semibold mb-0">📦 Movimientos de Insumos</h5>
        </div>
        <div class="p-6">
            <div id="alertaMovimiento"></div>
            
            <!-- Errores generales de validación de movimiento -->
            @if ($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        ⚠️
                        <div>
                            <strong class="text-red-800">Errores en el movimiento:</strong>
                            <ul class="list-disc list-inside mt-2 text-red-700 text-sm">
                                @error('movimiento_id_insumo') <li>{{ $message }}</li> @enderror
                                @error('movimiento_cantidad') <li>{{ $message }}</li> @enderror
                                @error('movimiento_motivo') <li>{{ $message }}</li> @enderror
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                <h6 class="font-semibold mb-4 text-slate-900">➡️ Registrar Consumo</h6>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Insumo <span class="text-red-500">*</span></label>
                        <select wire:model.live="movimiento_id_insumo" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_id_insumo') ring-2 ring-red-500 @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
                            @endforeach
                        </select>
                        @if($stock_disponible_insumo !== null)
                            <small class="text-slate-600 mt-1 block">
                                Stock disponible: <strong class="{{ $stock_disponible_insumo > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $stock_disponible_insumo }}</strong>
                            </small>
                        @endif
                        @error('movimiento_id_insumo') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Cantidad <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="movimiento_cantidad" step="0.1" min="0" 
                               @if($stock_disponible_insumo !== null) max="{{ $stock_disponible_insumo }}" @endif
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_cantidad') ring-2 ring-red-500 @enderror" placeholder="0.00">
                        @error('movimiento_cantidad') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Motivo <span class="text-red-500">*</span></label>
                        <select wire:model="movimiento_motivo" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_motivo') ring-2 ring-red-500 @enderror">
                            <option value="Producción">Producción</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Varios">Varios</option>
                        </select>
                        @error('movimiento_motivo') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click.prevent="agregarMovimiento" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                            <span wire:loading.remove wire:target="agregarMovimiento">➕ Agregar</span>
                            <span wire:loading wire:target="agregarMovimiento"><svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                        </button>
                    </div>
                </div>
            </div>

            @if(count($movimientos) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-slate-100 border-b border-slate-300">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Insumo</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Cantidad</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Motivo</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Observaciones</th>
                                <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movimientos as $index => $mov)
                                <tr class="border-b border-slate-200 hover:bg-slate-50" wire:key="row-{{ $index }}">
                                    <td class="px-4 py-2"><strong>{{ $mov['nombre_insumo'] }}</strong></td>
                                    <td class="px-4 py-2">{{ number_format($mov['cantidad'], 2) }} {{ $mov['unidad'] ?? '' }}</td>
                                    <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $mov['motivo'] }}</span></td>
                                    <td class="px-4 py-2"><small>{{ $mov['observaciones'] ?? '-' }}</small></td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" wire:click.prevent="eliminarMovimiento({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 text-sm">
                    ℹ️ Sin movimientos registrados. Esta sección es opcional.
                </div>
            @endif
        </div>
    </div>
</div>
