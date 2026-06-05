<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🚚 Proveedores</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Proveedor', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-proveedores', 'editar-proveedores'])],
        ['value' => 'listado', 'label' => 'Listado de Proveedores', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-proveedores', 'editar-proveedores'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $proveedor_id ? '✏️ Editar Proveedor' : '➕ Nuevo Proveedor' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="razon_social" class="block text-sm font-semibold text-slate-700 mb-1.5">Razón Social <span class="text-red-500">*</span></label>
                            <input type="text" id="razon_social" wire:model="razon_social"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('razon_social') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre del proveedor">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="direccion" class="block text-sm font-semibold text-slate-700 mb-1.5">Dirección</label>
                            <input type="text" id="direccion" wire:model="direccion"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Dirección completa">
                            @error('direccion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                            <input type="text" id="telefono" wire:model="telefono"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="+54 9 11 1234-5678">
                            @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                            <input type="email" id="email" wire:model="email"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="correo@ejemplo.com">
                            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($proveedor_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-proveedores', 'editar-proveedores'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $proveedor_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por razón social, CUIT o email..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Razón Social</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">CUIT</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dirección</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Teléfono</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($proveedores as $proveedor)
                                <tr wire:key="row-{{ $proveedor->id_proveedor }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $proveedor->id_proveedor }}</span></td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $proveedor->razon_social }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $proveedor->cuit }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $proveedor->direccion ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $proveedor->telefono ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $proveedor->email ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-right">
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
    @endif
</div>
