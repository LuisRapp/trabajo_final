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

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Tipo', 'icon' => 'plus', 'can' => auth()->user()?->canAny(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento']) ?? false],
        ['value' => 'listado', 'label' => 'Listado de Tipos', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

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
                <form autocomplete="off" wire:submit.prevent="guardar">
                    <div class="mb-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-tinta mb-1.5">Nombre del Tipo <span class="text-tierra">*</span></label>
                            <input autocomplete="off" type="text" id="nombre" wire:model="nombre"
                                class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                placeholder="Ej: Service 500 hs, Cambio de aceite">
                            @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="comportamiento" class="block text-sm font-semibold text-tinta mb-1.5">Comportamiento <span class="text-tierra">*</span></label>
                            <select id="comportamiento" wire:model.live="comportamiento"
                                class="form-input @error('comportamiento') border-tierra bg-tierra-suave @enderror">
                                @foreach (\App\Enums\ComportamientoMantenimiento::cases() as $opcion)
                                    <option value="{{ $opcion->value }}">{{ $opcion->label() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-tinta-suave">
                                {{ \App\Enums\ComportamientoMantenimiento::tryFrom((string) $comportamiento)?->descripcion() }}
                            </p>
                            @error('comportamiento') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
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
                        <input autocomplete="off" type="text" wire:model.live="busqueda" placeholder="Buscar por nombre..."
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder-arena-oscura">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Comportamiento</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tipos as $tipo)
                                <tr wire:key="row-{{ $tipo->id_tipo_mantenimiento }}">
                                    <td><x-ui.badge variant="neutral">{{ $tipo->id_tipo_mantenimiento }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $tipo->nombre }}</td>
                                    <td>
                                        <x-ui.badge variant="{{ $tipo->comportamiento?->variant() ?? 'neutral' }}">
                                            {{ $tipo->comportamiento?->label() ?? '—' }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-tipos-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $tipo->id_tipo_mantenimiento }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-tipos-mantenimiento')
                                                <x-ui.confirmador accion="eliminar" :parametros="[$tipo->id_tipo_mantenimiento]"
                                                    mensaje="¿Dar de baja el tipo de mantenimiento «{{ $tipo->nombre }}»?"
                                                    etiqueta="Dar de baja"
                                                />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-tinta-suave">
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
