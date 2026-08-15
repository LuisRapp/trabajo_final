<div>
    <!-- SECCIÓN 2: Registro de Producción -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
        <div class="bg-blue-600 text-white px-6 py-4">
            <h5 class="text-lg font-semibold mb-0">🚛 Registro de Producción</h5>
        </div>
        <div class="p-6">
            <!-- Errores generales de validación de carga -->
            @if ($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        ⚠️
                        <div>
                            <strong class="text-red-800">Errores en la carga:</strong>
                            <ul class="list-disc list-inside mt-2 text-red-700 text-sm">
                                @error('carga_id_categoria_madera') <li>{{ $message }}</li> @enderror
                                @error('carga_ticket') <li>{{ $message }}</li> @enderror
                                @error('carga_peso_bruto') <li>{{ $message }}</li> @enderror
                                @error('carga_tara') <li>{{ $message }}</li> @enderror
                                @error('carga_peso_neto') <li>{{ $message }}</li> @enderror
                                @error('carga_id_chofer') <li>{{ $message }}</li> @enderror
                                @error('carga_destino') <li>{{ $message }}</li> @enderror
                                @error('carga_empleados') <li>{{ $message }}</li> @enderror
                                @error('carga_maquinarias') <li>{{ $message }}</li> @enderror
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulario agregar carga -->
            <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                <h6 class="font-semibold mb-4 text-slate-900">➕ Registrar Carga</h6>
                
                <!-- Fila 1: Categoría, Maquinaria -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría <span class="text-red-500">*</span></label>
                        <select wire:model="carga_id_categoria_madera" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_id_categoria_madera') ring-2 ring-red-500 @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($this->categoriasMadera as $cat)
                                <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carga_id_categoria_madera') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Maquinarias <span class="text-red-500">*</span></label>
                        <div wire:loading wire:target="id_lote" class="text-center py-2">
                            <svg class="inline-block w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando maquinarias...
                        </div>
                        <div wire:loading.remove wire:target="id_lote">
                            <div class="border border-slate-300 rounded-lg p-3 bg-white @error('carga_maquinarias') ring-2 ring-red-500 @enderror">
                                <input type="text" wire:model.live.debounce.300ms="busqueda_maquinaria"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-2 focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none"
                                    placeholder="Buscar maquinaria...">
                                <div class="max-h-32 overflow-y-auto">
                                    @forelse($this->maquinariasCargaFiltrada as $maq)
                                        <div class="flex items-center mb-2" wire:key="item-{{ $maq->id_maquinaria }}">
                                            <input type="checkbox" value="{{ $maq->id_maquinaria }}" id="maq-{{ $maq->id_maquinaria }}" wire:model="carga_maquinarias" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                            <label for="maq-{{ $maq->id_maquinaria }}" class="ml-2 text-sm text-slate-700">
                                                {{ $maq->modelo }} - <small class="text-slate-500">{{ $maq->tipoMaquinaria->nombre ?? 'Sin tipo' }}</small>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-slate-500 text-sm p-2">
                                            Sin resultados para "{{ $busqueda_maquinaria }}"
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('carga_maquinarias') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Chofer y Cliente con búsqueda -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Chofer <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 @error('carga_id_chofer') ring-2 ring-red-500 @enderror" placeholder="Buscar chofer...">
                        <select wire:model="carga_id_chofer" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                            @forelse($this->choferesFiltrados as $chofer)
                                <option value="{{ $chofer->id_chofer }}" wire:key="option-{{ $chofer->id_chofer }}">{{ $chofer->apellido }}, {{ $chofer->nombre }}</option>
                            @empty
                                <option value="" disabled>No hay resultados</option>
                            @endforelse
                        </select>
                        @error('carga_id_chofer') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Destino (Cliente) <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 @error('carga_destino') ring-2 ring-red-500 @enderror" placeholder="Buscar cliente...">
                        <select wire:model="carga_destino" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                            @forelse($this->clientesFiltrados as $cliente)
                                <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                            @empty
                                <option value="" disabled>No hay resultados</option>
                            @endforelse
                        </select>
                        @error('carga_destino') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Fila 3: Pesajes con cálculo reactivo -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ticket <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="carga_ticket" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_ticket') ring-2 ring-red-500 @enderror" placeholder="TKT-12345">
                        @error('carga_ticket') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Bruto (Ton) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.live="carga_peso_bruto" step="0.1" min="0" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_peso_bruto') ring-2 ring-red-500 @enderror" placeholder="0.00">
                        @error('carga_peso_bruto') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tara (Ton) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.live="carga_tara" step="0.1" min="0" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_tara') ring-2 ring-red-500 @enderror {{ $carga_tara > 0 && $carga_peso_bruto > 0 && $carga_tara > $carga_peso_bruto ? 'ring-2 ring-red-500' : '' }}" placeholder="0.00">
                        @error('carga_tara') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-blue-600 mb-2">Neto (Ton) <span class="text-sm">Calculado</span></label>
                        <input type="text" value="{{ is_numeric($carga_peso_neto) ? number_format((float) $carga_peso_neto, 2, '.', '') : '0.00' }}" class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-100 text-slate-700" readonly>
                        @error('carga_peso_neto') <div class="text-red-600 text-sm mt-1 block">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if($carga_tara > 0 && $carga_peso_bruto > 0 && $carga_tara > $carga_peso_bruto)
                    <div class="md:col-span-4 mt-1">
                        <div class="flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                            <span>⚠️</span>
                            <span>La tara no puede ser mayor al bruto. Verifique los valores.</span>
                        </div>
                    </div>
                @endif

                <!-- Fila 4: Empleados -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Empleados <span class="text-red-500">*</span></label>
                        <div wire:loading wire:target="id_lote" class="text-center py-2">
                            <svg class="inline-block w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando empleados...
                        </div>
                        <div wire:loading.remove wire:target="id_lote">
                            <div class="border border-slate-300 rounded-lg p-3 bg-white @error('carga_empleados') ring-2 ring-red-500 @enderror">
                                <input type="text" wire:model.live.debounce.300ms="busqueda_empleado"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-2 focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none"
                                    placeholder="Buscar empleado...">
                                <div class="max-h-40 overflow-y-auto">
                                    @forelse($this->empleadosCargaFiltrados as $emp)
                                        <div class="flex items-center mb-2" wire:key="item-{{ $emp->id_empleado }}">
                                            <input type="checkbox" value="{{ $emp->id_empleado }}" id="emp-{{ $emp->id_empleado }}" wire:model="carga_empleados" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                            <label for="emp-{{ $emp->id_empleado }}" class="ml-2 text-sm text-slate-700">
                                                {{ $emp->apellido }}, {{ $emp->nombre }} - <small class="text-slate-500">{{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-slate-500 text-sm p-2">
                                            Sin resultados para "{{ $busqueda_empleado }}"
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('carga_empleados') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" wire:click.prevent="agregarCarga" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarCarga">
                        <span wire:loading.remove wire:target="agregarCarga">➕ Agregar Carga</span>
                        <span wire:loading wire:target="agregarCarga"><svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Agregando...</span>
                    </button>
                </div>
            </div>

            <!-- Listado cargas -->
            @if(count($cargas) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-slate-100 border-b border-slate-300">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Ticket</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Categoría</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Neto (Ton)</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Chofer</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Destino</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Empleados</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Maquinarias</th>
                                <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cargas as $index => $carga)
                                <tr class="border-b border-slate-200 hover:bg-slate-50" wire:key="row-{{ $index }}">
                                    <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $carga['ticket'] }}</span></td>
                                    <td class="px-4 py-2">
                                        @php
                                            $cat = $this->categoriasMadera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                        @endphp
                                        {{ $cat->nombre ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 font-semibold">{{ number_format($carga['peso_neto'], 2) }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $chofer = $this->choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                        @endphp
                                        {{ $chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-' }}
                                    </td>
                                    <td class="px-4 py-2">{{ $carga['destino_nombre'] ?? '-' }}</td>
                                    <td class="px-4 py-2"><small>{{ count($carga['empleados'] ?? []) }} emp</small></td>
                                    <td class="px-4 py-2"><small>{{ count($carga['maquinarias'] ?? []) }} maq</small></td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" wire:click.prevent="eliminarCarga({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors" title="Eliminar">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                            <tr>
                                <td colspan="7" class="px-4 py-2 text-right">Total:</td>
                                <td class="px-4 py-2 text-center text-blue-600">{{ number_format(collect($cargas)->sum('peso_neto'), 2) }} Ton</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm">
                    ℹ️ Sin cargas registradas. Agregue al menos una carga.
                </div>
            @endif
        </div>
    </div>
</div>
