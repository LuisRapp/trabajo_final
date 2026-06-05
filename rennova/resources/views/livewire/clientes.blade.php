<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">👥 Clientes</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Cliente', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-clientes', 'editar-clientes'])],
        ['value' => 'listado', 'label' => 'Listado de Clientes', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-clientes', 'editar-clientes'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $cliente_id ? '✏️ Editar Cliente' : '➕ Nuevo Cliente' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="razon_social" class="block text-sm font-semibold text-slate-700 mb-1.5">Razón Social / Nombre <span class="text-red-500">*</span></label>
                            <input type="text" id="razon_social" wire:model="razon_social"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('razon_social') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre del cliente">
                            @error('razon_social') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="cuit" class="block text-sm font-semibold text-slate-700 mb-1.5">CUIT <span class="text-red-500">*</span></label>
                            <input type="text" id="cuit" wire:model="cuit"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('cuit') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="XX-XXXXXXXX-X">
                            @error('cuit') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="direccion" class="block text-sm font-semibold text-slate-700 mb-1.5">Dirección</label>
                            <input type="text" id="direccion" wire:model="direccion"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Dirección completa">
                            @error('direccion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contacto" class="block text-sm font-semibold text-slate-700 mb-1.5">Contacto</label>
                            <input type="text" id="contacto" wire:model="contacto"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Teléfono / Email">
                            @error('contacto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($cliente_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-clientes', 'editar-clientes'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $cliente_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por razón social, CUIT o contacto..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Razón Social</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">CUIT</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dirección</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contacto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($clientes as $cliente)
                                <tr wire:key="row-{{ $cliente->id_cliente }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $cliente->id_cliente }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $cliente->razon_social ?? $cliente->nombre }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $cliente->cuit }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $cliente->direccion ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $cliente->contacto ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $cliente->id_cliente }})"
                                            deleteWireClick="eliminar({{ $cliente->id_cliente }})"
                                            deleteMessage="¿Está seguro de eliminar este cliente?"
                                            :canEdit="auth()->user()->can('editar-clientes')"
                                            :canDelete="auth()->user()->can('eliminar-clientes')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay clientes registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
