<div class="container py-4"><div>

    <div class="d-flex justify-content-between align-items-center mb-4">    

        <h1 class="mb-0"><i class="bi bi-shield-lock"></i> Gestión de Roles y Permisos</h1></div>

    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button
                type="button"
                class="nav-link <?php echo e($activeTab === 'roles' ? 'active' : ''); ?>"
                wire:click="$set('activeTab', 'roles')"
                aria-selected="<?php echo e($activeTab === 'roles' ? 'true' : 'false'); ?>"
                aria-controls="roles-tab"
            >
                <i class="bi bi-person-badge"></i> Roles y Permisos
            </button>
        </li>
        <li class="nav-item">
            <button
                type="button"
                class="nav-link <?php echo e($activeTab === 'users' ? 'active' : ''); ?>"
                wire:click="$set('activeTab', 'users')"
                aria-selected="<?php echo e($activeTab === 'users' ? 'true' : 'false'); ?>"
                aria-controls="users-tab"
            >
                <i class="bi bi-people"></i> Asignar Roles a Usuarios
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Roles y Permisos -->
        <div class="tab-pane fade <?php echo e($activeTab === 'roles' ? 'show active' : ''); ?>" id="roles-tab">
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
                                <?php $__errorArgs = ['newRoleName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <hr>

                            <!-- Lista de Roles -->
                            <div class="list-group">
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center <?php echo e($selectedRole == $role->id ? 'active' : ''); ?>" 
                                         style="cursor: pointer;" 
                                         wire:click="selectRole(<?php echo e($role->id); ?>)">
                                        <div>
                                            <strong><?php echo e($role->name); ?></strong>
                                            <br>
                                            <small class="<?php echo e($selectedRole == $role->id ? 'text-white-50' : 'text-muted'); ?>">
                                                <?php echo e($role->permissions->count()); ?> permisos
                                            </small>
                                        </div>
                                        <?php if($role->name !== 'Administrador'): ?>
                                            <button type="button" 
                                                    wire:click.stop="deleteRole(<?php echo e($role->id); ?>)" 
                                                    onclick="return confirm('¿Eliminar rol <?php echo e($role->name); ?>?')"
                                                    class="btn btn-sm btn-<?php echo e($selectedRole == $role->id ? 'light' : 'outline-danger'); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($selectedRole): ?>
                                <form wire:submit.prevent="updateRolePermissions">
                                    <div class="row">
                                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $modulePermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header bg-secondary text-white py-2">
                                                        <strong class="text-capitalize"><?php echo e(str_replace('-', ' ', $module)); ?></strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php $__currentLoopData = $modulePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       wire:model="rolePermissions" 
                                                                       value="<?php echo e($permission->name); ?>" 
                                                                       id="perm-<?php echo e($permission->id); ?>">
                                                                <label class="form-check-label" for="perm-<?php echo e($permission->id); ?>">
                                                                    <?php echo e(ucfirst(str_replace('-', ' ', $permission->name))); ?>

                                                                </label>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Permisos
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Seleccione un rol de la lista para gestionar sus permisos
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Asignar Roles a Usuarios -->
        <div class="tab-pane fade <?php echo e($activeTab === 'users' ? 'show active' : ''); ?>" id="users-tab">
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
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item <?php echo e($selectedUser == $user->id ? 'active' : ''); ?>" 
                                         style="cursor: pointer;" 
                                         wire:click="selectUser(<?php echo e($user->id); ?>)">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo e($this->displayUserName($user)); ?></strong>
                                                <br>
                                                <small class="<?php echo e($selectedUser == $user->id ? 'text-white-50' : 'text-muted'); ?>">
                                                    <?php echo e($user->email); ?>

                                                </small>
                                            </div>
                                            <?php if($user->roles->isNotEmpty()): ?>
                                                <span class="badge bg-<?php echo e($selectedUser == $user->id ? 'light text-dark' : 'info'); ?>">
                                                    <?php echo e($user->roles->pluck('name')->join(', ')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($selectedUser): ?>
                                <form wire:submit.prevent="updateUserRoles">
                                    <div class="mb-3">
                                        <p class="text-muted mb-3">
                                            Seleccione los roles que desea asignar al usuario:
                                        </p>
                                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       wire:model="userRoles" 
                                                       value="<?php echo e($role->name); ?>" 
                                                       id="role-<?php echo e($role->id); ?>">
                                                <label class="form-check-label" for="role-<?php echo e($role->id); ?>">
                                                    <strong><?php echo e($role->name); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo e($role->permissions->count()); ?> permisos asignados</small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Roles
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Seleccione un usuario de la lista para asignar roles
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/roles-permisos.blade.php ENDPATH**/ ?>