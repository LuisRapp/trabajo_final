<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.identification class="size-6" />
            Choferes
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-choferes', 'editar-choferes'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Chofer
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Choferes
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-choferes', 'editar-choferes'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($chofer_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Chofer
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Chofer
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_cliente" class="block text-sm font-semibold text-tinta mb-1.5">Cliente <span class="text-tierra">*</span></label>
                            <select id="id_cliente" wire:model="id_cliente"
                                class="form-input @error('id_cliente') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}" wire:key="option-{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('id_cliente') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-semibold text-tinta mb-1.5">Apellido <span class="text-tierra">*</span></label>
                            <input type="text" id="apellido" wire:model="apellido"
                                class="form-input @error('apellido') border-tierra bg-tierra-suave @enderror"
                                placeholder="Apellido">
                            @error('apellido') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                placeholder="Nombre">
                            @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="dni" class="block text-sm font-semibold text-tinta mb-1.5">DNI <span class="text-tierra">*</span></label>
                            <input type="text" id="dni" wire:model="dni"
                                class="form-input @error('dni') border-tierra bg-tierra-suave @enderror"
                                placeholder="12345678">
                            @error('dni') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-semibold text-tinta mb-1.5">Teléfono</label>
                            <input type="text" id="telefono" wire:model="telefono"
                                class="form-input"
                                placeholder="Teléfono">
                            @error('telefono') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="direccion" class="block text-sm font-semibold text-tinta mb-1.5">Dirección</label>
                            <input type="text" id="direccion" wire:model="direccion"
                                class="form-input"
                                placeholder="Dirección">
                            @error('direccion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($chofer_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-choferes', 'editar-choferes'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $chofer_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card class="overflow-hidden">
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-corteza-suave">
                        <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                        <input type="text"
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                            placeholder="Buscar por apellido, nombre, DNI, cliente..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Apellido y Nombre</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($choferes as $c)
                                <tr wire:key="row-{{ $c->id_chofer }}">
                                    <td><x-ui.badge variant="neutral">{{ $c->id_chofer }}</x-ui.badge></td>
                                    <td class="text-tinta-suave">{{ $c->cliente->razon_social ?? 'N/A' }}</td>
                                    <td class="font-medium text-tinta">{{ $c->apellido }}, {{ $c->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $c->dni }}</td>
                                    <td class="text-tinta-suave">{{ $c->telefono ?? '-' }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-choferes')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $c->id_chofer }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-choferes')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $c->id_chofer }})"
                                                wire:confirm="¿Está seguro de eliminar este chofer?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-tinta-suave">
                                        No hay choferes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $choferes->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
