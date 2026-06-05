<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-person-badge"></i> Choferes
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
        ['value' => 'nuevo', 'label' => 'Nuevo Chofer', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-choferes', 'editar-choferes'])],
        ['value' => 'listado', 'label' => 'Listado de Choferes', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-choferes', 'editar-choferes'])
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-{{ $chofer_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $chofer_id ? 'Editar Chofer' : 'Nuevo Chofer' }}
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Cliente <span class="text-red-500">*</span></label>
                                <select wire:model="id_cliente" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_cliente') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('id_cliente') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Apellido <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="apellido" placeholder="Apellido" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('apellido') ring-2 ring-red-500 @enderror">
                                @error('apellido') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nombre" placeholder="Nombre" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('nombre') ring-2 ring-red-500 @enderror">
                                @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">DNI <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="dni" placeholder="12345678" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('dni') ring-2 ring-red-500 @enderror">
                                @error('dni') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                                <input type="text" wire:model="telefono" placeholder="Teléfono" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('telefono') ring-2 ring-red-500 @enderror">
                                @error('telefono') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Dirección</label>
                                <input type="text" wire:model="direccion" placeholder="Dirección" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('direccion') ring-2 ring-red-500 @enderror">
                                @error('direccion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($chofer_id)
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            @canany(['crear-choferes', 'editar-choferes'])
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> {{ $chofer_id ? 'Actualizar' : 'Guardar' }}
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
                    <x-search-input placeholder="Buscar por apellido, nombre, DNI, cliente..." />

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Cliente</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Apellido y Nombre</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">DNI</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Teléfono</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($choferes as $c)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $c->id_chofer }}">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $c->id_chofer }}</span></td>
                                        <td class="px-3 py-3 text-slate-600">{{ $c->cliente->razon_social ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $c->apellido }}, {{ $c->nombre }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $c->dni }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $c->telefono ?? '-' }}</td>
                                        <td class="px-3 py-3 text-center">
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
                </div>
            </div>
        </div>
    @endif
</div>
