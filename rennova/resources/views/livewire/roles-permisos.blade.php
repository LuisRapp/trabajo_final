<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🛡️ Gestión de Roles y Permisos</h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
            <span class="text-red-600">⚠</span>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-6" role="tablist">
        <button
            type="button"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'roles' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
            wire:click="$set('activeTab', 'roles')"
            aria-selected="{{ $activeTab === 'roles' ? 'true' : 'false' }}"
            aria-controls="roles-tab"
        >
            👤 Roles y Permisos
        </button>
        <button
            type="button"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'users' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
            wire:click="$set('activeTab', 'users')"
            aria-selected="{{ $activeTab === 'users' ? 'true' : 'false' }}"
            aria-controls="users-tab"
        >
            👥 Asignar Roles a Usuarios
        </button>
    </div>

    <div>
        <!-- Tab 1: Roles y Permisos -->
        <div class="{{ $activeTab === 'roles' ? '' : 'hidden' }}" id="roles-tab">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Lista de Roles -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-brand text-white px-6 py-4">
                            <h5 class="text-lg font-semibold">📋 Roles del Sistema</h5>
                        </div>
                        <div class="p-6">
                            <!-- Crear Nuevo Rol -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Crear Nuevo Rol</label>
                                <div class="flex gap-1">
                                    <input type="text" wire:model="newRoleName"
                                        class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                        placeholder="Nombre del rol...">
                                    <button type="button" wire:click="createRole"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                        ➕
                                    </button>
                                </div>
                                @error('newRoleName') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <hr class="border-slate-200 my-4">

                            <!-- Lista de Roles -->
                            <div class="space-y-1">
                                @foreach($roles as $role)
                                    <div class="flex justify-between items-center px-4 py-3 rounded-lg cursor-pointer transition-colors {{ $selectedRole == $role->id ? 'bg-brand text-white' : 'hover:bg-slate-50' }}"
                                         wire:click="selectRole({{ $role->id }})" wire:key="item-{{ $role->id }}">
                                        <div>
                                            <strong>{{ $role->name }}</strong>
                                            <br>
                                            <small class="{{ $selectedRole == $role->id ? 'text-white/70' : 'text-slate-500' }}">
                                                {{ $role->permissions->count() }} permisos
                                            </small>
                                        </div>
                                        @if($role->name !== 'Administrador')
                                            <button type="button"
                                                    wire:click.stop="deleteRole({{ $role->id }})"
                                                    onclick="return confirm('¿Eliminar rol {{ $role->name }}?')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $selectedRole == $role->id ? 'text-white/80 hover:text-white hover:bg-white/20' : 'text-red-600 hover:bg-red-50' }}">
                                                🗑️
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permisos del Rol -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                            <h5 class="text-lg font-semibold text-slate-800">🔑 Permisos del Rol</h5>
                        </div>
                        <div class="p-6">
                            @if($selectedRole)
                                <form wire:submit.prevent="updateRolePermissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($permissions as $module => $modulePermissions)
                                            <div wire:key="module-{{ $module }}">
                                                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                                                    <div class="bg-slate-600 text-white px-4 py-2 text-sm">
                                                        <strong class="capitalize">{{ str_replace('-', ' ', $module) }}</strong>
                                                    </div>
                                                    <div class="p-4">
                                                        @foreach($modulePermissions as $permission)
                                                            <div class="flex items-center gap-2 py-1" wire:key="perm-{{ $permission->id }}">
                                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                                       type="checkbox"
                                                                       wire:model="rolePermissions"
                                                                       value="{{ $permission->name }}"
                                                                       id="perm-{{ $permission->id }}">
                                                                <label class="text-sm text-slate-700" for="perm-{{ $permission->id }}">
                                                                    {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                            💾 Guardar Permisos
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                                    <span>ℹ️</span> Seleccione un rol de la lista para gestionar sus permisos
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Asignar Roles a Usuarios -->
        <div class="{{ $activeTab === 'users' ? '' : 'hidden' }}" id="users-tab">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lista de Usuarios -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-brand text-white px-6 py-4">
                            <h5 class="text-lg font-semibold">👥 Usuarios del Sistema</h5>
                        </div>
                        <div class="p-6">
                            <!-- Búsqueda -->
                            <div class="mb-4">
                                <input type="text" wire:model.live="busqueda"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                    placeholder="Buscar usuario por nombre, apellido o email...">
                            </div>

                            <!-- Lista de Usuarios -->
                            <div class="space-y-1 max-h-[500px] overflow-y-auto">
                                @foreach($users as $user)
                                    <div class="flex justify-between items-center px-4 py-3 rounded-lg cursor-pointer transition-colors {{ $selectedUser == $user->id ? 'bg-brand text-white' : 'hover:bg-slate-50' }}"
                                         wire:click="selectUser({{ $user->id }})" wire:key="item-{{ $user->id }}">
                                        <div>
                                            <strong>{{ $this->displayUserName($user) }}</strong>
                                            <br>
                                            <small class="{{ $selectedUser == $user->id ? 'text-white/70' : 'text-slate-500' }}">
                                                {{ $user->email }}
                                            </small>
                                        </div>
                                        @if($user->roles->isNotEmpty())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $selectedUser == $user->id ? 'bg-white/20 text-white' : 'bg-cyan-100 text-cyan-700' }}">
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles del Usuario -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                            <h5 class="text-lg font-semibold text-slate-800">👤 Roles del Usuario</h5>
                        </div>
                        <div class="p-6">
                            @if($selectedUser)
                                <form wire:submit.prevent="updateUserRoles">
                                    <div class="mb-4">
                                        <p class="text-slate-500 mb-4">
                                            Seleccione los roles que desea asignar al usuario:
                                        </p>
                                        @foreach($roles as $role)
                                            <div class="flex items-start gap-2 py-2" wire:key="role-{{ $role->id }}">
                                                <input class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       wire:model="userRoles"
                                                       value="{{ $role->name }}"
                                                       id="role-{{ $role->id }}">
                                                <label class="text-sm" for="role-{{ $role->id }}">
                                                    <strong class="text-slate-800">{{ $role->name }}</strong>
                                                    <br>
                                                    <small class="text-slate-500">{{ $role->permissions->count() }} permisos asignados</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                            💾 Guardar Roles
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                                    <span>ℹ️</span> Seleccione un usuario de la lista para asignar roles
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
