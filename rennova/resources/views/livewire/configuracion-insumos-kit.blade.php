<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header con información del Kit -->
    <div class="bg-slate-50 rounded-xl mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="mb-1 text-xl font-bold text-slate-900">🔧 Configurando Kit: <strong>{{ $kit->nombre_kit }}</strong></h4>
                    <p class="text-slate-500">
                        ⚙️ Tipo de Maquinaria:
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">{{ $kit->tipoMaquinaria->nombre }}</span>
                    </p>
                </div>
                <a href="{{ route('kits-preventivos.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                    ← Volver a Kits
                </a>
            </div>
        </div>
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

    <!-- Formulario para Añadir Insumo -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-brand text-white px-6 py-4">
            <h5 class="text-lg font-semibold">➕ Añadir Insumo al Kit</h5>
        </div>
        <div class="p-6">
            <form wire:submit.prevent="agregarInsumo">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Insumo <span class="text-red-500">*</span></label>
                        <select wire:model="insumo_id"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('insumo_id') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            <option value="">Seleccione un insumo</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                    {{ $insumo->nombre }} ({{ $insumo->unidad_medida }})
                                </option>
                            @endforeach
                        </select>
                        @error('insumo_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cantidad Necesaria <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" min="0" wire:model="cantidad_necesaria"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('cantidad_necesaria') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                            placeholder="0.00">
                        @error('cantidad_necesaria') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors w-full justify-center">
                            ➕ Agregar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Insumos del Kit -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-slate-800">📦 Insumos en el Kit</h5>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre del Insumo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Unidad de Medida</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad Necesaria</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($insumosKit as $insumo)
                            <tr wire:key="row-{{ $insumo['id_insumo'] }}" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $insumo['id_insumo'] }}</span></td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ $insumo['nombre'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $insumo['unidad_medida'] }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">
                                        {{ number_format($insumo['cantidad_necesaria'], 2) }} {{ $insumo['unidad_medida'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <button class="inline-flex items-center gap-1 px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-xs font-medium transition-colors"
                                        wire:click="quitarInsumo({{ $insumo['id_insumo'] }})"
                                        onclick="return confirm('¿Está seguro de quitar este insumo del kit?')"
                                        title="Quitar">
                                        🗑️ Quitar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <div class="text-4xl mb-2">📥</div>
                                    <p>No hay insumos en este kit. Agregue insumos usando el formulario superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
