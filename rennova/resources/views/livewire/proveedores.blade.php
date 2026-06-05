<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-truck"></i> Proveedores
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
        ['value' => 'nuevo', 'label' => 'Nuevo Proveedor', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-proveedores', 'editar-proveedores'])],
        ['value' => 'listado', 'label' => 'Listado de Proveedores', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    <div>
        @if($tab_activo === 'nuevo')
            @canany(['crear-proveedores', 'editar-proveedores'])
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                        <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                            <i class="bi bi-{{ $proveedor_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                            {{ $proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                        </h5>
                    </div>
                    <div class="p-6">
                        <form wire:submit.prevent="guardar">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Razón Social <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="razon_social" placeholder="Nombre del proveedor" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('razon_social') ring-2 ring-red-500 @enderror">
                                    @error('razon_social') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">CUIT <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="cuit" placeholder="XX-XXXXXXXX-X" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('cuit') ring-2 ring-red-500 @enderror">
                                    @error('cuit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Dirección</label>
                                    <input type="text" wire:model="direccion" placeholder="Dirección completa" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('direccion') ring-2 ring-red-500 @enderror">
                                    @error('direccion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                                    <input type="text" wire:model="telefono" placeholder="+54 9 11 1234-5678" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('telefono') ring-2 ring-red-500 @enderror">
                                    @error('telefono') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                                    <input type="email" wire:model="email" placeholder="correo@ejemplo.com" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('email') ring-2 ring-red-500 @enderror">
                                    @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                @if ($proveedor_id)
                                    <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                @endif
                                @canany(['crear-proveedores', 'editar-proveedores'])
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                    <i class="bi bi-check-circle"></i> {{ $proveedor_id ? 'Actualizar' : 'Guardar' }}
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
                        <x-search-input placeholder="Buscar por razón social, CUIT o email..." />

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Razón Social</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">CUIT</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Dirección</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Teléfono</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Email</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse ($proveedores as $proveedor)
                                        <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $proveedor->id_proveedor }}">
                                            <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $proveedor->id_proveedor }}</span></td>
                                            <td class="px-3 py-3"><span class="font-semibold text-slate-900">{{ $proveedor->razon_social }}</span></td>
                                            <td class="px-3 py-3 text-slate-600">{{ $proveedor->cuit }}</td>
                                            <td class="px-3 py-3 text-slate-600">{{ $proveedor->direccion ?? '-' }}</td>
                                            <td class="px-3 py-3 text-slate-600">{{ $proveedor->telefono ?? '-' }}</td>
                                            <td class="px-3 py-3 text-slate-600">{{ $proveedor->email ?? '-' }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <x-action-buttons
                                                    editWireClick="editar({{ $proveedor->id_proveedor }})"
                                                    deleteWireClick="eliminar({{ $proveedor->id_proveedor }})"
                                                    deleteMessage="¿Está seguro de eliminar este proveedor?"
                                                    :canEdit="auth()->user()->can('editar-proveedores')"
                                                    :canDelete="auth()->user()->can('eliminar-proveedores')" />
                                            </td>
                                        </tr>
                                    @empty
                                        <x-empty-state :colspan="7" message="No hay proveedores registrados." />
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
