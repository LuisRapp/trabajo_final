<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-person-workspace"></i> Empleados
        </h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-empleados', 'editar-empleados'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Empleado
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Empleados
        </button>
    </div>

    <!-- Tab 1: Nuevo Empleado -->
    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-empleados', 'editar-empleados'])): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                    <i class="bi bi-<?php echo e($empleado_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                    <?php echo e($empleado_id ? 'Editar Empleado' : 'Nuevo Empleado'); ?>

                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">DNI <?php if(!$empleado_id): ?><span class="text-red-500">*</span><?php endif; ?></label>
                            <input type="text" wire:model="dni" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['dni'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="12345678" maxlength="8">
                            <?php $__errorArgs = ['dni'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php if($empleado_id): ?> <small class="text-slate-500 text-xs mt-1 block">Opcional - Dejar en blanco para mantener el actual</small> <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="apellido" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Apellido">
                            <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nombre" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Nombre">
                            <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rol Laboral <span class="text-red-500">*</span></label>
                            <select wire:model="id_rol_laboral" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_rol_laboral'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rol->id_rol_laboral); ?>" wire:key="option-<?php echo e($rol->id_rol_laboral); ?>"><?php echo e($rol->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_rol_laboral'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Nacimiento <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_nacimiento" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_nacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha_nacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_inicio_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_inicio_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha_inicio_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Fin</label>
                            <input type="date" wire:model="fecha_fin_actividades" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_fin_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <small class="text-slate-500 text-xs mt-1 block">Opcional</small>
                            <?php $__errorArgs = ['fecha_fin_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        <?php if($empleado_id): ?>
                            <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium text-sm">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-empleados', 'editar-empleados'])): ?>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                            <i class="bi bi-check-circle"></i> <?php echo e($empleado_id ? 'Actualizar' : 'Guardar'); ?>

                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Tab 2: Listado de Empleados -->
    <?php if($tab_activo === 'listado'): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">Listado de Empleados</h5>
            </div>
            <div class="p-6">
                <!-- Buscador -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                        <i class="bi bi-search text-slate-500"></i>
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por apellido, nombre, DNI o rol..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                    </div>
                </div>

                <!-- Tabla -->
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
                            <?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50 transition-colors" wire:key="row-<?php echo e($empleado->id_empleado); ?>">
                                    <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($empleado->id_empleado); ?></span></td>
                                    <td class="px-3 py-3 text-slate-600"><?php echo e(number_format($empleado->dni, 0, ',', '.')); ?></td>
                                    <td class="px-3 py-3 font-semibold text-slate-800"><?php echo e($empleado->apellido); ?>, <?php echo e($empleado->nombre); ?></td>
                                    <td class="px-3 py-3 text-slate-600"><?php echo e($empleado->rolLaboral?->nombre ?? 'N/A'); ?></td>
                                    <td class="px-3 py-3 text-slate-600"><?php echo e($empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td class="px-3 py-3 text-slate-600"><?php echo e($empleado->fecha_inicio_actividades ? \Carbon\Carbon::parse($empleado->fecha_inicio_actividades)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex gap-1 justify-center">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-empleados')): ?>
                                            <button type="button" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200" wire:click="editar(<?php echo e($empleado->id_empleado); ?>)" title="Editar">
                                                <i class="bi bi-pencil text-sm"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-empleados')): ?>
                                            <button type="button" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200" wire:click="eliminar(<?php echo e($empleado->id_empleado); ?>)" onclick="return confirm('¿Está seguro de eliminar este empleado?')" title="Eliminar">
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-3 py-8 text-center">
                                        <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                        <p class="text-slate-500 font-medium">No hay empleados registrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if($empleados->hasPages()): ?>
                    <div class="mt-6">
                        <?php echo e($empleados->links('pagination::tailwind')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.querySelector('[x-data*="open"]');
        if (successAlert && window.Alpine) {
            setTimeout(() => {
                successAlert.remove?.();
            }, 3000);
        }
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/empleados.blade.php ENDPATH**/ ?>