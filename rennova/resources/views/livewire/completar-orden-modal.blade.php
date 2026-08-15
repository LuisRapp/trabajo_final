<div>
    @once
        <style>
            .lw-modal-overlay {
                position: fixed;
                inset: 0;
                background-color: rgba(15, 23, 42, 0.6);
                z-index: 2050;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 2rem 1rem;
                overflow-y: auto;
            }
            .lw-modal-card {
                width: min(900px, 100%);
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
                overflow: hidden;
            }
            .lw-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.5rem;
                background: #198754;
                color: #fff;
            }
            .lw-modal-body { padding: 1.5rem; }
            .lw-modal-footer {
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: .75rem;
                background: #f8f9fa;
            }
            .lw-close {
                background: transparent;
                border: none;
                color: inherit;
                font-size: 1.25rem;
                line-height: 1;
                cursor: pointer;
            }
        </style>
    @endonce

    @if($mostrarModal)
        <div class="lw-modal-overlay" wire:key="modal-overlay-completar">
            <div class="lw-modal-card" wire:key="modal-card-completar-{{ $orden_completar_id }}">
                <div class="lw-modal-header bg-brand">
                    <h5 class="mb-0 flex items-center gap-2">✓ Completar Orden de Mantenimiento</h5>
                    <button type="button" class="lw-close" wire:click="cerrarModal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="lw-modal-body">
                    @if (session()->has('error'))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 flex items-start gap-3">
                            ⚠️
                            <div class="flex-1">{{ session('error') }}</div>
                            <button type="button" class="text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                                ✕
                            </button>
                        </div>
                    @endif

                    @error('general')
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 flex items-start gap-3">
                            ⚠️
                            <div class="flex-1">{{ $message }}</div>
                            <button type="button" class="text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                                ✕
                            </button>
                        </div>
                    @enderror

                    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700 text-sm">
                        <strong>Orden #{{ $orden_completar_info['id'] ?? 'N/A' }}</strong><br>
                        <strong>Maquinaria:</strong> {{ $orden_completar_info['maquinaria'] ?? 'N/A' }}<br>
                        <strong>Tipo:</strong> {{ $orden_completar_info['tipo'] ?? 'N/A' }}<br>
                        <strong>Fecha Inicio:</strong> {{ isset($orden_completar_info['fecha_inicio']) ? \Carbon\Carbon::parse($orden_completar_info['fecha_inicio'])->format('d/m/Y') : 'N/A' }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Finalización <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_fin_completar" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_fin_completar') ring-2 ring-red-500 @enderror">
                            @error('fecha_fin_completar') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Costo Total (opcional)</label>
                            <div class="flex items-center gap-2">
                                <span class="px-4 py-3 bg-slate-100 text-slate-600 rounded-lg border border-slate-300">$</span>
                                <input type="number" wire:model="costo_total_completar" step="1" min="0" class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('costo_total_completar') ring-2 ring-red-500 @enderror" placeholder="0.00">
                            </div>
                            @error('costo_total_completar') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            <small class="text-slate-500 text-xs mt-1 block">Costo adicional (ej: mano de obra). El costo de los insumos se calcula automáticamente al momento del cierre.</small>
                        </div>
                    </div>

                    <hr class="my-6 border-slate-200">
                    <h6 class="mb-4 font-semibold text-slate-700 flex items-center gap-2">
                        📦 Insumos Utilizados
                        @if(!$orden_es_correctivo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">Kit Preventivo</span>
                        @endif
                    </h6>

                    @foreach($insumos_usados as $index => $insumo)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200" wire:key="insumo-{{ $index }}">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Insumo</label>
                                <select wire:model.live="insumos_usados.{{ $index }}.id_insumo" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors text-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($insumosDisponibles as $ins)
                                        <option value="{{ $ins->id_insumo }}" wire:key="option-{{ $ins->id_insumo }}">
                                            {{ $ins->nombre }} (Stock: {{ number_format($ins->stock_disponible, 2) }} - ${{ number_format($ins->precio_promedio, 2) }}/u)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Cantidad a usar</label>
                                <input type="number" wire:model="insumos_usados.{{ $index }}.cantidad" step="0.1" min="0" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors" placeholder="0">
                                @if(!empty($insumo['id_insumo']))
                                    @php
                                        $stockDisponible = optional($insumosDisponibles->firstWhere('id_insumo', $insumo['id_insumo']))->stock_disponible ?? 0;
                                    @endphp
                                    <small class="text-slate-500 text-xs mt-1 block">Disponible: {{ number_format($stockDisponible, 2) }}</small>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Precio Unitario</label>
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-600">$</span>
                                    <input type="number" wire:model="insumos_usados.{{ $index }}.precio_unitario" step="0.01" min="0" class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors bg-slate-50" readonly>
                                </div>
                            </div>
                            <div class="flex items-end justify-end">
                                @if($index > 0 || count($insumos_usados) > 1)
                                    <button type="button" wire:click="eliminarInsumo({{ $index }})" class="inline-flex items-center px-2 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200" title="Eliminar">
                                        🗑️
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <button type="button" wire:click="agregarInsumo" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200 font-medium text-sm mt-4">
                        ➕ Agregar Insumo
                    </button>
                </div>
                <div class="lw-modal-footer">
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded transition-colors font-medium text-sm" wire:click="cerrarModal">
                        ✕ Cancelar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded transition-colors font-medium text-sm bg-brand hover:bg-brand-hover" wire:click="completarOrden" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="completarOrden">
                            ✓ Completar Orden
                        </span>
                        <span wire:loading wire:target="completarOrden">
                            <svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
