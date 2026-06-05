<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-person-workspace"></i> Empleados
        </h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Empleado', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-empleados', 'editar-empleados'])],
        ['value' => 'listado', 'label' => 'Listado de Empleados', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-empleados', 'editar-empleados'])
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                    <i class="bi bi-{{ $empleado_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                    {{ $empleado_id ? 'Editar Empleado' : 'Nuevo Empleado' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">DNI @if(!$empleado_id)<span class="text-red-500">*</span>@endif</label>
                            <input type="text" wire:model="dni" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('dni') ring-2 ring-red-500 @enderror" placeholder="12345678" maxlength="8">
                            @error('dni') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            @if($empleado_id) <small class="text-slate-500 text-xs mt-1 block">Opcional - Dejar en blanco para mantener el actual</small> @endif
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="apellido" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('apellido') ring-2 ring-red-500 @enderror" placeholder="Apellido">
                            @error('apellido') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nombre" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('nombre') ring-2 ring-red-500 @enderror" placeholder="Nombre">
                            @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rol Laboral <span class="text-red-500">*</span></label>
                            <select wire:model="id_rol_laboral" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_rol_laboral') ring-2 ring-red-500 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_rol_laboral') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Nacimiento <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_nacimiento" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_nacimiento') ring-2 ring-red-500 @enderror">
                            @error('fecha_nacimiento') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_inicio_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_inicio_actividades') ring-2 ring-red-500 @enderror">
                            @error('fecha_inicio_actividades') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Fin</label>
                            <input type="date" wire:model="fecha_fin_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_fin_actividades') ring-2 ring-red-500 @enderror">
                            <small class="text-slate-500 text-xs mt-1 block">Opcional</small>
                            @error('fecha_fin_actividades') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($empleado_id)
                            <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium text-sm">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        @endif
                        @canany(['crear-empleados', 'editar-empleados'])
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                            <i class="bi bi-check-circle"></i> {{ $empleado_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @endif

    @if($tab_activo === 'listado')
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">Listado de Empleados</h5>
            </div>
            <div class="p-6">
                <x-search-input placeholder="Buscar por apellido, nombre, DNI o rol..." />

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">DNI</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Apellido y Nombre</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Rol</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Nacimiento</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Inicio</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($empleados as $empleado)
                                <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $empleado->id_empleado }}">
                                    <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $empleado->id_empleado }}</span></td>
                                    <td class="px-3 py-3 text-slate-600">{{ number_format($empleado->dni, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $empleado->apellido }}, {{ $empleado->nombre }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $empleado->rolLaboral?->nombre ?? 'N/A' }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $empleado->fecha_inicio_actividades ? \Carbon\Carbon::parse($empleado->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <x-action-buttons
                                            editWireClick="editar({{ $empleado->id_empleado }})"
                                            deleteWireClick="eliminar({{ $empleado->id_empleado }})"
                                            deleteMessage="¿Está seguro de eliminar este empleado?"
                                            :canEdit="auth()->user()->can('editar-empleados')"
                                            :canDelete="auth()->user()->can('eliminar-empleados')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay empleados registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($empleados->hasPages())
                    <div class="mt-6">
                        {{ $empleados->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
