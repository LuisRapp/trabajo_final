<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.briefcase class="size-6" />
            Roles Laborales
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-roles-laborales', 'editar-roles-laborales'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Rol
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Roles
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-roles-laborales', 'editar-roles-laborales'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($rol_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Rol
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Rol
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-semibold text-tinta mb-1.5">Nombre del Rol <span class="text-tierra">*</span></label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                            placeholder="Ej: Operario, Supervisor, Encargado">
                        @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($rol_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-roles-laborales', 'editar-roles-laborales'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $rol_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @else
        <x-ui.card class="overflow-hidden">
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-corteza-suave">
                        <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                        <input type="text"
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                            placeholder="Buscar por nombre..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $rol)
                                <tr wire:key="row-{{ $rol->id_rol_laboral }}">
                                    <td><x-ui.badge variant="neutral">{{ $rol->id_rol_laboral }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $rol->nombre }}</td>
                                    <td>
                                        @if($rol->trashed())
                                            <x-ui.badge variant="neutral">Inactivo</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Activo</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-roles-laborales')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $rol->id_rol_laboral }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-roles-laborales')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $rol->id_rol_laboral }})"
                                                wire:confirm="¿Está seguro de eliminar este rol?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-12 text-tinta-suave">
                                        No hay roles registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
