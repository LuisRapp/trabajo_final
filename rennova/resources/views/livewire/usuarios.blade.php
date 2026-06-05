<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">👥 Usuarios</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Usuario', 'icon' => 'plus-circle', 'can' => auth()->user()->can('crear-usuarios')],
        ['value' => 'listado', 'label' => 'Listado de Usuarios', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @can('crear-usuarios')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $usuario_id ? '✏️ Editar Usuario' : '➕ Nuevo Usuario' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Nombre">
                            @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" id="apellido" wire:model="apellido"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('apellido') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Apellido">
                            @error('apellido') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" wire:model="email"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('email') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="correo@ejemplo.com">
                            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña @if(!$usuario_id)<span class="text-red-500">*</span>@endif</label>
                            <input type="password" id="password" wire:model="password"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('password') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Contraseña">
                            @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirmar Contraseña @if(!$usuario_id)<span class="text-red-500">*</span>@endif</label>
                            <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Confirmar contraseña">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                            <input type="text" id="telefono" wire:model="telefono"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                placeholder="Teléfono">
                            @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="activo" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                            <select id="activo" wire:model="activo"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('activo') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('activo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($usuario_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @can('crear-usuarios')
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $usuario_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
        @endcan
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <x-search-input placeholder="Buscar por nombre, apellido, email..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Apellido</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($usuarios as $usuario)
                                <tr wire:key="row-{{ $usuario->id }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $usuario->id }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $usuario->nombre }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $usuario->apellido }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $usuario->email }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $usuario->telefono ?? '-' }}</td>
                                    <td class="px-4 py-2.5">
                                        @if($usuario->trashed())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Inactivo</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Activo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $usuario->id }})"
                                            deleteWireClick="eliminar({{ $usuario->id }})"
                                            deleteMessage="¿Está seguro de eliminar este usuario?"
                                            :canEdit="auth()->user()->can('editar-usuarios')"
                                            :canDelete="auth()->user()->can('eliminar-usuarios')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay usuarios registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
