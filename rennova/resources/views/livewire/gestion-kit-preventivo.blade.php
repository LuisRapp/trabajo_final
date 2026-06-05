<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🔧 Kits de Mantenimiento Preventivo</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Kit', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-kits-preventivos', 'editar-kits-preventivos'])],
        ['value' => 'listado', 'label' => 'Listado de Kits', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-kits-preventivos', 'editar-kits-preventivos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $kit_id ? '✏️ Editar Kit' : '➕ Nuevo Kit' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre del Kit <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nombre"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre del kit">
                            @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Descripción</label>
                            <textarea wire:model="descripcion"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('descripcion') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Descripción del kit" rows="1"></textarea>
                            @error('descripcion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($kit_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-kits-preventivos', 'editar-kits-preventivos'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $kit_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($kits as $kit)
                                <tr wire:key="row-{{ $kit->id_kit_preventivo }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $kit->id_kit_preventivo }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $kit->nombre }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $kit->descripcion ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <x-action-buttons
                                            editWireClick="editar({{ $kit->id_kit_preventivo }})"
                                            deleteWireClick="eliminar({{ $kit->id_kit_preventivo }})"
                                            deleteMessage="¿Está seguro de eliminar este kit?"
                                            :canEdit="auth()->user()->can('editar-kits-preventivos')"
                                            :canDelete="auth()->user()->can('eliminar-kits-preventivos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay kits registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
