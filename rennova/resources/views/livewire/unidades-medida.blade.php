<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta">Unidades de Medida</h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nueva Unidad', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-unidades-medida', 'editar-unidades-medida'])],
        ['value' => 'listado', 'label' => 'Listado de Unidades', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-unidades-medida', 'editar-unidades-medida'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $unidad_id ? 'Editar Unidad' : 'Nueva Unidad' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="nombre" class="block text-xs font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                                <input type="text" id="nombre" wire:model="nombre"
                                    class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Nombre de la unidad de medida">
                                @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="abreviatura" class="block text-xs font-semibold text-tinta mb-1.5">Abreviatura <span class="text-tierra">*</span></label>
                                <input type="text" id="abreviatura" wire:model="abreviatura"
                                    class="form-input @error('abreviatura') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Ej: kg, lt, m3">
                                @error('abreviatura') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            @if ($unidad_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-unidades-medida', 'editar-unidades-medida'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $unidad_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre o abreviatura..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Abreviatura</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unidades as $unidad)
                                <tr wire:key="row-{{ $unidad->id_unidad_medida }}">
                                    <td><x-ui.badge variant="neutral">{{ $unidad->id_unidad_medida }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $unidad->nombre }}</td>
                                    <td><x-ui.badge variant="info">{{ $unidad->abreviatura }}</x-ui.badge></td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-unidades-medida')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="editar({{ $unidad->id_unidad_medida }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-unidades-medida')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click="eliminar({{ $unidad->id_unidad_medida }})" wire:confirm="¿Esta seguro de eliminar esta unidad?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" message="No hay unidades registradas." icon="scale" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $unidades->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
