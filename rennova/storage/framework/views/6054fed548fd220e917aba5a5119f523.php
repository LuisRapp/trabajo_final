<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🛡️ Gestión de Roles y Permisos</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    <?php endif; ?>

    <?php if(session()->has('error')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
            <span class="text-red-600">⚠</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('error')); ?></span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-6" role="tablist">
        <button
            type="button"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($activeTab === 'roles' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
            wire:click="$set('activeTab', 'roles')"
            aria-selected="<?php echo e($activeTab === 'roles' ? 'true' : 'false'); ?>"
            aria-controls="roles-tab"
        >
            👤 Roles y Permisos
        </button>
        <button
            type="button"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($activeTab === 'users' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
            wire:click="$set('activeTab', 'users')"
            aria-selected="<?php echo e($activeTab === 'users' ? 'true' : 'false'); ?>"
            aria-controls="users-tab"
        >
            👥 Asignar Roles a Usuarios
        </button>
    </div>

    <div>
        <!-- Tab 1: Roles y Permisos -->
        <div class="<?php echo e($activeTab === 'roles' ? '' : 'hidden'); ?>" id="roles-tab">
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
                                <?php $__errorArgs = ['newRoleName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <hr class="border-slate-200 my-4">

                            <!-- Lista de Roles -->
                            <div class="space-y-1">
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex justify-between items-center px-4 py-3 rounded-lg cursor-pointer transition-colors <?php echo e($selectedRole == $role->id ? 'bg-brand text-white' : 'hover:bg-slate-50'); ?>"
                                         wire:click="selectRole(<?php echo e($role->id); ?>)" wire:key="item-<?php echo e($role->id); ?>">
                                        <div>
                                            <strong><?php echo e($role->name); ?></strong>
                                            <br>
                                            <small class="<?php echo e($selectedRole == $role->id ? 'text-white/70' : 'text-slate-500'); ?>">
                                                <?php echo e($role->permissions->count()); ?> permisos
                                            </small>
                                        </div>
                                        <?php if($role->name !== 'Administrador'): ?>
                                            <button type="button"
                                                    wire:click.stop="deleteRole(<?php echo e($role->id); ?>)"
                                                    onclick="return confirm('¿Eliminar rol <?php echo e($role->name); ?>?')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?php echo e($selectedRole == $role->id ? 'text-white/80 hover:text-white hover:bg-white/20' : 'text-red-600 hover:bg-red-50'); ?>">
                                                🗑️
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($selectedRole): ?>
                                <form wire:submit.prevent="updateRolePermissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $modulePermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div wire:key="module-<?php echo e($module); ?>">
                                                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                                                    <div class="bg-slate-600 text-white px-4 py-2 text-sm">
                                                        <strong class="capitalize"><?php echo e(str_replace('-', ' ', $module)); ?></strong>
                                                    </div>
                                                    <div class="p-4">
                                                        <?php $__currentLoopData = $modulePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="flex items-center gap-2 py-1" wire:key="perm-<?php echo e($permission->id); ?>">
                                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                                       type="checkbox"
                                                                       wire:model="rolePermissions"
                                                                       value="<?php echo e($permission->name); ?>"
                                                                       id="perm-<?php echo e($permission->id); ?>">
                                                                <label class="text-sm text-slate-700" for="perm-<?php echo e($permission->id); ?>">
                                                                    <?php echo e(ucfirst(str_replace('-', ' ', $permission->name))); ?>

                                                                </label>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                            💾 Guardar Permisos
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                                    <span>ℹ️</span> Seleccione un rol de la lista para gestionar sus permisos
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Asignar Roles a Usuarios -->
        <div class="<?php echo e($activeTab === 'users' ? '' : 'hidden'); ?>" id="users-tab">
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
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex justify-between items-center px-4 py-3 rounded-lg cursor-pointer transition-colors <?php echo e($selectedUser == $user->id ? 'bg-brand text-white' : 'hover:bg-slate-50'); ?>"
                                         wire:click="selectUser(<?php echo e($user->id); ?>)" wire:key="item-<?php echo e($user->id); ?>">
                                        <div>
                                            <strong><?php echo e($this->displayUserName($user)); ?></strong>
                                            <br>
                                            <small class="<?php echo e($selectedUser == $user->id ? 'text-white/70' : 'text-slate-500'); ?>">
                                                <?php echo e($user->email); ?>

                                            </small>
                                        </div>
                                        <?php if($user->roles->isNotEmpty()): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($selectedUser == $user->id ? 'bg-white/20 text-white' : 'bg-cyan-100 text-cyan-700'); ?>">
                                                <?php echo e($user->roles->pluck('name')->join(', ')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($selectedUser): ?>
                                <form wire:submit.prevent="updateUserRoles">
                                    <div class="mb-4">
                                        <p class="text-slate-500 mb-4">
                                            Seleccione los roles que desea asignar al usuario:
                                        </p>
                                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-start gap-2 py-2" wire:key="role-<?php echo e($role->id); ?>">
                                                <input class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       wire:model="userRoles"
                                                       value="<?php echo e($role->name); ?>"
                                                       id="role-<?php echo e($role->id); ?>">
                                                <label class="text-sm" for="role-<?php echo e($role->id); ?>">
                                                    <strong class="text-slate-800"><?php echo e($role->name); ?></strong>
                                                    <br>
                                                    <small class="text-slate-500"><?php echo e($role->permissions->count()); ?> permisos asignados</small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                            💾 Guardar Roles
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                                    <span>ℹ️</span> Seleccione un usuario de la lista para asignar roles
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/roles-permisos.blade.php ENDPATH**/ ?>