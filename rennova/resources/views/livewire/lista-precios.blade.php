<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🏷️ Lista de Precios</h1>
    </div>

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

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Precio', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-precios', 'editar-precios'])],
        ['value' => 'listado', 'label' => 'Listado de Precios', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-precios', 'editar-precios'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $precio_id ? '✏️ Editar Precio' : '➕ Nuevo Precio' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cliente <span class="text-red-500">*</span></label>
                            <select wire:model="cliente_id"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('cliente_id') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Categoría <span class="text-red-500">*</span></label>
                            <select wire:model="categoria_id"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('categoria_id') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Precio <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="precio" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('precio') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00">
                            @error('precio') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Desde <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_desde"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_desde') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_desde') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Hasta</label>
                            <input type="date" wire:model="fecha_hasta"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_hasta') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_hasta') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($precio_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-precios', 'editar-precios'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $precio_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <x-search-input placeholder="Buscar por cliente, categoría..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio/Ton</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Desde</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Hasta</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($precios as $precioItem)
                                <tr wire:key="row-{{ $precioItem->id }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $precioItem->id }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $precioItem->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $precioItem->categoriaMadera->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">${{ number_format($precioItem->precio, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $precioItem->fecha_desde ? \Carbon\Carbon::parse($precioItem->fecha_desde)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $precioItem->fecha_hasta ? \Carbon\Carbon::parse($precioItem->fecha_hasta)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <x-action-buttons
                                            editWireClick="editar({{ $precioItem->id }})"
                                            deleteWireClick="eliminar({{ $precioItem->id }})"
                                            deleteMessage="¿Está seguro de eliminar este precio? Esta acción no se puede deshacer."
                                            :canEdit="auth()->user()->can('editar-precios')"
                                            :canDelete="auth()->user()->can('eliminar-precios')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay precios registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $precios->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
