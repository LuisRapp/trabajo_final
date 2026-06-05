<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-truck"></i> Maquinarias
        </h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Maquinaria', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-maquinarias', 'editar-maquinarias'])],
        ['value' => 'listado', 'label' => 'Listado de Maquinarias', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-maquinarias', 'editar-maquinarias'])
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-{{ $maquinaria_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $maquinaria_id ? 'Editar Maquinaria' : 'Nueva Maquinaria' }}
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Maquinaria <span class="text-red-500">*</span></label>
                                <select wire:model="id_tipo_maquinaria" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_tipo_maquinaria') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id_tipo_maquinaria }}" wire:key="option-{{ $tipo->id_tipo_maquinaria }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_tipo_maquinaria') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Modelo <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="modelo" placeholder="Modelo de la maquinaria" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('modelo') ring-2 ring-red-500 @enderror">
                                @error('modelo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Estado <span class="text-red-500">*</span></label>
                                <select wire:model="estado" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('estado') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="asignada">Asignada</option>
                                    <option value="en_mantenimiento">En Mantenimiento</option>
                                </select>
                                @error('estado') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">¿Es alquilada?</label>
                                <select wire:model="es_alquilada" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio Actividades <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="fecha_inicio_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_inicio_actividades') ring-2 ring-red-500 @enderror">
                                @error('fecha_inicio_actividades') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Umbral Toneladas <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="umbral_toneladas" placeholder="Ej: 1000" step="0.01" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('umbral_toneladas') ring-2 ring-red-500 @enderror">
                                @error('umbral_toneladas') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($maquinaria_id)
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            @canany(['crear-maquinarias', 'editar-maquinarias'])
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> {{ $maquinaria_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcanany
    @elseif($tab_activo === 'listado')
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6">
                    <x-search-input placeholder="Buscar por tipo, modelo o estado..." />

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Tipo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Modelo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Alquilada</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Umbral (ton)</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($maquinarias as $maquinaria)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $maquinaria->id_maquinaria }}">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $maquinaria->id_maquinaria }}</span></td>
                                        <td class="px-3 py-3 text-slate-600">{{ $maquinaria->tipoMaquinaria->nombre ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $maquinaria->modelo }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                {{ $maquinaria->estado === 'disponible' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                                                {{ $maquinaria->estado === 'asignada' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                                {{ $maquinaria->estado === 'en_mantenimiento' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                                                {{ ucfirst($maquinaria->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">{{ $maquinaria->es_alquilada ? 'Sí' : 'No' }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($maquinaria->umbral_toneladas, 2, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-center">
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
                </div>
            </div>
        </div>
    @endif
</div>
