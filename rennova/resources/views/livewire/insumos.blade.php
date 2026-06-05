<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">📦 Insumos</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Insumo', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-insumos', 'editar-insumos'])],
        ['value' => 'listado', 'label' => 'Listado de Insumos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-insumos', 'editar-insumos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $insumo_id ? '✏️ Editar Insumo' : '➕ Nuevo Insumo' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                            placeholder="Nombre del insumo">
                        @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_unidad_medida" class="block text-sm font-semibold text-slate-700 mb-1.5">Unidad de Medida <span class="text-red-500">*</span></label>
                            <select id="id_unidad_medida" wire:model="id_unidad_medida"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_unidad_medida') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->id_unidad_medida }}" wire:key="option-{{ $unidad->id_unidad_medida }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                @endforeach
                            </select>
                            @error('id_unidad_medida') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_proveedor" class="block text-sm font-semibold text-slate-700 mb-1.5">Proveedor Principal <span class="text-red-500">*</span></label>
                            <select id="id_proveedor" wire:model="id_proveedor"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_proveedor') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('id_proveedor') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="descripcion" class="block text-sm font-semibold text-slate-700 mb-1.5">Descripción</label>
                            <textarea id="descripcion" wire:model="descripcion" rows="1"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors resize-none @error('descripcion') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Descripción del insumo"></textarea>
                            @error('descripcion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700">
                        <span class="mt-0.5 shrink-0">ℹ️</span>
                        <span>Precio calculado automáticamente mediante sistema FIFO</span>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($insumo_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-insumos', 'editar-insumos'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $insumo_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre, descripción, proveedor, unidad o costo..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Unidad</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proveedor</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock Actual</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Promedio</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($insumos as $insumo)
                                <tr wire:key="row-{{ $insumo->id_insumo }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $insumo->id_insumo }}</span></td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $insumo->nombre }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $insumo->descripcion ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5">
                                        @if($insumo->unidadMedida)
                                            <span class="text-slate-600">{{ $insumo->unidadMedida->nombre }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 ml-1">{{ $insumo->unidadMedida->abreviatura }}</span>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $insumo->proveedor?->razon_social ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $insumo->stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ number_format($insumo->stock, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-slate-600">
                                        @if($insumo->precio_promedio > 0)
                                            ${{ number_format($insumo->precio_promedio, 2, ',', '.') }}
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $insumo->id_insumo }})"
                                            deleteWireClick="eliminar({{ $insumo->id_insumo }})"
                                            deleteMessage="¿Está seguro de eliminar este insumo?"
                                            :canEdit="auth()->user()->can('editar-insumos')"
                                            :canDelete="auth()->user()->can('eliminar-insumos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="8" message="No hay insumos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
