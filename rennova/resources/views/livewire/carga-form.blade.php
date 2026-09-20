<div>
    <!-- SECCIÓN 2: Registro de Producción -->
    <x-ui.card class="mb-5 overflow-hidden">
        <div class="bg-pino text-blanco px-6 py-4">
            <h5 class="text-lg font-semibold mb-0 flex items-center gap-2">
                <flux:icon.truck class="size-5" />
                Registro de Producción
            </h5>
        </div>
        <div class="p-6">
            <!-- Errores generales de validación de carga -->
            @if ($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias'))
                <x-ui.alert variant="danger" class="mb-4" dismissible="false">
                    <strong>Errores en la carga:</strong>
                    <ul class="list-disc list-inside mt-2 text-sm">
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
                </x-ui.alert>
            @endif

            <!-- Formulario agregar carga -->
            <div class="border border-arena rounded-sm p-4 mb-4 bg-corteza-suave">
                <h6 class="font-semibold mb-4 text-tinta flex items-center gap-2">
                    <flux:icon.plus class="size-4" />
                    Registrar Carga
                </h6>

                <!-- Fila 1: Categoría, Maquinaria -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Categoría <span class="text-tierra">*</span></label>
                        <select wire:model="carga_id_categoria_madera" class="form-input @error('carga_id_categoria_madera') ring-2 ring-tierra @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($this->categoriasMadera as $cat)
                                <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carga_id_categoria_madera') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-tinta mb-2">Maquinarias <span class="text-tierra">*</span></label>
                        <div wire:loading wire:target="id_lote" class="text-center py-2 text-tinta-suave">
                            <flux:icon.arrow-path class="inline-block size-4 animate-spin" />
                            Cargando maquinarias...
                        </div>
                        <div wire:loading.remove wire:target="id_lote">
                            <div class="border border-arena rounded-sm p-3 bg-blanco @error('carga_maquinarias') ring-2 ring-tierra @enderror">
                                <input type="text" wire:model.live.debounce.300ms="busqueda_maquinaria"
                                    class="form-input mb-2"
                                    placeholder="Buscar maquinaria...">
                                <div class="max-h-32 overflow-y-auto">
                                    @forelse($this->maquinariasCargaFiltrada as $maq)
                                        <div class="flex items-center mb-2" wire:key="item-{{ $maq->id_maquinaria }}">
                                            <input type="checkbox" value="{{ $maq->id_maquinaria }}" id="maq-{{ $maq->id_maquinaria }}" wire:model="carga_maquinarias" class="h-4 w-4 rounded border-arena text-pino focus:ring-pino">
                                            <label for="maq-{{ $maq->id_maquinaria }}" class="ml-2 text-sm text-tinta">
                                                {{ $maq->modelo }} - <small class="text-tinta-suave">{{ $maq->tipoMaquinaria->nombre ?? 'Sin tipo' }}</small>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-tinta-suave text-sm p-2">
                                            Sin resultados para "{{ $busqueda_maquinaria }}"
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('carga_maquinarias') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Chofer y Cliente con búsqueda -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Chofer <span class="text-tierra">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="form-input mb-2 @error('carga_id_chofer') ring-2 ring-tierra @enderror" placeholder="Buscar chofer...">
                        <select wire:model="carga_id_chofer" class="form-input" size="3">
                            @forelse($this->choferesFiltrados as $chofer)
                                <option value="{{ $chofer->id_chofer }}" wire:key="option-{{ $chofer->id_chofer }}">{{ $chofer->apellido }}, {{ $chofer->nombre }}</option>
                            @empty
                                <option value="" disabled>No hay resultados</option>
                            @endforelse
                        </select>
                        @error('carga_id_chofer') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Destino (Cliente) <span class="text-tierra">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="form-input mb-2 @error('carga_destino') ring-2 ring-tierra @enderror" placeholder="Buscar cliente...">
                        <select wire:model="carga_destino" class="form-input" size="3">
                            @forelse($this->clientesFiltrados as $cliente)
                                <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                            @empty
                                <option value="" disabled>No hay resultados</option>
                            @endforelse
                        </select>
                        @error('carga_destino') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Fila 3: Pesajes con cálculo reactivo -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Ticket <span class="text-tierra">*</span></label>
                        <input type="text" wire:model="carga_ticket" class="form-input @error('carga_ticket') ring-2 ring-tierra @enderror" placeholder="TKT-12345">
                        @error('carga_ticket') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Bruto (Ton) <span class="text-tierra">*</span></label>
                        <input type="number" wire:model.live="carga_peso_bruto" step="0.1" min="0" class="form-input @error('carga_peso_bruto') ring-2 ring-tierra @enderror" placeholder="0.00">
                        @error('carga_peso_bruto') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Tara (Ton) <span class="text-tierra">*</span></label>
                        <input type="number" wire:model.live="carga_tara" step="0.1" min="0" class="form-input @error('carga_tara') ring-2 ring-tierra @enderror {{ $carga_tara > 0 && $carga_peso_bruto > 0 && $carga_tara > $carga_peso_bruto ? 'ring-2 ring-tierra' : '' }}" placeholder="0.00">
                        @error('carga_tara') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-pino mb-2">Neto (Ton) <span class="text-sm">Calculado</span></label>
                        <input type="text" value="{{ is_numeric($carga_peso_neto) ? number_format((float) $carga_peso_neto, 2, '.', '') : '0.00' }}" class="form-input bg-corteza-suave text-tinta-suave" readonly>
                        @error('carga_peso_neto') <div class="text-tierra text-sm mt-1 block">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if($carga_tara > 0 && $carga_peso_bruto > 0 && $carga_tara > $carga_peso_bruto)
                    <div class="md:col-span-4 mt-1">
                        <x-ui.alert variant="warning" dismissible="false">
                            La tara no puede ser mayor al bruto. Verifique los valores.
                        </x-ui.alert>
                    </div>
                @endif

                <!-- Fila 4: Empleados -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Empleados <span class="text-tierra">*</span></label>
                        <div wire:loading wire:target="id_lote" class="text-center py-2 text-tinta-suave">
                            <flux:icon.arrow-path class="inline-block size-4 animate-spin" />
                            Cargando empleados...
                        </div>
                        <div wire:loading.remove wire:target="id_lote">
                            <div class="border border-arena rounded-sm p-3 bg-blanco @error('carga_empleados') ring-2 ring-tierra @enderror">
                                <input type="text" wire:model.live.debounce.300ms="busqueda_empleado"
                                    class="form-input mb-2"
                                    placeholder="Buscar empleado...">
                                <div class="max-h-40 overflow-y-auto">
                                    @forelse($this->empleadosCargaFiltrados as $emp)
                                        <div class="flex items-center mb-2" wire:key="item-{{ $emp->id_empleado }}">
                                            <input type="checkbox" value="{{ $emp->id_empleado }}" id="emp-{{ $emp->id_empleado }}" wire:model="carga_empleados" class="h-4 w-4 rounded border-arena text-pino focus:ring-pino">
                                            <label for="emp-{{ $emp->id_empleado }}" class="ml-2 text-sm text-tinta">
                                                {{ $emp->apellido }}, {{ $emp->nombre }} - <small class="text-tinta-suave">{{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-tinta-suave text-sm p-2">
                                            Sin resultados para "{{ $busqueda_empleado }}"
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('carga_empleados') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-ui.button variant="primary" wire:click.prevent="agregarCarga" wire:loading.attr="disabled" wire:target="agregarCarga">
                        <span wire:loading.remove wire:target="agregarCarga"><flux:icon.plus class="size-4" /> Agregar Carga</span>
                        <span wire:loading wire:target="agregarCarga"><flux:icon.arrow-path class="inline-block size-4 animate-spin mr-2" />Agregando...</span>
                    </x-ui.button>
                </div>
            </div>

            <!-- Listado cargas -->
            @if(count($cargas) > 0)
                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Categoría</th>
                                <th>Neto (Ton)</th>
                                <th>Chofer</th>
                                <th>Destino</th>
                                <th>Empleados</th>
                                <th>Maquinarias</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cargas as $index => $carga)
                                <tr wire:key="row-{{ $index }}">
                                    <td><span class="badge badge-neutral">{{ $carga['ticket'] }}</span></td>
                                    <td>
                                        @php
                                            $cat = $this->categoriasMadera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                        @endphp
                                        {{ $cat->nombre ?? '-' }}
                                    </td>
                                    <td class="font-semibold">{{ number_format($carga['peso_neto'], 2) }}</td>
                                    <td>
                                        @php
                                            $chofer = $this->choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                        @endphp
                                        {{ $chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-' }}
                                    </td>
                                    <td>{{ $carga['destino_nombre'] ?? '-' }}</td>
                                    <td><small>{{ count($carga['empleados'] ?? []) }} emp</small></td>
                                    <td><small>{{ count($carga['maquinarias'] ?? []) }} maq</small></td>
                                    <td class="text-center">
                                        <x-ui.button variant="danger" size="sm" icon="trash" wire:click.prevent="eliminarCarga({{ $index }})" title="Eliminar" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right">Total:</td>
                                <td class="text-center text-pino font-semibold">{{ number_format(collect($cargas)->sum('peso_neto'), 2) }} Ton</td>
                            </tr>
                        </tfoot>
                    </table>
                </x-ui.table-container>
            @else
                <x-ui.alert variant="info" dismissible="false">
                    Sin cargas registradas. Agregue al menos una carga.
                </x-ui.alert>
            @endif
        </div>
    </x-ui.card>
</div>
