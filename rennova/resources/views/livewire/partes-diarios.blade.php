<div
    class="w-full relative"
    x-data="{
        paso: 1,
        maxPaso: 1,
        openOverrideModal: false,
        modalError: '',
        requiereOverride: @entangle('clima_requiere_override').live,
        esDiaCaido: @entangle('es_dia_caido').live,
        overrideConfirmado: @entangle('clima_override_confirmado').live,
        overrideMotivo: @entangle('clima_override_motivo').live
    }"
>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.clipboard-document-check class="size-6" />
            Partes Diarios
        </h1>
    </div>

    <x-flash-messages />

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        @canany(['crear-partes-diarios', 'editar-partes-diarios'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nuevo Parte Diario
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Partes Diarios
        </button>
    </div>

    <!-- Pestaña 1: Nuevo Parte Diario -->
    @if($tab_activo === 'nuevo')
        @canany(['crear-partes-diarios', 'editar-partes-diarios'])

        <!-- Stepper Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between max-w-xl">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors border-2"
                        :class="paso >= 1 ? 'bg-pino text-blanco border-pino' : 'bg-blanco text-tinta-suave border-arena'">
                        <span x-show="paso > 1"><flux:icon.check class="size-4" /></span>
                        <span x-show="paso <= 1">1</span>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline" :class="paso === 1 ? 'text-pino' : 'text-tinta-suave'">Datos</span>
                </div>
                <div class="flex-1 h-0.5 mx-3" :class="paso > 1 ? 'bg-pino' : 'bg-arena'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors border-2"
                        :class="paso >= 2 ? 'bg-pino text-blanco border-pino' : 'bg-blanco text-tinta-suave border-arena'">
                        <span x-show="paso > 2"><flux:icon.check class="size-4" /></span>
                        <span x-show="paso <= 2">2</span>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline" :class="paso === 2 ? 'text-pino' : 'text-tinta-suave'">Producción</span>
                </div>
                <div class="flex-1 h-0.5 mx-3" :class="paso > 2 ? 'bg-pino' : 'bg-arena'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors border-2"
                        :class="paso >= 3 ? 'bg-pino text-blanco border-pino' : 'bg-blanco text-tinta-suave border-arena'">
                        <span>3</span>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline" :class="paso === 3 ? 'text-pino' : 'text-tinta-suave'">Insumos</span>
                </div>
            </div>
        </div>

        <div id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab" class="tab-pane-content">

            <!-- SECCIÓN 1: Datos Maestros -->
            <div x-show="paso === 1" x-transition>
            <x-ui.card class="mb-5 overflow-hidden">
                <div class="bg-corteza-suave px-6 py-4 border-b border-arena">
                    <h5 class="text-lg font-semibold text-tinta mb-0 flex items-center gap-2">
                        @if($parte_id)
                            <flux:icon.pencil-square class="size-5" />
                            Modificar Parte Diario
                        @else
                            <flux:icon.plus class="size-5" />
                            Nuevo Parte Diario
                        @endif
                    </h5>
                </div>
                <div class="p-6">
                    <!-- Fila 1: Fecha, Lote, Tarea, Día Caído -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Fecha <span class="text-tierra">*</span></label>
                            <input type="date"
                                   wire:model="fecha"
                                   max="{{ date('Y-m-d') }}"
                                   min="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                   class="form-input @error('fecha') ring-2 ring-tierra @enderror">
                            @error('fecha') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Lote <span class="text-tierra">*</span></label>
                            <select wire:model.live="id_lote" class="form-input @error('id_lote') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione un lote...</option>
                                @foreach($this->lotes as $lote)
                                    <option value="{{ $lote->id_lote }}" wire:key="option-{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="id_lote" class="text-tinta-suave text-sm mt-1">
                                <flux:icon.arrow-path class="inline-block size-4 animate-spin" /> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-tinta mb-2">Tarea del lote <span class="text-tierra">*</span></label>
                            <select wire:model.live="id_lote_tarea" wire:key="lote-tareas-{{ $id_lote }}" class="form-input @error('id_lote_tarea') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione una tarea...</option>
                                @foreach($this->loteTareas as $tarea)
                                    <option value="{{ $tarea->id_lote_tarea }}" wire:key="option-{{ $tarea->id_lote_tarea }}">
                                        #{{ $tarea->id_lote_tarea }} - {{ $tarea->tipo_tarea_label }} ({{ $tarea->estado }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_lote_tarea') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                            @if(!$id_lote)
                                <div class="text-tinta-suave text-sm mt-2">Seleccioná un lote para cargar las tareas.</div>
                            @elseif($this->loteTareas->isEmpty())
                                <div class="text-tinta-suave text-sm mt-2">Este lote no tiene tareas cargadas.</div>
                            @endif

                            @if($id_lote && $this->loteTareas->isEmpty())
                                <div class="mt-3 rounded-sm border border-arena bg-corteza-suave p-3" wire:key="tarea-rapida-{{ $id_lote }}">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-tinta">No hay tareas para este lote</div>
                                        <x-ui.button variant="primary" size="sm" icon="plus" wire:click="$set('mostrarModalTareaRapida', true)">
                                            Crear tarea
                                        </x-ui.button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Día Caído</label>
                            <div class="flex items-center mt-2">
                                <input type="checkbox" id="diaCaidoSwitch" wire:model.live="es_dia_caido" class="w-5 h-5 rounded border-arena text-pino focus:ring-pino">
                                <label for="diaCaidoSwitch" class="ml-2">
                                    <span class="inline-block px-3 py-1 rounded-sm text-sm font-medium {{ $es_dia_caido ? 'bg-resina-suave text-resina' : 'bg-corteza-suave text-tinta' }}">
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
                            $estadoVariant = $estado === 'INACTIVO'
                                ? 'danger'
                                : ($estado === 'OPERATIVO_CONDICIONAL' ? 'warning' : 'success');
                        @endphp
                        <div class="mb-6">
                            <div class="rounded-sm border border-arena bg-corteza-suave p-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <x-ui.badge variant="{{ $estadoVariant }}">
                                        Estado pronostico: {{ $estadoLabel }}
                                    </x-ui.badge>
                                    @if($clima_es_fin_de_semana)
                                        <x-ui.badge variant="neutral">Fin de semana</x-ui.badge>
                                    @endif
                                    @if($clima_fuente === 'fallback')
                                        <x-ui.badge variant="warning">Fallback: sin datos de API</x-ui.badge>
                                    @endif
                                </div>
                                @if($clima_razon)
                                    <p class="text-sm text-tinta-suave mt-2">Motivo: {{ $clima_razon }}</p>
                                @endif
                            </div>
                        </div>

                    @endif

                    <!-- Fila 2: Observaciones -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Observaciones</label>
                            <textarea wire:model="observaciones" class="form-input" rows="2" placeholder="Observaciones adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Nav: Siguiente from Step 1 -->
            <div class="flex justify-end gap-3 mb-6" x-show="paso === 1">
                <x-ui.button variant="primary" icon="arrow-right"
                    type="button"
                    @click="if(await $wire.validarPaso1()) { paso = 2; maxPaso = Math.max(maxPaso, 2); }">
                    Siguiente
                </x-ui.button>
            </div>
            </div>

            <!-- SECCIÓN 2: Registro de Producción (Si NO es día caído) -->
            <div x-show="paso === 2 && !esDiaCaido" x-transition>
                <livewire:carga-form
                    :id_lote="$id_lote"
                    :fecha="$fecha"
                    :empleados_asignados_ids="$empleados_asignados_ids"
                    :maquinarias_asignadas_ids="$maquinarias_asignadas_ids"
                    :cargas="$cargas"
                />
            </div>

            <!-- SECCIÓN 3: Jornales (Si ES día caído) — Child Component -->
            <div x-show="paso === 2 && esDiaCaido" x-transition>
                <livewire:jornal-form
                    :fecha="$fecha"
                    :empleados-filtrados="$this->empleadosFiltrados"
                    :es-dia-caido="$es_dia_caido"
                    :jornales="$jornales"
                    wire:key="jornal-form-{{ $parte_id ?? 'new' }}"
                />
            </div>

            <!-- Nav: Volver + Siguiente from Step 2 -->
            <div class="flex justify-between gap-3 mb-6" x-show="paso === 2">
                <x-ui.button variant="secondary" icon="arrow-left" type="button" @click="paso = 1">
                    Volver
                </x-ui.button>
                <x-ui.button variant="primary" icon="arrow-right" type="button" @click="paso = 3; maxPaso = Math.max(maxPaso, 3);">
                    Siguiente
                </x-ui.button>
            </div>

            <!-- SECCIÓN 4: Movimientos de Insumos — Child Component -->
            <div x-show="paso === 3" x-transition>
                <!-- Summary counts -->
                @if(count($cargas) > 0 || count($jornales) > 0 || count($movimientos) > 0)
                    <div class="mb-4 flex flex-wrap gap-3">
                        @if(count($cargas) > 0)
                            <x-ui.badge variant="info">{{ count($cargas) }} carga(s) · {{ number_format($total_toneladas, 2) }} ton</x-ui.badge>
                        @endif
                        @if(count($jornales) > 0)
                            <x-ui.badge variant="warning">{{ count($jornales) }} jornal(es)</x-ui.badge>
                        @endif
                        @if(count($movimientos) > 0)
                            <x-ui.badge variant="success">{{ count($movimientos) }} movimiento(s)</x-ui.badge>
                        @endif
                    </div>
                @endif

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
                        <label class="block text-sm font-semibold text-tinta mb-2">Tipo <span class="text-tierra">*</span></label>
                        <select wire:model.live="nueva_tarea_tipo_tarea" class="form-input">
                            <option value="">Seleccione...</option>
                            @foreach($this->taskTypes as $taskType)
                                <option value="{{ $taskType->value }}" wire:key="option-{{ $taskType->value }}">{{ $taskType->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-2">Superficie (ha)</label>
                        <input type="number" wire:model.live="nueva_tarea_superficie_afectada_ha" step="0.01" min="0"
                            class="form-input"
                            placeholder="(opcional)">
                    </div>

                    <div class="flex gap-3 justify-end pt-2">
                        <x-ui.button variant="secondary" wire:click="$set('mostrarModalTareaRapida', false)">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" wire:click="crearTareaRapida" :disabled="!$nueva_tarea_tipo_tarea">
                            Crear
                        </x-ui.button>
                    </div>
                </div>
            </flux:modal>

            <!-- BOTÓN GUARDAR -->
            <x-ui.card class="overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between gap-3">
                        <x-ui.button variant="secondary" icon="arrow-left" type="button" @click="paso = 2">
                            Volver
                        </x-ui.button>
                        <div class="flex gap-3">
                            <x-ui.button variant="secondary" icon="x-mark" type="button" wire:click.prevent="cancelarEdicion" wire:loading.attr="disabled">
                                Cancelar
                            </x-ui.button>
                            @canany(['crear-partes-diarios', 'editar-partes-diarios'])
                            <x-ui.button
                                variant="primary"
                                icon="check"
                                type="button"
                                @click.prevent="
                                    if (!esDiaCaido && requiereOverride && !overrideConfirmado) {
                                        modalError = '';
                                        openOverrideModal = true;
                                    } else {
                                        $wire.guardar();
                                    }
                                "
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                            >
                                <span wire:loading.remove wire:target="guardar">Guardar Parte Diario</span>
                                <span wire:loading wire:target="guardar"><flux:icon.arrow-path class="inline-block size-4 animate-spin mr-2" />Guardando...</span>
                            </x-ui.button>
                            @endcanany
                        </div>
                    </div>
                </div>
            </x-ui.card>
            </div>
        </div>
        @endcanany
    @endif

    @include('livewire.partials.clima-override-modal')

    @if($tab_activo === 'listado')
        @include('livewire.partials.partes-listado')
    @endif
</div>
