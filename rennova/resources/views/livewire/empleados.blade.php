<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">👷 Empleados</h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Empleado', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-empleados', 'editar-empleados'])],
        ['value' => 'listado', 'label' => 'Listado de Empleados', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-empleados', 'editar-empleados'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $empleado_id ? '✏️ Editar Empleado' : '➕ Nuevo Empleado' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="dni" class="block text-sm font-semibold text-slate-700 mb-1.5">DNI @if(!$empleado_id)<span class="text-red-500">*</span>@endif</label>
                            <input type="text" id="dni" wire:model="dni"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('dni') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="12345678" maxlength="8">
                            @error('dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            @if($empleado_id) <small class="text-slate-500 text-xs mt-1 block">Opcional - Dejar en blanco para mantener el actual</small> @endif
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" id="apellido" wire:model="apellido"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('apellido') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Apellido">
                            @error('apellido') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre">
                            @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label for="id_rol_laboral" class="block text-sm font-semibold text-slate-700 mb-1.5">Rol Laboral <span class="text-red-500">*</span></label>
                            <select id="id_rol_laboral" wire:model="id_rol_laboral"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_rol_laboral') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_rol_laboral') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_nacimiento" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Nacimiento <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_nacimiento" wire:model="fecha_nacimiento"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_nacimiento') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_nacimiento') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_inicio_actividades" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Inicio <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_inicio_actividades" wire:model="fecha_inicio_actividades"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_inicio_actividades') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_inicio_actividades') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin_actividades" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Fin</label>
                            <input type="date" id="fecha_fin_actividades" wire:model="fecha_fin_actividades"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_fin_actividades') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            <small class="text-slate-500 text-xs mt-1 block">Opcional</small>
                            @error('fecha_fin_actividades') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($empleado_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-empleados', 'editar-empleados'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $empleado_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @endif

    @if($tab_activo === 'listado')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">Listado de Empleados</h5>
            </div>
            <div class="p-6">
                <x-search-input placeholder="Buscar por apellido, nombre, DNI o rol..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">DNI</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Apellido y Nombre</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Nacimiento</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Inicio</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($empleados as $empleado)
                                <tr wire:key="row-{{ $empleado->id_empleado }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $empleado->id_empleado }}</span></td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ number_format($empleado->dni, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $empleado->apellido }}, {{ $empleado->nombre }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $empleado->rolLaboral?->nombre ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $empleado->fecha_inicio_actividades ? \Carbon\Carbon::parse($empleado->fecha_inicio_actividades)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-3 py-2.5 text-right">
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
