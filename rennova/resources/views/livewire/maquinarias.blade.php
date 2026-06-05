<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🚜 Maquinarias</h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Maquinaria', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-maquinarias', 'editar-maquinarias'])],
        ['value' => 'listado', 'label' => 'Listado de Maquinarias', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-maquinarias', 'editar-maquinarias'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $maquinaria_id ? '✏️ Editar Maquinaria' : '➕ Nueva Maquinaria' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_tipo_maquinaria" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Maquinaria <span class="text-red-500">*</span></label>
                            <select id="id_tipo_maquinaria" wire:model="id_tipo_maquinaria"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_tipo_maquinaria') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_maquinaria }}" wire:key="option-{{ $tipo->id_tipo_maquinaria }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_maquinaria') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="modelo" class="block text-sm font-semibold text-slate-700 mb-1.5">Modelo <span class="text-red-500">*</span></label>
                            <input type="text" id="modelo" wire:model="modelo"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('modelo') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Modelo de la maquinaria">
                            @error('modelo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                            <select id="estado" wire:model="estado"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('estado') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                <option value="disponible">Disponible</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_mantenimiento">En Mantenimiento</option>
                            </select>
                            @error('estado') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="es_alquilada" class="block text-sm font-semibold text-slate-700 mb-1.5">¿Es alquilada?</label>
                            <select id="es_alquilada" wire:model="es_alquilada"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div>
                            <label for="fecha_inicio_actividades" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Inicio Actividades <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_inicio_actividades" wire:model="fecha_inicio_actividades"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_inicio_actividades') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_inicio_actividades') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="umbral_toneladas" class="block text-sm font-semibold text-slate-700 mb-1.5">Umbral Toneladas <span class="text-red-500">*</span></label>
                            <input type="number" id="umbral_toneladas" wire:model="umbral_toneladas" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('umbral_toneladas') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Ej: 1000">
                            @error('umbral_toneladas') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($maquinaria_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-maquinarias', 'editar-maquinarias'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $maquinaria_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @elseif($tab_activo === 'listado')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <x-search-input placeholder="Buscar por tipo, modelo o estado..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Modelo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alquilada</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Umbral (ton)</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($maquinarias as $maquinaria)
                                <tr wire:key="row-{{ $maquinaria->id_maquinaria }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $maquinaria->id_maquinaria }}</span></td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $maquinaria->tipoMaquinaria->nombre ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $maquinaria->modelo }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $maquinaria->estado === 'disponible' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                            {{ $maquinaria->estado === 'asignada' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                            {{ $maquinaria->estado === 'en_mantenimiento' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                                            {{ ucfirst($maquinaria->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $maquinaria->es_alquilada ? 'Sí' : 'No' }}</td>
                                    <td class="px-3 py-2.5 text-right text-slate-600">{{ number_format($maquinaria->umbral_toneladas, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $maquinaria->id_maquinaria }})"
                                            deleteWireClick="eliminar({{ $maquinaria->id_maquinaria }})"
                                            deleteMessage="¿Está seguro de eliminar esta maquinaria?"
                                            :canEdit="auth()->user()->can('editar-maquinarias')"
                                            :canDelete="auth()->user()->can('eliminar-maquinarias')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay maquinarias registradas." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $maquinarias->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
