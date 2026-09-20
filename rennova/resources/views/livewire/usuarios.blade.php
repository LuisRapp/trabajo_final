<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.users class="size-6" />
            Usuarios
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Usuario', 'icon' => 'plus', 'can' => auth()->user()->can('crear-usuarios')],
        ['value' => 'listado', 'label' => 'Listado de Usuarios', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @can('crear-usuarios')
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $usuario_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="nombre" class="block text-xs font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                                <input type="text" id="nombre" wire:model="nombre"
                                    class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Nombre">
                                @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="apellido" class="block text-xs font-semibold text-tinta mb-1.5">Apellido <span class="text-tierra">*</span></label>
                                <input type="text" id="apellido" wire:model="apellido"
                                    class="form-input @error('apellido') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Apellido">
                                @error('apellido') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="email" class="block text-xs font-semibold text-tinta mb-1.5">Email <span class="text-tierra">*</span></label>
                                <input type="email" id="email" wire:model="email"
                                    class="form-input @error('email') border-tierra bg-tierra-suave @enderror"
                                    placeholder="correo@ejemplo.com">
                                @error('email') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password" class="block text-xs font-semibold text-tinta mb-1.5">Contraseña @if(!$usuario_id)<span class="text-tierra">*</span>@endif</label>
                                <input type="password" id="password" wire:model="password"
                                    class="form-input @error('password') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Contraseña">
                                @error('password') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold text-tinta mb-1.5">Confirmar Contraseña @if(!$usuario_id)<span class="text-tierra">*</span>@endif</label>
                                <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                    class="form-input"
                                    placeholder="Confirmar contraseña">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="telefono" class="block text-xs font-semibold text-tinta mb-1.5">Telefono</label>
                                <input type="text" id="telefono" wire:model="telefono"
                                    class="form-input"
                                    placeholder="Telefono">
                                @error('telefono') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="activo" class="block text-xs font-semibold text-tinta mb-1.5">Estado <span class="text-tierra">*</span></label>
                                <select id="activo" wire:model="activo"
                                    class="form-input @error('activo') border-tierra bg-tierra-suave @enderror">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                @error('activo') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if ($usuario_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @can('crear-usuarios')
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $usuario_id ? 'Actualizar' : 'Guardar' }}
                                </x-ui.button>
                            @endcan
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @endcan
    @else
        <x-ui.card>
            <div class="p-4">
                <x-search-input placeholder="Buscar por nombre, apellido, email..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usuario)
                                <tr wire:key="row-{{ $usuario->id }}">
                                    <td><x-ui.badge variant="neutral">{{ $usuario->id }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $usuario->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $usuario->apellido }}</td>
                                    <td class="text-tinta-suave">{{ $usuario->email }}</td>
                                    <td class="text-tinta-suave">{{ $usuario->telefono ?? '-' }}</td>
                                    <td>
                                        @if($usuario->trashed())
                                            <x-ui.badge variant="danger">Inactivo</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Activo</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $usuario->id }})"
                                            deleteWireClick="eliminar({{ $usuario->id }})"
                                            deleteMessage="¿Esta seguro de eliminar este usuario?"
                                            :canEdit="auth()->user()->can('editar-usuarios')"
                                            :canDelete="auth()->user()->can('eliminar-usuarios')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay usuarios registrados." icon="user" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $usuarios->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
