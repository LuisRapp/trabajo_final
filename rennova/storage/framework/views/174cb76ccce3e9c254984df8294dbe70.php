<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-person-badge"></i> Choferes
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

    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-choferes', 'editar-choferes'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Chofer
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Choferes
        </button>
    </div>

    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-choferes', 'editar-choferes'])): ?>
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-<?php echo e($chofer_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($chofer_id ? 'Modificar Chofer' : 'Nuevo Chofer'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Cliente <span class="text-red-500">*</span></label>
                                <select wire:model="id_cliente" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_cliente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un cliente...</option>
                                    <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cliente->id_cliente); ?>" wire:key="option-<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social ?? $cliente->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_cliente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-slate-500 text-xs">El chofer estará asociado a este cliente</small>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Apellido <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="apellido" placeholder="Apellido del chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nombre" placeholder="Nombre del chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">DNI <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="dni" placeholder="DNI del chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['dni'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['dni'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                                <input type="text" wire:model="telefono" placeholder="Teléfono de contacto" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Dirección</label>
                                <input type="text" wire:model="direccion" placeholder="Dirección del chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Estado</label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="estado" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo e($estado ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-600 border border-slate-200'); ?>">
                                    <?php echo e($estado ? 'Activo' : 'Inactivo'); ?>

                                </span>
                            </div>
                            <small class="text-slate-500 text-xs mt-2 block">Los choferes inactivos no aparecerán en los formularios de carga</small>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <?php if($chofer_id): ?>
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-choferes', 'editar-choferes'])): ?>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> <?php echo e($chofer_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php elseif($tab_activo === 'listado'): ?>
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6">
                    <!-- Buscador -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                            <i class="bi bi-search text-slate-500"></i>
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por cliente, apellido, nombre, DNI, teléfono o dirección..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Cliente</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Apellido y Nombre</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">DNI</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Teléfono</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php $__empty_1 = true; $__currentLoopData = $choferes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-<?php echo e($c->id_chofer); ?>">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($c->id_chofer); ?></span></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($c->cliente?->razon_social ?? $c->cliente?->nombre ?? 'Sin cliente'); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($c->apellido); ?>, <?php echo e($c->nombre); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($c->dni); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($c->telefono ?? '-'); ?></td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($c->estado ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-600 border border-slate-200'); ?>">
                                                <?php echo e($c->estado ? 'Activo' : 'Inactivo'); ?>

                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex gap-1 justify-center">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-choferes')): ?>
                                                <button wire:click="editar(<?php echo e($c->id_chofer); ?>)" @click="$set('tab_activo', 'nuevo')" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-choferes')): ?>
                                                <button wire:click="eliminar(<?php echo e($c->id_chofer); ?>)" onclick="return confirm('¿Está seguro de eliminar este chofer?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
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
                                            <p class="text-slate-500 font-medium">No hay choferes registrados.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript para cambiar entre pestañas -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('choferGuardado', () => {
            // Livewire actualizará automáticamente
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/choferes.blade.php ENDPATH**/ ?>