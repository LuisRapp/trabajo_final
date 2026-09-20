<div>
    @once
        <style>
            .lw-modal-overlay {
                position: fixed;
                inset: 0;
                background-color: rgba(28, 25, 23, 0.6);
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
                border-radius: 0.125rem;
                box-shadow: 0 20px 40px rgba(28, 25, 23, 0.25);
                overflow: hidden;
                border: 1px solid var(--color-arena);
            }
            .lw-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.5rem;
                background: var(--color-pino);
                color: #fff;
            }
            .lw-modal-body { padding: 1.5rem; }
            .lw-modal-footer {
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: .75rem;
                background: var(--color-corteza-suave);
                border-top: 1px solid var(--color-arena);
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
                <div class="lw-modal-header">
                    <h5 class="mb-0 flex items-center gap-2">
                        <flux:icon.check class="size-5" />
                        Completar Orden de Mantenimiento
                    </h5>
                    <button type="button" class="lw-close" wire:click="cerrarModal" aria-label="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
                <div class="lw-modal-body">
                    @if (session()->has('error'))
                        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">
                            {{ session('error') }}
                        </x-ui.alert>
                    @endif

                    @error('general')
                        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">
                            {{ $message }}
                        </x-ui.alert>
                    @enderror

                    <x-ui.alert variant="info" class="mb-6 text-sm" :dismissible="false">
                        <strong>Orden #{{ $orden_completar_info['id'] ?? 'N/A' }}</strong><br>
                        <strong>Maquinaria:</strong> {{ $orden_completar_info['maquinaria'] ?? 'N/A' }}<br>
                        <strong>Tipo:</strong> {{ $orden_completar_info['tipo'] ?? 'N/A' }}<br>
                        <strong>Fecha Inicio:</strong> {{ isset($orden_completar_info['fecha_inicio']) ? \Carbon\Carbon::parse($orden_completar_info['fecha_inicio'])->format('d/m/Y') : 'N/A' }}
                    </x-ui.alert>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Fecha Finalizacion <span class="text-tierra">*</span></label>
                            <input type="date" wire:model="fecha_fin_completar" class="form-input @error('fecha_fin_completar') ring-2 ring-tierra @enderror">
                            @error('fecha_fin_completar') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Costo Total (opcional)</label>
                            <div class="flex items-center gap-2">
                                <span class="px-4 py-3 bg-corteza-suave text-tinta-suave rounded-sm border border-arena">$</span>
                                <input type="number" wire:model="costo_total_completar" step="1" min="0" class="flex-1 form-input @error('costo_total_completar') ring-2 ring-tierra @enderror" placeholder="0.00">
                            </div>
                            @error('costo_total_completar') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">Costo adicional (ej: mano de obra). El costo de los insumos se calcula automaticamente al momento del cierre.</small>
                        </div>
                    </div>

                    <hr class="my-6 border-arena">
                    <h6 class="mb-4 font-semibold text-tinta flex items-center gap-2">
                        <flux:icon.archive-box class="size-4" />
                        Insumos Utilizados
                        @if(!$orden_es_correctivo)
                            <x-ui.badge variant="info">Kit Preventivo</x-ui.badge>
                        @endif
                    </h6>

                    @foreach($insumos_usados as $index => $insumo)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4 p-4 bg-hueso rounded-sm border border-arena" wire:key="insumo-{{ $index }}">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-tinta mb-2">Insumo</label>
                                <select wire:model.live="insumos_usados.{{ $index }}.id_insumo" class="form-input">
                                    <option value="">Seleccione...</option>
                                    @foreach($insumosDisponibles as $ins)
                                        <option value="{{ $ins->id_insumo }}" wire:key="option-{{ $ins->id_insumo }}">
                                            {{ $ins->nombre }} (Stock: {{ number_format($ins->stock_disponible, 2) }} - ${{ number_format($ins->precio_promedio, 2) }}/u)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-2">Cantidad a usar</label>
                                <input type="number" wire:model="insumos_usados.{{ $index }}.cantidad" step="0.1" min="0" class="form-input" placeholder="0">
                                @if(!empty($insumo['id_insumo']))
                                    @php
                                        $stockDisponible = optional($insumosDisponibles->firstWhere('id_insumo', $insumo['id_insumo']))->stock_disponible ?? 0;
                                    @endphp
                                    <small class="text-tinta-suave text-xs mt-1 block">Disponible: {{ number_format($stockDisponible, 2) }}</small>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-2">Precio Unitario</label>
                                <div class="flex items-center gap-1">
                                    <span class="text-tinta-suave">$</span>
                                    <input type="number" wire:model="insumos_usados.{{ $index }}.precio_unitario" step="0.01" min="0" class="flex-1 form-input bg-hueso" readonly>
                                </div>
                            </div>
                            <div class="flex items-end justify-end">
                                @if($index > 0 || count($insumos_usados) > 1)
                                    <x-ui.button type="button" size="sm" variant="danger" icon="trash" wire:click="eliminarInsumo({{ $index }})" title="Eliminar" />
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <x-ui.button type="button" variant="secondary" icon="plus" wire:click="agregarInsumo" class="mt-4">
                        Agregar Insumo
                    </x-ui.button>
                </div>
                <div class="lw-modal-footer">
                    <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="cerrarModal">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="button" icon="check" wire:click="completarOrden" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="completarOrden">
                            Completar Orden
                        </span>
                        <span wire:loading wire:target="completarOrden">
                            <flux:icon.arrow-path class="size-4 inline animate-spin mr-1" />
                            Procesando...
                        </span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
