<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.wrench class="size-6" />
            Tipos de Mantenimiento
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nuevo Tipo
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }} {{ !auth()->user()->canAny(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento']) ? 'rounded-l-sm' : '' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Tipos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])
        <x-ui.card class="mb-6 overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta">
                    @if($tipo_id)
                        <flux:icon.pencil-square class="size-5 inline" />
                        Editar Tipo
                    @else
                        <flux:icon.plus class="size-5 inline" />
                        Nuevo Tipo
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-semibold text-tinta mb-1.5">Nombre del Tipo <span class="text-tierra">*</span></label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                            placeholder="Ej: Preventivo, Correctivo, Predictivo">
                        @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($tipo_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])
                        <x-ui.button type="submit" icon="check">
                            {{ $tipo_id ? 'Actualizar' : 'Guardar' }}
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
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tipos as $tipo)
                                <tr wire:key="row-{{ $tipo->id_tipo_mantenimiento }}">
                                    <td><x-ui.badge variant="neutral">{{ $tipo->id_tipo_mantenimiento }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $tipo->nombre }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-tipos-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $tipo->id_tipo_mantenimiento }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-tipos-mantenimiento')
                                                <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $tipo->id_tipo_mantenimiento }})" wire:confirm="¿Esta seguro de dar de baja este tipo?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-tinta-suave">
                                        No hay tipos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $tipos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
