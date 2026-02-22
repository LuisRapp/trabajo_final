<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><i class="bi bi-clipboard-check mr-3"></i>Partes Diarios</h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
            <i class="bi bi-check-circle-fill text-green-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-green-800">{{ session('message') }}</p>
            </div>
            <button type="button" @click="open = false" class="text-green-600 hover:text-green-800">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-red-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
            <button type="button" @click="open = false" class="text-red-600 hover:text-red-800">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-plus-circle"></i> Nuevo Parte Diario
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-list-ul"></i> Listado de Partes Diarios
        </button>
    </div>

    <!-- Pestaña 1: Nuevo Parte Diario -->
    @if($tab_activo === 'nuevo')
        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
        <div id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab" class="tab-pane-content">
            
            <!-- SECCIÓN 1: Datos Maestros -->
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
                    <h5 class="text-lg font-semibold text-slate-900 mb-0">
                        <i class="bi bi-{{ $parte_id ? 'pencil-square' : 'plus-circle' }} mr-2"></i> 
                        {{ $parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario' }}
                    </h5>
                </div>
                <div class="p-6">
                    <!-- Fila 1: Lote, Fecha, Día Caído -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('id_lote') ring-2 ring-red-500 @enderror">
                                <option value="">Seleccione un lote...</option>
                                @foreach($this->lotes as $lote)
                                    <option value="{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="id_lote" class="text-slate-600 text-sm mt-1">
                                <i class="bi bi-arrow-repeat animate-spin"></i> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tarea del lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote_tarea" wire:key="lote-tareas-{{ $id_lote }}" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('id_lote_tarea') ring-2 ring-red-500 @enderror">
                                <option value="">Seleccione una tarea...</option>
                                @foreach($this->loteTareas as $tarea)
                                    <option value="{{ $tarea->id_lote_tarea }}">
                                        #{{ $tarea->id_lote_tarea }} - {{ $tarea->tipo_tarea_label }} ({{ $tarea->estado }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_lote_tarea') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            @if(!$id_lote)
                                <div class="text-slate-500 text-sm mt-2">Seleccioná un lote para cargar las tareas.</div>
                            @elseif($this->loteTareas->isEmpty())
                                <div class="text-slate-500 text-sm mt-2">Este lote no tiene tareas cargadas.</div>
                            @endif

                            @if($id_lote && $this->loteTareas->isEmpty())
                                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3" wire:key="tarea-rapida-{{ $id_lote }}">
                                    <div class="text-sm font-semibold text-slate-800 mb-2">No hay tareas para este lote</div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-end">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
                                            <select wire:model.live="nueva_tarea_tipo_tarea" class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none">
                                                <option value="">Seleccione...</option>
                                                @foreach($this->taskTypes as $taskType)
                                                    <option value="{{ $taskType->value }}">{{ $taskType->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sup. (ha)</label>
                                            <input type="number" wire:model.live="nueva_tarea_superficie_afectada_ha" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none" placeholder="(opc.)">
                                        </div>
                                        <div>
                                            <button type="button" class="w-full px-3 py-2 bg-green-700 text-white rounded text-sm font-semibold hover:bg-green-800 transition-colors disabled:opacity-50" wire:click.prevent="crearTareaRapida" @disabled(!$nueva_tarea_tipo_tarea)
                                                wire:loading.attr="disabled" wire:target="crearTareaRapida">
                                                <span wire:loading.remove wire:target="crearTareaRapida"><i class="bi bi-plus-circle mr-1"></i>Crear</span>
                                                <span wire:loading wire:target="crearTareaRapida"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   wire:model="fecha" 
                                   max="{{ date('Y-m-d') }}" 
                                   min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                   class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('fecha') ring-2 ring-red-500 @enderror">
                            @error('fecha') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Día Caído</label>
                            <div class="flex items-center mt-2">
                                <input type="checkbox" id="diaCaidoSwitch" wire:model.live="es_dia_caido" class="w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-600">
                                <label for="diaCaidoSwitch" class="ml-2">
                                    <span class="inline-block px-3 py-1 rounded text-sm font-medium {{ $es_dia_caido ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-200 text-slate-800' }}">
                                        {{ $es_dia_caido ? 'SÍ - Jornal' : 'NO - Destajo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    @if($id_lote && $fecha)
                        @php
                            $estado = strtoupper($clima_estado ?? 'OPERATIVO');
                            $estadoLabel = $estado === 'INACTIVO'
                                ? 'No operativo'
                                : ($estado === 'OPERATIVO_CONDICIONAL' ? 'Operativo condicional' : 'Operativo');
                            $estadoClass = $estado === 'INACTIVO'
                                ? 'bg-rose-100 text-rose-800'
                                : ($estado === 'OPERATIVO_CONDICIONAL' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                        @endphp
                        <div class="mb-6">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold {{ $estadoClass }}">
                                        Estado pronostico: {{ $estadoLabel }}
                                    </span>
                                    @if($clima_es_fin_de_semana)
                                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-slate-200 text-slate-700">
                                            Fin de semana
                                        </span>
                                    @endif
                                    @if($clima_fuente === 'fallback')
                                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Fallback: sin datos de API
                                        </span>
                                    @endif
                                </div>
                                @if($clima_razon)
                                    <p class="text-sm text-slate-600 mt-2">Motivo: {{ $clima_razon }}</p>
                                @endif
                            </div>
                        </div>

                        @if(!$es_dia_caido && $clima_requiere_override)
                            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <div class="flex items-start gap-3">
                                    <i class="bi bi-exclamation-triangle-fill text-amber-600 mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="font-semibold text-amber-900">Dia no operativo</p>
                                        <p class="text-sm text-amber-800 mt-1">
                                            Para registrar produccion en este dia necesitas confirmar la operacion y dejar un motivo.
                                        </p>
                                        <div class="mt-3 flex items-center gap-2">
                                            <input type="checkbox" id="climaOverrideSwitch" wire:model.live="clima_override_confirmado" class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-600">
                                            <label for="climaOverrideSwitch" class="text-sm text-amber-900">Confirmo operar en dia no operativo</label>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-semibold text-amber-900 mb-1">Motivo de confirmacion</label>
                                            <textarea wire:model="clima_override_motivo" rows="2" class="w-full px-3 py-2 border border-amber-200 rounded text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-300 focus:outline-none" placeholder="Ej: compromiso logístico, entrega urgente"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Fila 2: Observaciones -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Observaciones</label>
                            <textarea wire:model="observaciones" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" rows="2" placeholder="Observaciones adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Registro de Producción (Si NO es día caído) -->
            @if(!$es_dia_caido)
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-blue-600 text-white px-6 py-4">
                        <h5 class="text-lg font-semibold mb-0"><i class="bi bi-truck mr-2"></i>Registro de Producción</h5>
                    </div>
                    <div class="p-6">
                        <!-- Errores generales de validación de carga -->
                        @if ($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias'))
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i class="bi bi-exclamation-triangle text-red-600 mt-0.5"></i>
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
                        <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50" x-data="{
                            bruto: @entangle('carga_peso_bruto').live,
                            tara: @entangle('carga_tara').live,
                            get neto() {
                                return (parseFloat(this.bruto) || 0) - (parseFloat(this.tara) || 0);
                            }
                        }" x-init="$watch('neto', value => $wire.set('carga_peso_neto', value))">
                            <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-plus-circle-fill mr-2"></i>Registrar Carga</h6>
                            
                            <!-- Fila 1: Categoría, Maquinaria -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría <span class="text-red-500">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_id_categoria_madera') ring-2 ring-red-500 @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($this->categoriasMadera as $cat)
                                            <option value="{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('carga_id_categoria_madera') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Maquinarias <span class="text-red-500">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Cargando maquinarias...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border border-default rounded-lg p-3 max-h-32 overflow-y-auto bg-white @error('carga_maquinarias') ring-2 ring-red-500 @enderror">
                                            @forelse($this->maquinariasFiltrada as $maq)
                                                <div class="flex items-center mb-2">
                                                    <input type="checkbox" value="{{ $maq->id_maquinaria }}" id="maq-{{ $maq->id_maquinaria }}" wire:model="carga_maquinarias" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                                    <label for="maq-{{ $maq->id_maquinaria }}" class="ml-2 text-sm text-slate-700">
                                                        {{ $maq->modelo }} - <small class="text-slate-500">{{ $maq->tipoMaquinaria->nombre ?? 'Sin tipo' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="text-slate-500 text-sm p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver maquinarias disponibles
                                                </div>
                                            @endforelse
                                        </div>
                                        @error('carga_maquinarias') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente con búsqueda -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chofer <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 @error('carga_id_chofer') ring-2 ring-red-500 @enderror" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                                        @forelse($this->choferesFiltrados as $chofer)
                                            <option value="{{ $chofer->id_chofer }}">{{ $chofer->apellido }}, {{ $chofer->nombre }}</option>
                                        @empty
                                            <option value="" disabled>No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_id_chofer') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Destino (Cliente) <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 @error('carga_destino') ring-2 ring-red-500 @enderror" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                                        @forelse($this->clientesFiltrados as $cliente)
                                            <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                        @empty
                                            <option value="" disabled>No hay resultados</option>
                                        @endforelse
                                    </select>
                                    @error('carga_destino') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Fila 3: Pesajes con cálculo reactivo Alpine -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ticket <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="carga_ticket" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_ticket') ring-2 ring-red-500 @enderror" placeholder="TKT-12345">
                                    @error('carga_ticket') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Bruto (Ton) <span class="text-red-500">*</span></label>
                                    <input type="number" x-model.number="bruto" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_peso_bruto') ring-2 ring-red-500 @enderror" placeholder="0.00">
                                    @error('carga_peso_bruto') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tara (Ton) <span class="text-red-500">*</span></label>
                                    <input type="number" x-model.number="tara" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('carga_tara') ring-2 ring-red-500 @enderror" placeholder="0.00">
                                    @error('carga_tara') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-blue-600 mb-2">Neto (Ton) <span class="text-sm">Calculado</span></label>
                                    <input type="text" x-text="neto.toFixed(2)" class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-100 text-slate-700" readonly>
                                    @error('carga_peso_neto') <div class="text-red-600 text-sm mt-1 block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Fila 4: Empleados -->
                            <div class="grid grid-cols-1 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Empleados <span class="text-red-500">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Cargando empleados...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border border-default rounded-lg p-3 max-h-40 overflow-y-auto bg-white @error('carga_empleados') ring-2 ring-red-500 @enderror">
                                            @forelse($this->empleadosFiltrados as $emp)
                                                <div class="flex items-center mb-2">
                                                    <input type="checkbox" value="{{ $emp->id_empleado }}" id="emp-{{ $emp->id_empleado }}" wire:model="carga_empleados" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                                    <label for="emp-{{ $emp->id_empleado }}" class="ml-2 text-sm text-slate-700">
                                                        {{ $emp->apellido }}, {{ $emp->nombre }} - <small class="text-slate-500">{{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="text-slate-500 text-sm p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver empleados disponibles
                                                </div>
                                            @endforelse
                                        </div>
                                        @error('carga_empleados') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" wire:click.prevent="agregarCarga" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarCarga">
                                    <span wire:loading.remove wire:target="agregarCarga"><i class="bi bi-plus-circle mr-2"></i>Agregar Carga</span>
                                    <span wire:loading wire:target="agregarCarga"><i class="bi bi-arrow-repeat animate-spin mr-2"></i>Agregando...</span>
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
                                            <tr class="border-b border-slate-200 hover:bg-slate-50">
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
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                                        <tr>
                                            <td colspan="7" class="px-4 py-2 text-right">Total:</td>
                                            <td class="px-4 py-2 text-center text-blue-600">{{ number_format($total_toneladas, 2) }} Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm">
                                <i class="bi bi-info-circle mr-2"></i> Sin cargas registradas. Agregue al menos una carga.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECCIÓN 3: Jornales (Si ES día caído) -->
            @if($es_dia_caido)
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-yellow-500 text-slate-900 px-6 py-4">
                        <h5 class="text-lg font-semibold mb-0"><i class="bi bi-cash-coin mr-2"></i>Asignación de Jornales</h5>
                    </div>
                    <div class="p-6">
                        <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                            <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-person-plus-fill mr-2"></i>Agregar Empleado al Jornal</h6>
                            <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end">
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Empleado <span class="text-red-500">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('jornal_id_empleado') ring-2 ring-red-500 @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($this->empleadosFiltrados as $emp)
                                            <option value="{{ $emp->id_empleado }}">
                                                {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                                @if(isset($jornal_por_empleado[$emp->id_empleado]))
                                                    (Jornal: ${{ number_format($jornal_por_empleado[$emp->id_empleado], 2) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jornal_id_empleado') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <button type="button" wire:click.prevent="agregarJornal" class="w-full px-6 py-3 bg-yellow-500 text-slate-900 rounded-lg font-semibold hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarJornal">
                                        <span wire:loading.remove wire:target="agregarJornal"><i class="bi bi-plus-circle mr-2"></i>Agregar</span>
                                        <span wire:loading wire:target="agregarJornal"><i class="bi bi-arrow-repeat animate-spin mr-2"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(count($jornales) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-sm">
                                    <thead class="bg-slate-100 border-b border-slate-300">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Empleado</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Rol</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Jornal</th>
                                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jornales as $index => $jornal)
                                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                                <td class="px-4 py-2">{{ $jornal['nombre_completo'] ?? '-' }}</td>
                                                <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $jornal['rol'] ?? '-' }}</span></td>
                                                <td class="px-4 py-2 text-green-600 font-bold">${{ number_format($jornal['jornal_diario'] ?? 0, 2) }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" wire:click.prevent="eliminarJornal({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-right">Total Jornales:</td>
                                            <td class="px-4 py-2 text-center text-yellow-600">${{ number_format(array_sum(array_column($jornales, 'jornal_diario')), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                                <i class="bi bi-info-circle mr-2"></i> Sin empleados asignados. Agregue al menos un empleado.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECCIÓN 4: Movimientos de Insumos -->
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold mb-0"><i class="bi bi-box-seam mr-2"></i>Movimientos de Insumos</h5>
                </div>
                <div class="p-6">
                    <div id="alertaMovimiento"></div>
                    
                    <!-- Errores generales de validación de movimiento -->
                    @if ($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <i class="bi bi-exclamation-triangle text-red-600 mt-0.5"></i>
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
                        <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-box-arrow-right mr-2"></i>Registrar Consumo</h6>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Insumo <span class="text-red-500">*</span></label>
                                <select wire:model.live="movimiento_id_insumo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_id_insumo') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($this->insumos as $insumo)
                                        <option value="{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</option>
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
                                       class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_cantidad') ring-2 ring-red-500 @enderror" placeholder="0.00">
                                @error('movimiento_cantidad') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Motivo <span class="text-red-500">*</span></label>
                                <select wire:model="movimiento_motivo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('movimiento_motivo') ring-2 ring-red-500 @enderror">
                                    <option value="Producción">Producción</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                    <option value="Varios">Varios</option>
                                </select>
                                @error('movimiento_motivo') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click.prevent="agregarMovimiento" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                                    <span wire:loading.remove wire:target="agregarMovimiento"><i class="bi bi-plus-circle mr-2"></i>Agregar</span>
                                    <span wire:loading wire:target="agregarMovimiento"><i class="bi bi-arrow-repeat animate-spin mr-2"></i></span>
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
                                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                                            <td class="px-4 py-2"><strong>{{ $mov['nombre_insumo'] }}</strong></td>
                                            <td class="px-4 py-2">{{ number_format($mov['cantidad'], 2) }} {{ $mov['unidad'] ?? '' }}</td>
                                            <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $mov['motivo'] }}</span></td>
                                            <td class="px-4 py-2"><small>{{ $mov['observaciones'] ?? '-' }}</small></td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" wire:click.prevent="eliminarMovimiento({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 text-sm">
                            <i class="bi bi-info-circle mr-2"></i> Sin movimientos registrados. Esta sección es opcional.
                        </div>
                    @endif
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click.prevent="cancelarEdicion" class="px-8 py-3 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle mr-2"></i>Cancelar
                        </button>
                        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
                        <button type="button" wire:click.prevent="guardar" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar"><i class="bi bi-check-circle mr-2"></i>Guardar Parte Diario</span>
                            <span wire:loading wire:target="guardar"><i class="bi bi-arrow-repeat animate-spin mr-2"></i>Guardando...</span>
                        </button>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>
        @endcanany
    @endif

    <!-- Pestaña 2: Listado -->
    @if($tab_activo === 'listado')
        <div id="listado-partes" role="tabpanel" aria-labelledby="listado-tab" class="tab-pane-content">
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
                <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
                    <h5 class="text-lg font-semibold text-slate-900 mb-0">Partes Diarios Registrados</h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Propietario</label>
                            <input type="text" wire:model.live.debounce.400ms="busqueda" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" placeholder="Ej: Juan Pérez...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Fecha</label>
                            <input type="date" wire:model.live="busqueda_fecha" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none">
                        </div>
                    </div>
                    
                    @if($partes && count($partes) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-slate-100 border-b border-slate-300">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">ID</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Lote</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Fecha</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Tipo</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Observaciones</th>
                                        <th class="px-4 py-2 text-center font-semibold text-slate-900">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($partes as $parte)
                                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                                            <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">#{{ $parte->id_parte_diario }}</span></td>
                                            <td class="px-4 py-2">{{ $parte->lote?->propietario ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-' }}</td>
                                            <td class="px-4 py-2">
                                                @if($parte->es_dia_caido)
                                                    <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded"><i class="bi bi-calendar-x mr-1"></i>Día Caído</span>
                                                @else
                                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded"><i class="bi bi-truck mr-1"></i>Producción</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2"><small>{{ $parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-' }}</small></td>
                                            <td class="px-4 py-2 text-center">
                                                <div class="inline-flex gap-2">
                                                    @can('editar-partes-diarios')
                                                    <button type="button" class="px-3 py-1 border border-blue-500 text-blue-600 rounded text-sm hover:bg-blue-50 transition-colors" wire:click.prevent="editar({{ $parte->id_parte_diario }})" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    @endcan
                                                    @can('eliminar-partes-diarios')
                                                    <button type="button" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors" wire:click.prevent="eliminar({{ $parte->id_parte_diario }})" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @isset($partes)
                            <div class="mt-6">
                                {{ $partes->links('pagination::tailwind') }}
                            </div>
                        @endisset
                    @else
                        <div class="text-center py-12">
                            <i class="bi bi-inbox text-6xl text-slate-300 block mb-4"></i>
                            <p class="text-slate-600">No hay partes diarios registrados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
