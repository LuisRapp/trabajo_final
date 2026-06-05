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

    <div class="mb-6 flex gap-0">
        @canany(['crear-maquinarias', 'editar-maquinarias'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-plus-circle"></i> Nueva Maquinaria
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-list-ul"></i> Listado de Maquinarias
        </button>
    </div>

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
                                    <option value="operativa">Operativa</option>
                                    <option value="en_mantenimiento">En Mantenimiento</option>
                                    <option value="fuera_de_servicio">Fuera de Servicio</option>
                                </select>
                                @error('estado') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">¿Es Alquilada?</label>
                                <select wire:model="es_alquilada" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('es_alquilada') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                                @error('es_alquilada') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio Actividades</label>
                                <input type="date" wire:model="fecha_inicio_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_inicio_actividades') ring-2 ring-red-500 @enderror">
                                @error('fecha_inicio_actividades') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Umbral Mantenimiento (ton)</label>
                                <input type="number" step="0.1" min="0" wire:model="umbral_toneladas" placeholder="Ej: 100.00" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('umbral_toneladas') ring-2 ring-red-500 @enderror">
                                <small class="text-slate-500 text-xs mt-1 block">Toneladas acumuladas para mantenimiento</small>
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
                    <!-- Buscador -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                            <i class="bi bi-search text-slate-500"></i>
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por tipo, modelo o estado..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Tipo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Modelo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Alquilada</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Umbral (ton)</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Inicio</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($maquinarias as $maquinaria)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $maquinaria->id_maquinaria }}">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $maquinaria->id_maquinaria }}</span></td>
                                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $maquinaria->modelo }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $maquinaria->estado == 'operativa' ? 'bg-green-50 text-green-700 border border-green-200' : 
                                                   ($maquinaria->estado == 'en_mantenimiento' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 
                                                   'bg-red-50 text-red-700 border border-red-200') }}">
                                                {{ ucfirst(str_replace('_', ' ', $maquinaria->estado)) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            @if($maquinaria->es_alquilada)
                                                <i class="bi bi-check-circle-fill" style="color: #2d7a4f;"></i> Sí
                                            @else
                                                <i class="bi bi-x-circle-fill text-slate-400"></i> No
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            @if($maquinaria->umbral_toneladas)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    <i class="bi bi-speedometer2"></i> {{ number_format($maquinaria->umbral_toneladas, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">No configurado</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">{{ $maquinaria->fecha_inicio_actividades ? \Carbon\Carbon::parse($maquinaria->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex gap-1 justify-center">
                                                @can('editar-maquinarias')
                                                <button wire:click="editar({{ $maquinaria->id_maquinaria }})" @click="$set('tab_activo', 'nuevo')" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                @endcan
                                                @can('eliminar-maquinarias')
                                                <button wire:click="eliminar({{ $maquinaria->id_maquinaria }})" onclick="return confirm('¿Está seguro de eliminar esta maquinaria?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-8 text-center">
                                            <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-slate-500 font-medium">No hay maquinarias registradas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- JavaScript para cambiar entre pestañas -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('maquinariaGuardada', () => {
            // Livewire actualizará automáticamente
        });
    });
</script>
</script>
