<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta">Categorias de Madera</h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Categoria', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-categorias-madera', 'editar-categorias-madera'])],
        ['value' => 'listado', 'label' => 'Listado de Categorias', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-categorias-madera', 'editar-categorias-madera'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $categoria_id ? 'Editar Categoria' : 'Nueva Categoria' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="nombre" class="block text-xs font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                                <input type="text" id="nombre" wire:model="nombre"
                                    class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Nombre de la categoria">
                                @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="descripcion" class="block text-xs font-semibold text-tinta mb-1.5">Descripcion</label>
                                <textarea id="descripcion" wire:model="descripcion" rows="1"
                                    class="form-input @error('descripcion') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Descripcion de la categoria"></textarea>
                                @error('descripcion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($categoria_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-categorias-madera', 'editar-categorias-madera'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $categoria_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre o descripcion..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripcion</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categorias as $categoria)
                                <tr wire:key="row-{{ $categoria->id_categoria_madera }}">
                                    <td><x-ui.badge variant="neutral">{{ $categoria->id_categoria_madera }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $categoria->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $categoria->descripcion ?? '-' }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-categorias-madera')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="editar({{ $categoria->id_categoria_madera }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-categorias-madera')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click="eliminar({{ $categoria->id_categoria_madera }})" wire:confirm="¿Esta seguro de eliminar esta categoria?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay categorias registradas." icon="tag" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $categorias->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
