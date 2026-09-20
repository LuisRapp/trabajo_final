<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.banknotes class="size-6" />
            Adelantos
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-adelantos', 'editar-adelantos'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Adelanto
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Adelantos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-adelantos', 'editar-adelantos'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($adelanto_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Adelanto
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Adelanto
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_empleado" class="block text-sm font-semibold text-tinta mb-1.5">Empleado <span class="text-tierra">*</span></label>
                            <select id="id_empleado" wire:model="id_empleado"
                                class="form-input @error('id_empleado') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach(($empleados ?? []) as $empleado)
                                    <option value="{{ $empleado->id_empleado }}" wire:key="option-{{ $empleado->id_empleado }}">{{ $empleado->apellido }}, {{ $empleado->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_empleado') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto" class="block text-sm font-semibold text-tinta mb-1.5">Monto <span class="text-tierra">*</span></label>
                            <div class="flex items-center gap-0">
                                <span class="px-3 py-2 bg-corteza-suave border border-r-0 border-arena rounded-l-sm text-tinta-suave font-semibold text-sm">$</span>
                                <input type="number" id="monto" wire:model="monto" step="0.1" min="0"
                                    class="form-input rounded-l-none @error('monto') border-tierra bg-tierra-suave @enderror"
                                    placeholder="0.00">
                            </div>
                            @error('monto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_emision" class="block text-sm font-semibold text-tinta mb-1.5">Fecha de Adelanto <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_emision" wire:model="fecha_emision"
                                class="form-input @error('fecha_emision') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_emision') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($adelanto_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-adelantos', 'editar-adelantos'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $adelanto_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @endif

    @if($tab_activo === 'listado')
        <x-ui.card class="overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta">Listado de Adelantos</h5>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-corteza-suave">
                        <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                        <input type="text"
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                            placeholder="Buscar por empleado, monto o fecha..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Monto</th>
                                <th>Fecha Adelanto</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($adelantos ?? []) as $adelanto)
                                <tr wire:key="row-{{ $adelanto->id_adelanto }}">
                                    <td><x-ui.badge variant="neutral">{{ $adelanto->id_adelanto }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $adelanto->empleado?->apellido ?? 'N/A' }}, {{ $adelanto->empleado?->nombre ?? '' }}</td>
                                    <td class="text-tinta-suave">${{ number_format($adelanto->monto, 2, ',', '.') }}</td>
                                    <td class="text-tinta-suave">{{ $adelanto->fecha_emision ? \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if($adelanto->activo)
                                            <x-ui.badge variant="success">Activo</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">Inactivo</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-adelantos')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $adelanto->id_adelanto }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-adelantos')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $adelanto->id_adelanto }})"
                                                wire:confirm="¿Está seguro de eliminar este adelanto?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-tinta-suave">
                                        No hay adelantos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $adelantos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
