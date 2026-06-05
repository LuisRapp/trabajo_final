<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-box-seam"></i> Insumos
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
        @canany(['crear-insumos', 'editar-insumos'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-plus-circle"></i> Nuevo Insumo
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-list-ul"></i> Listado de Insumos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-insumos', 'editar-insumos'])
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-{{ $insumo_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $insumo_id ? 'Editar Insumo' : 'Nuevo Insumo' }}
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nombre" placeholder="Nombre del insumo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('nombre') ring-2 ring-red-500 @enderror">
                            @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Unidad de Medida <span class="text-red-500">*</span></label>
                                <select wire:model="id_unidad_medida" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_unidad_medida') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id_unidad_medida }}" wire:key="option-{{ $unidad->id_unidad_medida }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                    @endforeach
                                </select>
                                @error('id_unidad_medida') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Proveedor Principal <span class="text-red-500">*</span></label>
                                <select wire:model="id_proveedor" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_proveedor') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('id_proveedor') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                                <textarea wire:model="descripcion" placeholder="Descripción del insumo" rows="1" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors resize-none @error('descripcion') ring-2 ring-red-500 @enderror"></textarea>
                                @error('descripcion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-6 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700">
                            <i class="bi bi-info-circle mt-0.5 shrink-0"></i>
                            <span>Precio calculado automáticamente mediante sistema FIFO</span>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if ($insumo_id)
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            @canany(['crear-insumos', 'editar-insumos'])
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> {{ $insumo_id ? 'Actualizar' : 'Guardar' }}
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
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por nombre, descripción, proveedor, unidad o costo..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Nombre</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Descripción</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Unidad</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Proveedor</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Stock Actual</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Precio Promedio</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($insumos as $insumo)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $insumo->id_insumo }}">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $insumo->id_insumo }}</span></td>
                                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $insumo->nombre }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $insumo->descripcion ?? 'N/A' }}</td>
                                        <td class="px-3 py-3">
                                            @if($insumo->unidadMedida)
                                                <span class="text-slate-600">{{ $insumo->unidadMedida->nombre }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 ml-1">{{ $insumo->unidadMedida->abreviatura }}</span>
                                            @else
                                                <span class="text-slate-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">{{ $insumo->proveedor?->razon_social ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $insumo->stock > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ number_format($insumo->stock, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right text-slate-600">
                                            @if($insumo->precio_promedio > 0)
                                                ${{ number_format($insumo->precio_promedio, 2, ',', '.') }}
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex gap-1 justify-center">
                                                @can('editar-insumos')
                                                <button wire:click="editar({{ $insumo->id_insumo }})" @click="$set('tab_activo', 'nuevo')" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                @endcan
                                                @can('eliminar-insumos')
                                                <button wire:click="eliminar({{ $insumo->id_insumo }})" onclick="return confirm('¿Está seguro de eliminar este insumo?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
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
                                            <p class="text-slate-500 font-medium">No hay insumos registrados.</p>
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
        Livewire.on('insumoGuardado', () => {
            // Livewire actualizará automáticamente
        });
    });
</script>
