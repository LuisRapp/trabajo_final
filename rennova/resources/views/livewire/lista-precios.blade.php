<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-tinta">Lista de Precios</h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Precio', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-lista-precios', 'editar-lista-precios'])],
        ['value' => 'listado', 'label' => 'Listado de Precios', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-lista-precios', 'editar-lista-precios'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $precio_id ? 'Editar Precio' : 'Nuevo Precio' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Cliente <span class="text-tierra">*</span></label>
                                <select wire:model="cliente_id"
                                    class="form-input @error('cliente_id') border-tierra bg-tierra-suave @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('cliente_id') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Categoria <span class="text-tierra">*</span></label>
                                <select wire:model="categoria_id"
                                    class="form-input @error('categoria_id') border-tierra bg-tierra-suave @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('categoria_id') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Precio <span class="text-tierra">*</span></label>
                                <input type="number" wire:model="precio" step="0.01"
                                    class="form-input @error('precio') border-tierra bg-tierra-suave @enderror"
                                    placeholder="0.00">
                                @error('precio') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Fecha Desde <span class="text-tierra">*</span></label>
                                <input type="date" wire:model="fecha_desde"
                                    class="form-input @error('fecha_desde') border-tierra bg-tierra-suave @enderror">
                                @error('fecha_desde') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Fecha Hasta</label>
                                <input type="date" wire:model="fecha_hasta"
                                    class="form-input @error('fecha_hasta') border-tierra bg-tierra-suave @enderror">
                                @error('fecha_hasta') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($precio_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-lista-precios', 'editar-lista-precios'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $precio_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por cliente, categoria..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Categoria</th>
                                <th>Precio/Ton</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($precios as $precioItem)
                                <tr wire:key="row-{{ $precioItem->id }}">
                                    <td><x-ui.badge variant="neutral">{{ $precioItem->id }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $precioItem->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="text-tinta-suave">{{ $precioItem->categoriaMadera->nombre ?? 'N/A' }}</td>
                                    <td class="text-tinta-suave">${{ number_format($precioItem->precio, 2, ',', '.') }}</td>
                                    <td class="text-tinta-suave">{{ $precioItem->fecha_desde ? \Carbon\Carbon::parse($precioItem->fecha_desde)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-tinta-suave">{{ $precioItem->fecha_hasta ? \Carbon\Carbon::parse($precioItem->fecha_hasta)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-lista-precios')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="editar({{ $precioItem->id }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-lista-precios')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click="eliminar({{ $precioItem->id }})" wire:confirm="¿Esta seguro de eliminar este precio? Esta accion no se puede deshacer." title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay precios registrados." icon="currency-dollar" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $precios->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
