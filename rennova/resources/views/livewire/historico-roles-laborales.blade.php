<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.clock class="size-6" />
            Histórico de Roles Laborales
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-historico-roles-laborales', 'editar-historico-roles-laborales'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Histórico
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Históricos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-roles-laborales', 'editar-historico-roles-laborales'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($historico_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Histórico
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Histórico
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="rol_laboral_id" class="block text-sm font-semibold text-tinta mb-1.5">Rol Laboral <span class="text-tierra">*</span></label>
                            <select id="rol_laboral_id" wire:model="rol_laboral_id"
                                class="form-input @error('rol_laboral_id') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($rolesLaborales as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('rol_laboral_id') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="precio_tonelada" class="block text-sm font-semibold text-tinta mb-1.5">Precio/Ton <span class="text-tierra">*</span></label>
                            <input type="number" id="precio_tonelada" wire:model="precio_tonelada" step="0.01"
                                class="form-input @error('precio_tonelada') border-tierra bg-tierra-suave @enderror"
                                placeholder="0.00">
                            @error('precio_tonelada') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="jornal_diario" class="block text-sm font-semibold text-tinta mb-1.5">Jornal Diario <span class="text-tierra">*</span></label>
                            <input type="number" id="jornal_diario" wire:model="jornal_diario" step="0.01"
                                class="form-input @error('jornal_diario') border-tierra bg-tierra-suave @enderror"
                                placeholder="0.00">
                            @error('jornal_diario') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Inicio <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_inicio" wire:model="fecha_inicio"
                                class="form-input @error('fecha_inicio') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_inicio') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Fin</label>
                            <input type="date" id="fecha_fin" wire:model="fecha_fin"
                                class="form-input @error('fecha_fin') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_fin') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="motivo_cambio" class="block text-sm font-semibold text-tinta mb-1.5">Motivo del Cambio</label>
                            <input type="text" id="motivo_cambio" wire:model="motivo_cambio"
                                class="form-input @error('motivo_cambio') border-tierra bg-tierra-suave @enderror"
                                placeholder="Motivo">
                            @error('motivo_cambio') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($historico_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-historico-roles-laborales', 'editar-historico-roles-laborales'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $historico_id ? 'Actualizar' : 'Guardar' }}
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
                            placeholder="Buscar por rol..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Rol</th>
                                <th class="text-right">Precio/Ton</th>
                                <th class="text-right">Jornal</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id }}">
                                    <td><x-ui.badge variant="neutral">{{ $historico->id }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $historico->rolLaboral->nombre ?? 'N/A' }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($historico->precio_tonelada, 2, ',', '.') }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($historico->jornal_diario, 2, ',', '.') }}</td>
                                    <td class="text-tinta-suave">{{ $historico->fecha_inicio ? \Carbon\Carbon::parse($historico->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-tinta-suave">{{ $historico->fecha_fin ? \Carbon\Carbon::parse($historico->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-historico-roles-laborales')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $historico->id }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-historico-roles-laborales')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $historico->id }})"
                                                wire:confirm="¿Está seguro de eliminar este histórico?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-tinta-suave">
                                        No hay históricos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $historicos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
