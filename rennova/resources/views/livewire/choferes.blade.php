<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">👤 Choferes</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Chofer', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-choferes', 'editar-choferes'])],
        ['value' => 'listado', 'label' => 'Listado de Choferes', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-choferes', 'editar-choferes'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $chofer_id ? '✏️ Editar Chofer' : '➕ Nuevo Chofer' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_cliente" class="block text-sm font-semibold text-slate-700 mb-1.5">Cliente <span class="text-red-500">*</span></label>
                            <select id="id_cliente" wire:model="id_cliente"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_cliente') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('id_cliente') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" id="apellido" wire:model="apellido"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('apellido') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Apellido">
                            @error('apellido') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre">
                            @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="dni" class="block text-sm font-semibold text-slate-700 mb-1.5">DNI <span class="text-red-500">*</span></label>
                            <input type="text" id="dni" wire:model="dni"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('dni') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="12345678">
                            @error('dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                            <input type="text" id="telefono" wire:model="telefono"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Teléfono">
                            @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="direccion" class="block text-sm font-semibold text-slate-700 mb-1.5">Dirección</label>
                            <input type="text" id="direccion" wire:model="direccion"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Dirección">
                            @error('direccion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($chofer_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-choferes', 'editar-choferes'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $chofer_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por apellido, nombre, DNI, cliente..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Apellido y Nombre</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">DNI</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Teléfono</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($choferes as $c)
                                <tr wire:key="row-{{ $c->id_chofer }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $c->id_chofer }}</span></td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $c->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $c->apellido }}, {{ $c->nombre }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $c->dni }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $c->telefono ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $c->id_chofer }})"
                                            deleteWireClick="eliminar({{ $c->id_chofer }})"
                                            deleteMessage="¿Está seguro de eliminar este chofer?"
                                            :canEdit="auth()->user()->can('editar-choferes')"
                                            :canDelete="auth()->user()->can('eliminar-choferes')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay choferes registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $choferes->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
