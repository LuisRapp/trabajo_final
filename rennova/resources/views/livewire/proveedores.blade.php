<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta">Proveedores</h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Proveedor', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-proveedores', 'editar-proveedores'])],
        ['value' => 'listado', 'label' => 'Listado de Proveedores', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-proveedores', 'editar-proveedores'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="razon_social" class="block text-xs font-semibold text-tinta mb-1.5">Razon Social <span class="text-tierra">*</span></label>
                                <input type="text" id="razon_social" wire:model="razon_social"
                                    class="form-input @error('razon_social') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Nombre del proveedor">
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="direccion" class="block text-xs font-semibold text-tinta mb-1.5">Direccion</label>
                                <input type="text" id="direccion" wire:model="direccion"
                                    class="form-input"
                                    placeholder="Direccion completa">
                                @error('direccion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="telefono" class="block text-xs font-semibold text-tinta mb-1.5">Telefono</label>
                                <input type="text" id="telefono" wire:model="telefono"
                                    class="form-input"
                                    placeholder="+54 9 11 1234-5678">
                                @error('telefono') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-semibold text-tinta mb-1.5">Email</label>
                                <input type="email" id="email" wire:model="email"
                                    class="form-input"
                                    placeholder="correo@ejemplo.com">
                                @error('email') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($proveedor_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-proveedores', 'editar-proveedores'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $proveedor_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por razon social, CUIT o email..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Razon Social</th>
                                <th>CUIT</th>
                                <th>Direccion</th>
                                <th>Telefono</th>
                                <th>Email</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proveedores as $proveedor)
                                <tr wire:key="row-{{ $proveedor->id_proveedor }}">
                                    <td><x-ui.badge variant="neutral">{{ $proveedor->id_proveedor }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $proveedor->razon_social }}</td>
                                    <td class="text-tinta-suave">{{ $proveedor->cuit }}</td>
                                    <td class="text-tinta-suave">{{ $proveedor->direccion ?? '-' }}</td>
                                    <td class="text-tinta-suave">{{ $proveedor->telefono ?? '-' }}</td>
                                    <td class="text-tinta-suave">{{ $proveedor->email ?? '-' }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-proveedores')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="editar({{ $proveedor->id_proveedor }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-proveedores')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click="eliminar({{ $proveedor->id_proveedor }})" wire:confirm="¿Esta seguro de eliminar este proveedor?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay proveedores registrados." icon="truck" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $proveedores->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
