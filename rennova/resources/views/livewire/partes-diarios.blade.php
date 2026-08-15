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

            <!-- SECCIÓN 3: Jornales (Si ES día caído) — Child Component -->
            <livewire:jornal-form
                :fecha="$fecha"
                :empleados-filtrados="$this->empleadosFiltrados"
                :es-dia-caido="$es_dia_caido"
                :jornales="$jornales"
                wire:key="jornal-form-{{ $parte_id ?? 'new' }}"
            />

            <!-- SECCIÓN 4: Movimientos de Insumos — Child Component -->
            <livewire:movimiento-form
                :insumos="$this->insumos"
                :movimientos="$movimientos"
                wire:key="movimiento-form-{{ $parte_id ?? 'new' }}"
            />

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

