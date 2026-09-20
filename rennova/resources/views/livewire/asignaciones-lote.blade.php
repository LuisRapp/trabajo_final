<div class="w-full">
    <!-- Pestañas (Tabs) -->
    <div class="flex border-b border-arena mb-6" id="asignacionesTabs" role="tablist">
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $mostrar_historial ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
                id="historial-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', true)">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Historial de Asignaciones
        </button>
        @canany(['crear-asignaciones-lote', 'editar-asignaciones-lote'])
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ !$mostrar_historial ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}"
                id="formulario-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', false)">
            @if($modo === 'editar')
                <flux:icon.pencil-square class="size-4 inline mr-1" />
                Modificar Asignación
            @else
                <flux:icon.plus class="size-4 inline mr-1" />
                Nueva Asignación
            @endif
        </button>
        @endcanany
    </div>

    <div id="asignacionesTabContent">
        <!-- Pestaña 1: Historial de Asignaciones -->
        <div class="{{ $mostrar_historial ? '' : 'hidden' }}"
             id="historial-asignaciones"
             role="tabpanel">
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                        <flux:icon.list-bullet class="size-5" />
                        Historial de Asignaciones por Lote
                    </h5>
                    @can('crear-asignaciones-lote')
                    <x-ui.button variant="primary" icon="plus" wire:click="nuevaAsignacion">
                        Nueva Asignación
                    </x-ui.button>
                    @endcan
                </div>
                <div class="p-6">
                    @if (session()->has('message'))
                        <x-ui.alert variant="success" class="mb-5">
                            {{ session('message') }}
                        </x-ui.alert>
                    @endif
                    @if (session()->has('error'))
                        <x-ui.alert variant="danger" class="mb-5">
                            {{ session('error') }}
                        </x-ui.alert>
                    @endif

                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Lote</th>
                                    <th>Estado</th>
                                    <th>Empleados Asignados</th>
                                    <th>Maquinarias Asignadas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historial as $lote)
                                    <tr wire:key="row-{{ $lote->id_lote }}">
                                        <td>
                                            <strong>Lote #{{ $lote->id_lote }}</strong><br>
                                            <small class="text-tinta-suave">{{ $lote->ubicacion }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $estadoVariant = match($lote->estado) {
                                                    'activo' => 'success',
                                                    'cerrado' => 'neutral',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <x-ui.badge variant="{{ $estadoVariant }}">
                                                {{ ucfirst($lote->estado) }}
                                            </x-ui.badge>
                                        </td>
                                        <td>
                                            @if($lote->empleados->count() > 0)
                                                <small>
                                                    @foreach($lote->empleados as $emp)
                                                        <x-ui.badge variant="info" class="mr-1" wire:key="emp-{{ $emp->id_empleado }}">{{ $emp->apellido }}</x-ui.badge>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-tinta-suave">Total: {{ $lote->empleados->count() }}</small>
                                            @else
                                                <span class="text-tinta-suave">Sin empleados</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lote->maquinarias->count() > 0)
                                                <small>
                                                    @foreach($lote->maquinarias as $maq)
                                                        <x-ui.badge variant="neutral" class="mr-1" wire:key="maq-{{ $maq->id_maquinaria }}">{{ $maq->modelo }}</x-ui.badge>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-tinta-suave">Total: {{ $lote->maquinarias->count() }}</small>
                                            @else
                                                <span class="text-tinta-suave">Sin maquinarias</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex rounded-sm shadow-xs">
                                                @can('editar-asignaciones-lote')
                                                <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                        wire:click="editarAsignacion({{ $lote->id_lote }})"
                                                        title="Modificar asignaciones"
                                                        class="rounded-r-none border-r-0" />
                                                @endcan
                                                @if($lote->estado !== 'cerrado')
                                                    @can('editar-asignaciones-lote')
                                                    <x-ui.button variant="ghost" size="sm" icon="check"
                                                            wire:click="liberar({{ $lote->id_lote }})"
                                                            onclick="return confirm('¿Cerrar este lote y liberar recursos?')"
                                                            title="Finalizar y liberar"
                                                            class="rounded-none border-x-0" />
                                                    @endcan
                                                @endif
                                                @can('eliminar-asignaciones-lote')
                                                <x-ui.button variant="ghost" size="sm" icon="trash"
                                                        wire:click="eliminarAsignacion({{ $lote->id_lote }})"
                                                        onclick="return confirm('¿Eliminar todas las asignaciones de este lote?')"
                                                        title="Eliminar asignaciones"
                                                        class="rounded-l-none" />
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-tinta-suave">
                                            No hay asignaciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </x-ui.table-container>
                </div>
            </x-ui.card>
        </div>

        <!-- Pestaña 2: Formulario de Asignación -->
        @canany(['crear-asignaciones-lote', 'editar-asignaciones-lote'])
        <div class="{{ !$mostrar_historial ? '' : 'hidden' }}"
             id="formulario-asignacion"
             role="tabpanel">
            <x-ui.card class="overflow-hidden mb-5" id="formulario-asignacion-card">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex items-center">
                    <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                        @if($modo === 'editar')
                            <flux:icon.pencil-square class="size-5" />
                            Modificar Asignación
                        @else
                            <flux:icon.plus class="size-5" />
                            Nueva Asignación
                        @endif
                    </h5>
                </div>
                <div class="p-6">
                    @if (session()->has('message'))
                        <x-ui.alert variant="success" class="mb-5">
                            {{ session('message') }}
                        </x-ui.alert>
                    @endif
                    @if (session()->has('error'))
                        <x-ui.alert variant="danger" class="mb-5">
                            {{ session('error') }}
                        </x-ui.alert>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Lote <span class="text-tierra">*</span></label>
                            <select class="form-input @error('id_lote') border-tierra bg-tierra-suave @enderror" wire:model.live="id_lote">
                                <option value="">Seleccione un lote</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}" wire:key="option-{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                            @error('id_lote') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">Primero seleccione el Lote para ver y editar sus asignaciones.</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.card class="overflow-hidden border-arena">
                            <div class="bg-tinta text-blanco px-6 py-4 flex justify-between items-center">
                                <strong class="flex items-center gap-2">
                                    <flux:icon.users class="size-5" />
                                    Empleados asignados
                                </strong>
                                @if($id_lote)
                                    <x-ui.badge variant="neutral" class="bg-blanco/20 text-blanco">{{ count($empleados_seleccionados) }} seleccionados</x-ui.badge>
                                @endif
                            </div>
                            <div class="p-6">
                                @if($id_lote)
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-input"
                                               placeholder="Buscar empleado..."
                                               wire:model.live="busqueda_empleado">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-arena rounded-sm p-3">
                                        @forelse($this->empleadosFiltrados as $emp)
                                            <div class="flex items-center gap-2 py-1" wire:key="item-{{ $emp->id_empleado }}">
                                                <input class="rounded border-arena text-pino focus:ring-pino"
                                                       type="checkbox"
                                                       value="{{ $emp->id_empleado }}"
                                                       id="emp-{{ $emp->id_empleado }}"
                                                       wire:model.live="empleados_seleccionados">
                                                <label class="text-sm text-tinta" for="emp-{{ $emp->id_empleado }}">
                                                    {{ $emp->apellido }}, {{ $emp->nombre }}
                                                    <small class="text-tinta-suave">- {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                </label>
                                            </div>
                                        @empty
                                            <small class="text-tinta-suave">No se encontraron empleados.</small>
                                        @endforelse
                                    </div>
                                    <small class="text-tinta-suave block mt-3">
                                        Seleccione todos los empleados que trabajarán en este lote.
                                    </small>
                                @else
                                    <x-ui.alert variant="warning" dismissible="false">
                                        Seleccione un Lote para habilitar esta sección.
                                    </x-ui.alert>
                                @endif
                            </div>
                        </x-ui.card>

                        <x-ui.card class="overflow-hidden border-pino/30">
                            <div class="bg-pino text-blanco px-6 py-4 flex justify-between items-center">
                                <strong class="flex items-center gap-2">
                                    <flux:icon.truck class="size-5" />
                                    Maquinarias asignadas
                                </strong>
                                @if($id_lote)
                                    <x-ui.badge variant="neutral" class="bg-blanco/20 text-blanco">{{ count($maquinarias_seleccionadas) }} seleccionadas</x-ui.badge>
                                @endif
                            </div>
                            <div class="p-6">
                                @if($id_lote)
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-input"
                                               placeholder="Buscar maquinaria..."
                                               wire:model.live="busqueda_maquinaria">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-arena rounded-sm p-3">
                                        @forelse($this->maquinariasFiltrada as $maq)
                                            <div class="flex items-center gap-2 py-1" wire:key="item-{{ $maq->id_maquinaria }}">
                                                <input class="rounded border-arena text-pino focus:ring-pino"
                                                       type="checkbox"
                                                       value="{{ $maq->id_maquinaria }}"
                                                       id="maq-{{ $maq->id_maquinaria }}"
                                                       wire:model.live="maquinarias_seleccionadas">
                                                <label class="text-sm text-tinta" for="maq-{{ $maq->id_maquinaria }}">
                                                    {{ $maq->modelo }}
                                                    <small class="text-tinta-suave">- {{ $maq->estado }} - {{ $maq->tipoMaquinaria->nombre ?? 'N/A' }}</small>
                                                </label>
                                            </div>
                                        @empty
                                            <small class="text-tinta-suave">No se encontraron maquinarias.</small>
                                        @endforelse
                                    </div>
                                    <x-ui.alert variant="info" class="mt-3" dismissible="false">
                                        Si solo hay una maquinaria asignada al lote, se preseleccionará en el Parte Diario.
                                    </x-ui.alert>
                                @else
                                    <x-ui.alert variant="warning" dismissible="false">
                                        Seleccione un Lote para habilitar esta sección.
                                    </x-ui.alert>
                                @endif
                            </div>
                        </x-ui.card>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <x-ui.button variant="primary" icon="check"
                                wire:click="guardar"
                                wire:loading.attr="disabled"
                                :disabled="!$id_lote">
                            Guardar asignaciones
                        </x-ui.button>
                        <x-ui.button variant="secondary" icon="x-mark" wire:click="cancelar">
                            Cancelar
                        </x-ui.button>
                        <div wire:loading wire:target="guardar" class="text-tinta-suave self-center">
                            <flux:icon.arrow-path class="inline size-4 animate-spin" /> Guardando...
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
        @endcanany
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            document.getElementById('formulario-asignacion-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
