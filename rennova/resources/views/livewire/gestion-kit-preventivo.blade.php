<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.wrench-screwdriver class="size-6" />
            Kits de Mantenimiento Preventivo
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nuevo Kit
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }} {{ !auth()->user()->canAny(['crear-kits-mantenimiento', 'editar-kits-mantenimiento']) ? 'rounded-l-sm' : '' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Kits
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
        <x-ui.card class="mb-6 overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta">
                    @if($kit_id)
                        <flux:icon.pencil-square class="size-5 inline" />
                        Editar Kit
                    @else
                        <flux:icon.plus class="size-5 inline" />
                        Nuevo Kit
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Nombre del Kit <span class="text-tierra">*</span></label>
                            <input type="text" wire:model="nombre"
                                class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                placeholder="Nombre del kit">
                            @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Descripcion</label>
                            <textarea wire:model="descripcion"
                                class="form-input @error('descripcion') border-tierra bg-tierra-suave @enderror"
                                placeholder="Descripcion del kit" rows="1"></textarea>
                            @error('descripcion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($kit_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
                        <x-ui.button type="submit" icon="check">
                            {{ $kit_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @else
        <x-ui.card>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-2.5 border border-arena rounded-sm bg-hueso">
                        <flux:icon.magnifying-glass class="size-4 text-arena-oscura" />
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por nombre..."
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder-arena-oscura">
                    </div>
                </div>

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
                            @forelse ($kits as $kit)
                                <tr wire:key="row-{{ $kit->id_kit_preventivo }}">
                                    <td><x-ui.badge variant="neutral">{{ $kit->id_kit_preventivo }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $kit->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $kit->descripcion ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="flex gap-1 justify-center">
                                            @can('editar-kits-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $kit->id_kit_preventivo }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-kits-mantenimiento')
                                                <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $kit->id_kit_preventivo }})" wire:confirm="¿Esta seguro de eliminar este kit?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-tinta-suave">
                                        No hay kits registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $kits->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
