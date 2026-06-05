<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-people"></i> Clientes
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
        ['value' => 'nuevo', 'label' => 'Nuevo Cliente', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-clientes', 'editar-clientes'])],
        ['value' => 'listado', 'label' => 'Listado de Clientes', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    <div>
        @if($tab_activo === 'nuevo')
            @canany(['crear-clientes', 'editar-clientes'])
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                        <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                            <i class="bi bi-{{ $cliente_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                            {{ $cliente_id ? 'Editar Cliente' : 'Nuevo Cliente' }}
                        </h5>
                    </div>
                    <div class="p-6">
                        <form wire:submit.prevent="guardar">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Razón Social / Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="razon_social" placeholder="Nombre del cliente" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('razon_social') ring-2 ring-red-500 @enderror">
                                    @error('razon_social') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">CUIT <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="cuit" placeholder="XX-XXXXXXXX-X" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('cuit') ring-2 ring-red-500 @enderror">
                                    @error('cuit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Dirección</label>
                                    <input type="text" wire:model="direccion" placeholder="Dirección completa" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('direccion') ring-2 ring-red-500 @enderror">
                                    @error('direccion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Contacto</label>
                                    <input type="text" wire:model="contacto" placeholder="Teléfono / Email" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('contacto') ring-2 ring-red-500 @enderror">
                                    @error('contacto') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                @if ($cliente_id)
                                    <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                @endif
                                @canany(['crear-clientes', 'editar-clientes'])
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                    <i class="bi bi-check-circle"></i> {{ $cliente_id ? 'Actualizar' : 'Guardar' }}
                                </button>
                                @endcanany
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcanany
        @else
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="p-6">
                        <x-search-input placeholder="Buscar por razón social, CUIT o contacto..." />

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Razón Social / Nombre</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">CUIT</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Dirección</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Contacto</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse ($clientes as $cliente)
                                        <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $cliente->id_cliente }}">
                                            <td class="px-4 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $cliente->id_cliente }}</span></td>
                                            <td class="px-4 py-3"><span class="font-semibold text-slate-900">{{ $cliente->razon_social ?? $cliente->nombre }}</span></td>
                                            <td class="px-4 py-3 text-slate-600">{{ $cliente->cuit }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $cliente->direccion ?? '-' }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $cliente->contacto ?? '-' }}</td>
                                            <td class="px-4 py-3 text-center">
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
            </div>
        @endif
    </div>
</div>
