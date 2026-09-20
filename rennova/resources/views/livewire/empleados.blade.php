<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.user class="size-6" />
            Empleados
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-empleados', 'editar-empleados'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Empleado
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Empleados
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-empleados', 'editar-empleados'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($empleado_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Empleado
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Empleado
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="dni" class="block text-sm font-semibold text-tinta mb-1.5">DNI @if(!$empleado_id)<span class="text-tierra">*</span>@endif</label>
                            <input type="text" id="dni" wire:model="dni"
                                class="form-input @error('dni') border-tierra bg-tierra-suave @enderror"
                                placeholder="12345678" maxlength="8">
                            @error('dni') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            @if($empleado_id) <small class="text-tinta-suave text-xs mt-1 block">Opcional - Dejar en blanco para mantener el actual</small> @endif
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

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label for="id_rol_laboral" class="block text-sm font-semibold text-tinta mb-1.5">Rol Laboral <span class="text-tierra">*</span></label>
                            <select id="id_rol_laboral" wire:model="id_rol_laboral"
                                class="form-input @error('id_rol_laboral') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_rol_laboral') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_nacimiento" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Nacimiento <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_nacimiento" wire:model="fecha_nacimiento"
                                class="form-input @error('fecha_nacimiento') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_nacimiento') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_inicio_actividades" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Inicio <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_inicio_actividades" wire:model="fecha_inicio_actividades"
                                class="form-input @error('fecha_inicio_actividades') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_inicio_actividades') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin_actividades" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Fin</label>
                            <input type="date" id="fecha_fin_actividades" wire:model="fecha_fin_actividades"
                                class="form-input @error('fecha_fin_actividades') border-tierra bg-tierra-suave @enderror">
                            <small class="text-tinta-suave text-xs mt-1 block">Opcional</small>
                            @error('fecha_fin_actividades') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($empleado_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-empleados', 'editar-empleados'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $empleado_id ? 'Actualizar' : 'Guardar' }}
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
                <h5 class="text-lg font-semibold text-tinta">Listado de Empleados</h5>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-corteza-suave">
                        <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                        <input type="text"
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                            placeholder="Buscar por apellido, nombre, DNI o rol..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>Apellido y Nombre</th>
                                <th>Rol</th>
                                <th>Fecha Nacimiento</th>
                                <th>Fecha Inicio</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($empleados as $empleado)
                                <tr wire:key="row-{{ $empleado->id_empleado }}">
                                    <td><x-ui.badge variant="neutral">{{ $empleado->id_empleado }}</x-ui.badge></td>
                                    <td class="text-tinta-suave">{{ number_format($empleado->dni, 0, ',', '.') }}</td>
                                    <td class="font-medium text-tinta">{{ $empleado->apellido }}, {{ $empleado->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $empleado->rolLaboral?->nombre ?? 'N/A' }}</td>
                                    <td class="text-tinta-suave">{{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-tinta-suave">{{ $empleado->fecha_inicio_actividades ? \Carbon\Carbon::parse($empleado->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-empleados')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $empleado->id_empleado }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-empleados')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $empleado->id_empleado }})"
                                                wire:confirm="¿Está seguro de eliminar este empleado?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-tinta-suave">
                                        No hay empleados registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $empleados->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
