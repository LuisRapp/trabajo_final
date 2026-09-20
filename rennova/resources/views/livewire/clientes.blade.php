<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.users class="size-6" />
            Clientes
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Cliente', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-clientes', 'editar-clientes'])],
        ['value' => 'listado', 'label' => 'Listado de Clientes', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-clientes', 'editar-clientes'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $cliente_id ? 'Editar Cliente' : 'Nuevo Cliente' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="razon_social" class="block text-xs font-semibold text-tinta mb-1.5">Razon Social / Nombre <span class="text-tierra">*</span></label>
                                <input type="text" id="razon_social" wire:model="razon_social"
                                    class="form-input @error('razon_social') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Nombre del cliente">
                                @error('razon_social') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="cuit" class="block text-xs font-semibold text-tinta mb-1.5">CUIT <span class="text-tierra">*</span></label>
                                <input type="text" id="cuit" wire:model="cuit"
                                    class="form-input @error('cuit') border-tierra bg-tierra-suave @enderror"
                                    placeholder="XX-XXXXXXXX-X">
                                @error('cuit') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="direccion" class="block text-xs font-semibold text-tinta mb-1.5">Direccion</label>
                                <input type="text" id="direccion" wire:model="direccion"
                                    class="form-input"
                                    placeholder="Direccion completa">
                                @error('direccion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="contacto" class="block text-xs font-semibold text-tinta mb-1.5">Contacto</label>
                                <input type="text" id="contacto" wire:model="contacto"
                                    class="form-input"
                                    placeholder="Telefono / Email">
                                @error('contacto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if ($cliente_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-clientes', 'editar-clientes'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $cliente_id ? 'Actualizar' : 'Guardar' }}
                                </x-ui.button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @endcanany
    @else
        <x-ui.card>
            <div class="p-4">
                <x-search-input placeholder="Buscar por razon social, CUIT o contacto..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Razon Social</th>
                                <th>CUIT</th>
                                <th>Direccion</th>
                                <th>Contacto</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr wire:key="row-{{ $cliente->id_cliente }}">
                                    <td><x-ui.badge variant="neutral">{{ $cliente->id_cliente }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $cliente->razon_social ?? $cliente->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $cliente->cuit }}</td>
                                    <td class="text-tinta-suave">{{ $cliente->direccion ?? '-' }}</td>
                                    <td class="text-tinta-suave">{{ $cliente->contacto ?? '-' }}</td>
                                    <td class="text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $cliente->id_cliente }})"
                                            deleteWireClick="eliminar({{ $cliente->id_cliente }})"
                                            deleteMessage="¿Esta seguro de eliminar este cliente?"
                                            :canEdit="auth()->user()->can('editar-clientes')"
                                            :canDelete="auth()->user()->can('eliminar-clientes')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay clientes registrados." icon="user" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $clientes->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
