<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-box-seam"></i> Insumos
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-insumos', 'editar-insumos'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Insumo
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Insumos
        </button>
    </div>

    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-insumos', 'editar-insumos'])): ?>
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-<?php echo e($insumo_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($insumo_id ? 'Editar Insumo' : 'Nuevo Insumo'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nombre" placeholder="Nombre del insumo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['nombre'];
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Unidad de Medida <span class="text-red-500">*</span></label>
                                <select wire:model="id_unidad_medida" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_unidad_medida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($unidad->id_unidad_medida); ?>"><?php echo e($unidad->nombre); ?> (<?php echo e($unidad->abreviatura); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_unidad_medida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Proveedor Principal <span class="text-red-500">*</span></label>
                                <select wire:model="id_proveedor" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($proveedor->id_proveedor); ?>"><?php echo e($proveedor->razon_social); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                                <textarea wire:model="descripcion" placeholder="Descripción del insumo" rows="1" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors resize-none <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"></textarea>
                                <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-6 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700">
                            <i class="bi bi-info-circle mt-0.5 flex-shrink-0"></i>
                            <span>Precio calculado automáticamente mediante sistema FIFO</span>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <?php if($insumo_id): ?>
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-insumos', 'editar-insumos'])): ?>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> <?php echo e($insumo_id ? 'Actualizar' : 'Guardar'); ?>

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
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por nombre, descripción, proveedor, unidad o costo..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Nombre</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Descripción</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Unidad</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Proveedor</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Stock Actual</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Precio Promedio</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php $__empty_1 = true; $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($insumo->id_insumo); ?></span></td>
                                        <td class="px-3 py-3 font-semibold text-slate-800"><?php echo e($insumo->nombre); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($insumo->descripcion ?? 'N/A'); ?></td>
                                        <td class="px-3 py-3">
                                            <?php if($insumo->unidadMedida): ?>
                                                <span class="text-slate-600"><?php echo e($insumo->unidadMedida->nombre); ?></span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 ml-1"><?php echo e($insumo->unidadMedida->abreviatura); ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($insumo->proveedor?->razon_social ?? 'N/A'); ?></td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($insumo->stock > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200'); ?>">
                                                <?php echo e(number_format($insumo->stock, 2)); ?>

                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right text-slate-600">
                                            <?php if($insumo->precio_promedio > 0): ?>
                                                $<?php echo e(number_format($insumo->precio_promedio, 2, ',', '.')); ?>

                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex gap-1 justify-center">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-insumos')): ?>
                                                <button wire:click="editar(<?php echo e($insumo->id_insumo); ?>)" @click="$set('tab_activo', 'nuevo')" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-insumos')): ?>
                                                <button wire:click="eliminar(<?php echo e($insumo->id_insumo); ?>)" onclick="return confirm('¿Está seguro de eliminar este insumo?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="px-3 py-8 text-center">
                                            <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-slate-500 font-medium">No hay insumos registrados.</p>
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
        Livewire.on('insumoGuardado', () => {
            // Livewire actualizará automáticamente
        });
    });
</script><?php /**PATH D:\trabajo_final\rennova\resources\views\livewire\insumos.blade.php ENDPATH**/ ?>