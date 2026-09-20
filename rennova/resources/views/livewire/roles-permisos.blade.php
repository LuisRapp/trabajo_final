<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.shield-check class="size-6" />
            Gestion de Roles y Permisos
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'roles', 'label' => 'Roles y Permisos', 'icon' => 'shield-check'],
        ['value' => 'users', 'label' => 'Asignar Roles a Usuarios', 'icon' => 'users'],
    ]" activeTab="{{ $activeTab }}" tabProperty="activeTab" />

    <div>
        <!-- Tab 1: Roles y Permisos -->
        <div class="{{ $activeTab === 'roles' ? '' : 'hidden' }}" id="roles-tab">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Lista de Roles -->
                <div>
                    <x-ui.card class="overflow-hidden">
                        <div class="bg-pino text-white px-4 py-3">
                            <h5 class="text-sm font-semibold">Roles del Sistema</h5>
                        </div>
                        <div class="p-4">
                            <!-- Crear Nuevo Rol -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-tinta mb-1.5">Crear Nuevo Rol</label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model="newRoleName"
                                        class="form-input @error('newRoleName') border-tierra bg-tierra-suave @enderror"
                                        placeholder="Nombre del rol...">
                                    <x-ui.button variant="primary" icon="plus" wire:click="createRole">
                                        Agregar
                                    </x-ui.button>
                                </div>
                                @error('newRoleName') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <hr class="border-arena my-4">

                            <!-- Lista de Roles -->
                            <div class="space-y-1">
                                @foreach($roles as $role)
                                    <div class="flex justify-between items-center px-3 py-2 rounded-sm cursor-pointer transition-colors {{ $selectedRole == $role->id ? 'bg-pino text-white' : 'hover:bg-corteza-suave' }}"
                                         wire:click="selectRole({{ $role->id }})" wire:key="item-{{ $role->id }}">
                                        <div>
                                            <span class="text-sm font-medium">{{ $role->name }}</span>
                                            <p class="text-xs {{ $selectedRole == $role->id ? 'text-white/70' : 'text-tinta-suave' }}">
                                                {{ $role->permissions->count() }} permisos
                                            </p>
                                        </div>
                                        @if($role->name !== 'Administrador')
                                            <button type="button"
                                                    wire:click.stop="deleteRole({{ $role->id }})"
                                                    onclick="return confirm('¿Eliminar rol {{ $role->name }}?')"
                                                    class="inline-flex items-center px-2 py-1 rounded-sm text-xs font-medium transition-colors {{ $selectedRole == $role->id ? 'text-white/80 hover:text-white hover:bg-white/20' : 'text-tierra hover:bg-tierra-suave' }}">
                                                <flux:icon.trash class="size-3.5" />
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Permisos del Rol -->
                <div class="md:col-span-2">
                    <x-ui.card class="overflow-hidden">
                        <div class="px-4 py-3 border-b border-arena bg-hueso">
                            <h5 class="text-sm font-semibold text-tinta">Permisos del Rol</h5>
                        </div>
                        <div class="p-4">
                            @if($selectedRole)
                                <form wire:submit.prevent="updateRolePermissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($permissions as $module => $modulePermissions)
                                            <div wire:key="module-{{ $module }}">
                                                <x-ui.card class="overflow-hidden">
                                                    <div class="bg-corteza text-white px-3 py-2">
                                                        <span class="text-xs font-semibold capitalize">{{ str_replace('-', ' ', $module) }}</span>
                                                    </div>
                                                    <div class="p-3">
                                                        @foreach($modulePermissions as $permission)
                                                            <div class="flex items-center gap-2 py-1" wire:key="perm-{{ $permission->id }}">
                                                                <input class="rounded border-arena text-pino focus:ring-pino/30"
                                                                       type="checkbox"
                                                                       wire:model="rolePermissions"
                                                                       value="{{ $permission->name }}"
                                                                       id="perm-{{ $permission->id }}">
                                                                <label class="text-xs text-tinta" for="perm-{{ $permission->id }}">
                                                                    {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </x-ui.card>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-2 mt-4">
                                        <x-ui.button variant="primary" icon="check" type="submit">
                                            Guardar Permisos
                                        </x-ui.button>
                                    </div>
                                </form>
                            @else
                                <x-ui.alert variant="info" dismissible="false">
                                    Seleccione un rol de la lista para gestionar sus permisos
                                </x-ui.alert>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>

        <!-- Tab 2: Asignar Roles a Usuarios -->
        <div class="{{ $activeTab === 'users' ? '' : 'hidden' }}" id="users-tab">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Lista de Usuarios -->
                <div>
                    <x-ui.card class="overflow-hidden">
                        <div class="bg-pino text-white px-4 py-3">
                            <h5 class="text-sm font-semibold">Usuarios del Sistema</h5>
                        </div>
                        <div class="p-4">
                            <x-search-input model="busqueda" placeholder="Buscar usuario por nombre, apellido o email..." />

                            <div class="space-y-1 max-h-[500px] overflow-y-auto">
                                @foreach($users as $user)
                                    <div class="flex justify-between items-center px-3 py-2 rounded-sm cursor-pointer transition-colors {{ $selectedUser == $user->id ? 'bg-pino text-white' : 'hover:bg-corteza-suave' }}"
                                         wire:click="selectUser({{ $user->id }})" wire:key="item-{{ $user->id }}">
                                        <div>
                                            <span class="text-sm font-medium">{{ $this->displayUserName($user) }}</span>
                                            <p class="text-xs {{ $selectedUser == $user->id ? 'text-white/70' : 'text-tinta-suave' }}">
                                                {{ $user->email }}
                                            </p>
                                        </div>
                                        @if($user->roles->isNotEmpty())
                                            <x-ui.badge variant="{{ $selectedUser == $user->id ? 'neutral' : 'info' }}" class="shrink-0">
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Roles del Usuario -->
                <div>
                    <x-ui.card class="overflow-hidden">
                        <div class="px-4 py-3 border-b border-arena bg-hueso">
                            <h5 class="text-sm font-semibold text-tinta">Roles del Usuario</h5>
                        </div>
                        <div class="p-4">
                            @if($selectedUser)
                                <form wire:submit.prevent="updateUserRoles">
                                    <div class="mb-4">
                                        <p class="text-tinta-suave text-sm mb-4">
                                            Seleccione los roles que desea asignar al usuario:
                                        </p>
                                        @foreach($roles as $role)
                                            <div class="flex items-start gap-2 py-2" wire:key="role-{{ $role->id }}">
                                                <input class="mt-0.5 rounded border-arena text-pino focus:ring-pino/30"
                                                       type="checkbox"
                                                       wire:model="userRoles"
                                                       value="{{ $role->name }}"
                                                       id="role-{{ $role->id }}">
                                                <label class="text-sm" for="role-{{ $role->id }}">
                                                    <span class="font-medium text-tinta">{{ $role->name }}</span>
                                                    <p class="text-xs text-tinta-suave">{{ $role->permissions->count() }} permisos asignados</p>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <x-ui.button variant="primary" icon="check" type="submit">
                                            Guardar Roles
                                        </x-ui.button>
                                    </div>
                                </form>
                            @else
                                <x-ui.alert variant="info" dismissible="false">
                                    Seleccione un usuario de la lista para asignar roles
                                </x-ui.alert>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>
</div>
