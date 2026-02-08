<div class="container py-4"><div>

    <div class="d-flex justify-content-between align-items-center mb-4">    {{-- Because she competes with no one, no one can compete with her. --}}

        <h1 class="mb-0"><i class="bi bi-shield-lock"></i> Gestión de Roles y Permisos</h1></div>

    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button
                type="button"
                class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}"
                wire:click="$set('activeTab', 'roles')"
                aria-selected="{{ $activeTab === 'roles' ? 'true' : 'false' }}"
                aria-controls="roles-tab"
            >
                <i class="bi bi-person-badge"></i> Roles y Permisos
            </button>
        </li>
        <li class="nav-item">
            <button
                type="button"
                class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}"
                wire:click="$set('activeTab', 'users')"
                aria-selected="{{ $activeTab === 'users' ? 'true' : 'false' }}"
                aria-controls="users-tab"
            >
                <i class="bi bi-people"></i> Asignar Roles a Usuarios
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Roles y Permisos -->
        <div class="tab-pane fade {{ $activeTab === 'roles' ? 'show active' : '' }}" id="roles-tab">
            <div class="row">
                <!-- Lista de Roles -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-list"></i> Roles del Sistema</h5>
                        </div>
                        <div class="card-body">
                            <!-- Crear Nuevo Rol -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Crear Nuevo Rol</label>
                                <div class="input-group">
                                    <input type="text" wire:model="newRoleName" class="form-control" placeholder="Nombre del rol...">
                                    <button type="button" wire:click="createRole" class="btn btn-success">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </div>
                                @error('newRoleName') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <hr>

                            <!-- Lista de Roles -->
                            <div class="list-group">
                                @foreach($roles as $role)
                                    <div class="list-group-item d-flex justify-content-between align-items-center {{ $selectedRole == $role->id ? 'active' : '' }}" 
                                         style="cursor: pointer;" 
                                         wire:click="selectRole({{ $role->id }})">
                                        <div>
                                            <strong>{{ $role->name }}</strong>
                                            <br>
                                            <small class="{{ $selectedRole == $role->id ? 'text-white-50' : 'text-muted' }}">
                                                {{ $role->permissions->count() }} permisos
                                            </small>
                                        </div>
                                        @if($role->name !== 'Administrador')
                                            <button type="button" 
                                                    wire:click.stop="deleteRole({{ $role->id }})" 
                                                    onclick="return confirm('¿Eliminar rol {{ $role->name }}?')"
                                                    class="btn btn-sm btn-{{ $selectedRole == $role->id ? 'light' : 'outline-danger' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permisos del Rol -->
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-key"></i> Permisos del Rol</h5>
                        </div>
                        <div class="card-body">
                            @if($selectedRole)
                                <form wire:submit.prevent="updateRolePermissions">
                                    <div class="row">
                                        @foreach($permissions as $module => $modulePermissions)
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header bg-secondary text-white py-2">
                                                        <strong class="text-capitalize">{{ str_replace('-', ' ', $module) }}</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($modulePermissions as $permission)
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       wire:model="rolePermissions" 
                                                                       value="{{ $permission->name }}" 
                                                                       id="perm-{{ $permission->id }}">
                                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                                    {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Permisos
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Seleccione un rol de la lista para gestionar sus permisos
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Asignar Roles a Usuarios -->
        <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="users-tab">
            <div class="row">
                <!-- Lista de Usuarios -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-people"></i> Usuarios del Sistema</h5>
                        </div>
                        <div class="card-body">
                            <!-- Búsqueda -->
                            <div class="mb-3">
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar usuario por nombre, apellido o email...">
                            </div>

                            <!-- Lista de Usuarios -->
                            <div class="list-group" style="max-height: 500px; overflow-y: auto;">
                                @foreach($users as $user)
                                    <div class="list-group-item {{ $selectedUser == $user->id ? 'active' : '' }}" 
                                         style="cursor: pointer;" 
                                         wire:click="selectUser({{ $user->id }})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $this->displayUserName($user) }}</strong>
                                                <br>
                                                <small class="{{ $selectedUser == $user->id ? 'text-white-50' : 'text-muted' }}">
                                                    {{ $user->email }}
                                                </small>
                                            </div>
                                            @if($user->roles->isNotEmpty())
                                                <span class="badge bg-{{ $selectedUser == $user->id ? 'light text-dark' : 'info' }}">
                                                    {{ $user->roles->pluck('name')->join(', ') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles del Usuario -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Roles del Usuario</h5>
                        </div>
                        <div class="card-body">
                            @if($selectedUser)
                                <form wire:submit.prevent="updateUserRoles">
                                    <div class="mb-3">
                                        <p class="text-muted mb-3">
                                            Seleccione los roles que desea asignar al usuario:
                                        </p>
                                        @foreach($roles as $role)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       wire:model="userRoles" 
                                                       value="{{ $role->name }}" 
                                                       id="role-{{ $role->id }}">
                                                <label class="form-check-label" for="role-{{ $role->id }}">
                                                    <strong>{{ $role->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $role->permissions->count() }} permisos asignados</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Roles
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Seleccione un usuario de la lista para asignar roles
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
