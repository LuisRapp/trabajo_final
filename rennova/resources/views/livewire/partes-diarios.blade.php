<div
    class="max-w-7xl mx-auto px-4 py-6 relative"
    x-data="{
        openOverrideModal: false,
        modalError: '',
        requiereOverride: @entangle('clima_requiere_override').live,
        esDiaCaido: @entangle('es_dia_caido').live,
        overrideConfirmado: @entangle('clima_override_confirmado').live,
        overrideMotivo: @entangle('clima_override_motivo').live
    }"
>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">📋 Partes Diarios</h1>
    </div>

    <x-flash-messages />

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            ➕ Nuevo Parte Diario
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            📋 Listado de Partes Diarios
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
                        {{ $parte_id ? '✏️ Modificar Parte Diario' : '➕ Nuevo Parte Diario' }}
                    </h5>
                </div>
                <div class="p-6">
                    <!-- Fila 1: Fecha, Lote, Tarea, Día Caído -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   wire:model="fecha" 
                                   max="{{ date('Y-m-d') }}" 
                                   min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('fecha') ring-2 ring-red-500 @enderror">
                            @error('fecha') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('id_lote') ring-2 ring-red-500 @enderror">
                                <option value="">Seleccione un lote...</option>
                                @foreach($this->lotes as $lote)
                                    <option value="{{ $lote->id_lote }}" wire:key="option-{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="id_lote" class="text-slate-600 text-sm mt-1">
                                <svg class="inline-block w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tarea del lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote_tarea" wire:key="lote-tareas-{{ $id_lote }}" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('id_lote_tarea') ring-2 ring-red-500 @enderror">
                                <option value="">Seleccione una tarea...</option>
                                @foreach($this->loteTareas as $tarea)
                                    <option value="{{ $tarea->id_lote_tarea }}" wire:key="option-{{ $tarea->id_lote_tarea }}">
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
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-slate-800">No hay tareas para este lote</div>
                                        <button type="button" wire:click="$set('mostrarModalTareaRapida', true)"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-700 text-white rounded-lg text-sm font-semibold hover:bg-green-800 transition-colors">
                                            ➕ Crear tarea
                                        </button>
                                    </div>
                                </div>
                            @endif
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
                <livewire:carga-form
                    :id_lote="$id_lote"
                    :fecha="$fecha"
                    :empleados_asignados_ids="$empleados_asignados_ids"
                    :maquinarias_asignadas_ids="$maquinarias_asignadas_ids"
                    :cargas="$cargas"
                />
            @endif

            <!-- SECCIÓN 3: Jornales (Si ES día caído) -->
            @if($es_dia_caido)
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-yellow-500 text-slate-900 px-6 py-4">
                        <h5 class="text-lg font-semibold mb-0">💰 Asignación de Jornales</h5>
                    </div>
                    <div class="p-6">
                        <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                            <h6 class="font-semibold mb-4 text-slate-900">👤 Agregar Empleado al Jornal</h6>
                            <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end">
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Empleado <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="busqueda_empleado"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-2 focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none"
                                        placeholder="Buscar empleado...">
                                    <select wire:model="jornal_id_empleado" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('jornal_id_empleado') ring-2 ring-red-500 @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($this->empleadosFiltrados as $emp)
                                            <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">
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
                                        <span wire:loading.remove wire:target="agregarJornal">➕ Agregar</span>
                                        <span wire:loading wire:target="agregarJornal"><svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
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
                                            <tr class="border-b border-slate-200 hover:bg-slate-50" wire:key="row-{{ $index }}">
                                                <td class="px-4 py-2">{{ $jornal['nombre_completo'] ?? '-' }}</td>
                                                <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $jornal['rol'] ?? '-' }}</span></td>
                                                <td class="px-4 py-2 text-green-600 font-bold">${{ number_format($jornal['jornal_diario'] ?? 0, 2) }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" wire:click.prevent="eliminarJornal({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                        🗑️
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
                                ℹ️ Sin empleados asignados. Agregue al menos un empleado.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECCIÓN 4: Movimientos de Insumos -->
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
                                    @foreach($this->insumos as $insumo)
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

            <!-- MODAL: Crear Tarea Rápida -->
            <flux:modal wire:model.live="mostrarModalTareaRapida" class="md:w-96">
                <div class="space-y-4">
                    <flux:heading size="lg">Crear Tarea Rápida</flux:heading>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model.live="nueva_tarea_tipo_tarea" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none">
                            <option value="">Seleccione...</option>
                            @foreach($this->taskTypes as $taskType)
                                <option value="{{ $taskType->value }}" wire:key="option-{{ $taskType->value }}">{{ $taskType->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Superficie (ha)</label>
                        <input type="number" wire:model.live="nueva_tarea_superficie_afectada_ha" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            placeholder="(opcional)">
                    </div>

                    <div class="flex gap-3 justify-end pt-2">
                        <flux:button variant="outline" wire:click="$set('mostrarModalTareaRapida', false)">Cancelar</flux:button>
                        <flux:button variant="primary" wire:click="crearTareaRapida" :disabled="!$nueva_tarea_tipo_tarea">
                            Crear
                        </flux:button>
                    </div>
                </div>
            </flux:modal>

            <!-- BOTÓN GUARDAR -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click.prevent="cancelarEdicion" class="px-8 py-3 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            ✕ Cancelar
                        </button>
                        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
                        <button
    type="button"
    @click.prevent="
        if (!esDiaCaido && requiereOverride && !overrideConfirmado) {
            modalError = '';
            openOverrideModal = true;
        } else {
            $wire.guardar();
        }
    "
    class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    wire:loading.attr="disabled"
    wire:target="guardar"
>
    <span wire:loading.remove wire:target="guardar">✓ Guardar Parte Diario</span>
    <span wire:loading wire:target="guardar"><svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Guardando...</span>
</button>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>
        @endcanany
    @endif

    @include('livewire.partials.clima-override-modal')

    @if($tab_activo === 'listado')
        @include('livewire.partials.partes-listado')
    @endif
</div>

