<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Pestañas (Tabs) -->
    <div class="flex border-b border-slate-200 mb-6" id="asignacionesTabs" role="tablist">
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $mostrar_historial ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
                id="historial-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', true)">
            📋 Historial de Asignaciones
        </button>
        @canany(['crear-asignaciones-lote', 'editar-asignaciones-lote'])
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ !$mostrar_historial ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
                id="formulario-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', false)">
            {{ $modo === 'editar' ? '✏️ Modificar Asignación' : '➕ Nueva Asignación' }}
        </button>
        @endcanany
    </div>

    <div id="asignacionesTabContent">
        <!-- Pestaña 1: Historial de Asignaciones -->
        <div class="{{ $mostrar_historial ? '' : 'hidden' }}"
             id="historial-asignaciones"
             role="tabpanel">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">📋 Historial de Asignaciones por Lote</h5>
                    @can('crear-asignaciones-lote')
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="nuevaAsignacion">
                        ➕ Nueva Asignación
                    </button>
                    @endcan
                </div>
                <div class="p-6">
                    @if (session()->has('message'))
                        <div x-data="{ open: true }" x-show="open" x-transition
                            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
                            <span class="text-emerald-600">✓</span>
                            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
                            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div x-data="{ open: true }" x-show="open" x-transition
                            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
                            <span class="text-red-600">⚠</span>
                            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
                            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Empleados Asignados</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Maquinarias Asignadas</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($historial as $lote)
                                    <tr wire:key="row-{{ $lote->id_lote }}" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <strong>Lote #{{ $lote->id_lote }}</strong><br>
                                            <small class="text-slate-500">{{ $lote->ubicacion }}</small>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lote->estado === 'activo' ? 'bg-emerald-100 text-emerald-700' : ($lote->estado === 'cerrado' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">
                                                {{ ucfirst($lote->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @if($lote->empleados->count() > 0)
                                                <small>
                                                    @foreach($lote->empleados as $emp)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 mr-1" wire:key="emp-{{ $emp->id_empleado }}">{{ $emp->apellido }}</span>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-slate-500">Total: {{ $lote->empleados->count() }}</small>
                                            @else
                                                <span class="text-slate-400">Sin empleados</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @if($lote->maquinarias->count() > 0)
                                                <small>
                                                    @foreach($lote->maquinarias as $maq)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand mr-1" wire:key="maq-{{ $maq->id_maquinaria }}">{{ $maq->modelo }}</span>
                                                    @endforeach
                                                </small>
                                                <br><small class="text-slate-500">Total: {{ $lote->maquinarias->count() }}</small>
                                            @else
                                                <span class="text-slate-400">Sin maquinarias</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <div class="inline-flex rounded-lg shadow-sm">
                                                @can('editar-asignaciones-lote')
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-brand bg-white text-brand hover:bg-brand/5 rounded-l-lg text-xs font-medium transition-colors"
                                                        wire:click="editarAsignacion({{ $lote->id_lote }})"
                                                        title="Modificar asignaciones">
                                                    ✏️
                                                </button>
                                                @endcan
                                                @if($lote->estado !== 'cerrado')
                                                    @can('editar-asignaciones-lote')
                                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-amber-500 bg-white text-amber-600 hover:bg-amber-50 text-xs font-medium transition-colors"
                                                            wire:click="liberar({{ $lote->id_lote }})"
                                                            onclick="return confirm('¿Cerrar este lote y liberar recursos?')"
                                                            title="Finalizar y liberar">
                                                        ✓
                                                    </button>
                                                    @endcan
                                                @endif
                                                @can('eliminar-asignaciones-lote')
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-300 bg-white text-red-600 hover:bg-red-50 rounded-r-lg text-xs font-medium transition-colors"
                                                        wire:click="eliminarAsignacion({{ $lote->id_lote }})"
                                                        onclick="return confirm('¿Eliminar todas las asignaciones de este lote?')"
                                                        title="Eliminar asignaciones">
                                                    🗑️
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-slate-400">
                                            <div class="text-5xl mb-2">📥</div>
                                            <p>No hay asignaciones registradas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Formulario de Asignación -->
        @canany(['crear-asignaciones-lote', 'editar-asignaciones-lote'])
        <div class="{{ !$mostrar_historial ? '' : 'hidden' }}"
             id="formulario-asignacion"
             role="tabpanel">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6" id="formulario-asignacion-card">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center">
                    <h5 class="text-lg font-semibold text-slate-800">
                        {{ $modo === 'editar' ? '✏️ Modificar Asignación' : '➕ Nueva Asignación' }}
                    </h5>
                </div>
                <div class="p-6">
                    @if (session()->has('message'))
                        <div x-data="{ open: true }" x-show="open" x-transition
                            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
                            <span class="text-emerald-600">✓</span>
                            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
                            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div x-data="{ open: true }" x-show="open" x-transition
                            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
                            <span class="text-red-600">⚠</span>
                            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
                            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lote <span class="text-red-500">*</span></label>
                            <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_lote') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror" wire:model.live="id_lote">
                                <option value="">Seleccione un lote</option>
                                @foreach($lotes as $l)
                                    <option value="{{ $l->id_lote }}" wire:key="option-{{ $l->id_lote }}">Lote #{{ $l->id_lote }} - {{ $l->ubicacion }} ({{ $l->estado }})</option>
                                @endforeach
                            </select>
                            @error('id_lote') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            <small class="text-slate-500 text-xs mt-1 block">Primero seleccione el Lote para ver y editar sus asignaciones.</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-300 overflow-hidden">
                            <div class="bg-slate-600 text-white px-6 py-4 flex justify-between items-center">
                                <strong>👥 Empleados asignados</strong>
                                @if($id_lote)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">{{ count($empleados_seleccionados) }} seleccionados</span>
                                @endif
                            </div>
                            <div class="p-6">
                                @if($id_lote)
                                    <div class="mb-3">
                                        <input type="text"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                               placeholder="Buscar empleado..."
                                               wire:model.live="busqueda_empleado">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                        @forelse($this->empleadosFiltrados as $emp)
                                            <div class="flex items-center gap-2 py-1" wire:key="item-{{ $emp->id_empleado }}">
                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       value="{{ $emp->id_empleado }}"
                                                       id="emp-{{ $emp->id_empleado }}"
                                                       wire:model.live="empleados_seleccionados">
                                                <label class="text-sm text-slate-700" for="emp-{{ $emp->id_empleado }}">
                                                    {{ $emp->apellido }}, {{ $emp->nombre }}
                                                    <small class="text-slate-500">- {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}</small>
                                                </label>
                                            </div>
                                        @empty
                                            <small class="text-slate-400">No se encontraron empleados.</small>
                                        @endforelse
                                    </div>
                                    <small class="text-slate-500 block mt-3">
                                        ℹ️ Seleccione todos los empleados que trabajarán en este lote.
                                    </small>
                                @else
                                    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                                        <small>Seleccione un Lote para habilitar esta sección.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                            <div class="bg-brand text-white px-6 py-4 flex justify-between items-center">
                                <strong>🚛 Maquinarias asignadas</strong>
                                @if($id_lote)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">{{ count($maquinarias_seleccionadas) }} seleccionadas</span>
                                @endif
                            </div>
                            <div class="p-6">
                                @if($id_lote)
                                    <div class="mb-3">
                                        <input type="text"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                               placeholder="Buscar maquinaria..."
                                               wire:model.live="busqueda_maquinaria">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                        @forelse($this->maquinariasFiltrada as $maq)
                                            <div class="flex items-center gap-2 py-1" wire:key="item-{{ $maq->id_maquinaria }}">
                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       value="{{ $maq->id_maquinaria }}"
                                                       id="maq-{{ $maq->id_maquinaria }}"
                                                       wire:model.live="maquinarias_seleccionadas">
                                                <label class="text-sm text-slate-700" for="maq-{{ $maq->id_maquinaria }}">
                                                    {{ $maq->modelo }}
                                                    <small class="text-slate-500">- {{ $maq->estado }} - {{ $maq->tipoMaquinaria->nombre ?? 'N/A' }}</small>
                                                </label>
                                            </div>
                                        @empty
                                            <small class="text-slate-400">No se encontraron maquinarias.</small>
                                        @endforelse
                                    </div>
                                    <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mt-3">
                                        <small>
                                            ℹ️ Si solo hay una maquinaria asignada al lote, se preseleccionará en el Parte Diario.
                                        </small>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                                        <small>Seleccione un Lote para habilitar esta sección.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        @canany(['crear-asignaciones-lote', 'editar-asignaciones-lote'])
                        <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors"
                                wire:click="guardar"
                                wire:loading.attr="disabled"
                                @disabled(!$id_lote)>
                            💾 Guardar asignaciones
                        </button>
                        @endcanany
                        <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cancelar">
                            ✕ Cancelar
                        </button>
                        <div wire:loading wire:target="guardar" class="text-slate-500 self-center">
                            ↻ Guardando...
                        </div>
                    </div>
                </div>
            </div>
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
