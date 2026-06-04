<div x-data="{ 
    currentPageInsumos: 1,
    currentPageHistorial: 1,
    itemsPerPage: 5
}" class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-tools"></i> Kits de Mantenimiento Preventivo
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

    <?php if(session()->has('error')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="flex-1 font-medium"><?php echo e(session('error')); ?></span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="mb-6 flex gap-0">
        <button type="button" wire:click="$set('activeTab','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($activeTab === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($activeTab === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-<?php echo e($editando_kit ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($editando_kit ? 'Editar Kit' : 'Nuevo Kit'); ?>

        </button>
        <button type="button" wire:click="$set('activeTab','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($activeTab === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($activeTab === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Kits
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Tab 1: Nuevo/Editar Kit -->
        <?php if($activeTab === 'nuevo'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-<?php echo e($editando_kit ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($editando_kit ? 'Editar Kit' : 'Configurar Kit'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <!-- Selector de Maquinaria -->
                    <div class="mb-6">
                        <label for="maquinaria_select" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="bi bi-truck"></i> Maquinaria <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="maquinaria_seleccionada" id="maquinaria_select" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['maquinaria_seleccionada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" <?php if($editando_kit): ?> disabled <?php endif; ?>>
                            <option value="">Seleccione una maquinaria</option>
                            <?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($maq->id_maquinaria); ?>"><?php echo e($maq->modelo); ?> (<?php echo e($maq->tipoMaquinaria ? $maq->tipoMaquinaria->nombre : 'Sin tipo'); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['maquinaria_seleccionada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <?php if(!$maquinaria_seleccionada): ?>
                        <div class="flex flex-col items-center justify-center py-12 rounded-lg bg-blue-50 border border-blue-200">
                            <i class="bi bi-arrow-up-circle text-4xl text-blue-600 mb-3"></i>
                            <p class="text-blue-700 font-medium">Seleccione una maquinaria para configurar su kit de mantenimiento preventivo</p>
                        </div>
                    <?php else: ?>
                        <!-- Acciones -->
                        <div class="flex gap-2 mb-6">
                            <button wire:click="abrirModalAgregar" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-plus-circle"></i> Agregar Insumo
                            </button>
                            <?php if($editando_kit): ?>
                                <button wire:click="limpiarKit" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar Edición
                                </button>
                            <?php else: ?>
                                <?php if($items_count > 0): ?>
                                    <button wire:click="registrarKit" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg transition-colors font-medium text-sm">
                                        <i class="bi bi-check-circle"></i> Registrar Kit
                                    </button>
                                    <button wire:click="limpiarKit" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-colors font-medium text-sm">
                                        <i class="bi bi-trash"></i> Limpiar Todo
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if($kit_modificado && !$editando_kit && $items_count > 0): ?>
                            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-700">
                                <i class="bi bi-exclamation-triangle"></i>
                                Hay cambios sin guardar. Haz clic en <strong>Registrar Kit</strong> para confirmar.
                            </div>
                        <?php endif; ?>

                        <!-- Insumos Table with Pagination -->
                        <div class="mb-6">
                            <h6 class="mb-4 font-semibold text-slate-700 flex items-center gap-2">
                                <i class="bi bi-list-check"></i> Insumos del Kit
                                <?php if($items_count > 0): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"><?php echo e($items_count); ?></span>
                                <?php endif; ?>
                            </h6>

                            <?php if(count($items) > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-slate-200 bg-slate-50">
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 8%;">ID</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 30%;">Insumo</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 15%;">Cantidad</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 15%;">Stock</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 15%;">Tipo</th>
                                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600" style="width: 17%;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $ins = optional($item->insumo);
                                                    $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                ?>
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($item->id_kit ?? $item->id); ?></span></td>
                                                    <td class="px-3 py-3 font-semibold text-slate-800"><?php echo e($ins->nombre ?? '—'); ?></td>
                                                    <td class="px-3 py-3 text-slate-600"><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                    <td class="px-3 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e(($stock >= $item->cantidad_requerida) ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'); ?>"><?php echo e(number_format($stock, 2)); ?></span>
                                                        <?php if($stock < $item->cantidad_requerida): ?>
                                                            <br><small class="text-red-600 text-xs">Faltan <?php echo e(number_format($item->cantidad_requerida - $stock, 2)); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($item->es_obligatorio ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-slate-100 text-slate-600 border border-slate-200'); ?>">
                                                            <?php echo e($item->es_obligatorio ? 'Obligatorio' : 'Opcional'); ?>

                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <div class="flex gap-1 justify-center">
                                                            <button wire:click="abrirModalEditar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200" title="Editar">
                                                                <i class="bi bi-pencil text-sm"></i>
                                                            </button>
                                                            <button wire:click="eliminar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200" onclick="return confirm('¿Dar de baja este insumo del kit?')" title="Dar de baja">
                                                                <i class="bi bi-trash text-sm"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 rounded-lg bg-slate-50 border border-slate-200">
                                    <i class="bi bi-inbox text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-slate-600 font-medium mb-3">No hay insumos configurados para este kit</p>
                                    <button wire:click="abrirModalAgregar" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                        <i class="bi bi-plus-circle"></i> Agregar Primer Insumo
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if($items_count > 0): ?>
                                <div class="mt-6 rounded-lg bg-slate-50 border border-slate-200 p-4">
                                    <div class="grid grid-cols-4 gap-4 text-center">
                                        <div>
                                            <strong class="block text-slate-600 text-xs mb-1">Total Insumos</strong>
                                            <span class="text-xl font-semibold text-slate-800"><?php echo e($items_count); ?></span>
                                        </div>
                                        <div>
                                            <strong class="block text-slate-600 text-xs mb-1">Obligatorios</strong>
                                            <span class="text-xl font-semibold text-red-600"><?php echo e($items_obligatorios); ?></span>
                                        </div>
                                        <div>
                                            <strong class="block text-slate-600 text-xs mb-1">Opcionales</strong>
                                            <span class="text-xl font-semibold text-slate-600"><?php echo e($items_opcionales); ?></span>
                                        </div>
                                        <div>
                                            <strong class="block text-slate-600 text-xs mb-1">Stock OK</strong>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e(($items_con_stock === $items_count) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'); ?>"><?php echo e($items_con_stock); ?>/<?php echo e($items_count); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Historial Bajas Table -->
                        <div class="border-t border-slate-200 pt-6">
                            <h6 class="mb-4 font-semibold text-slate-700 flex items-center gap-2">
                                <i class="bi bi-archive"></i> Historial de Bajas
                            </h6>

                            <?php if($historial->count() > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-slate-200 bg-slate-50">
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 8%;">ID</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 35%;">Insumo</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 15%;">Cantidad</th>
                                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600" style="width: 20%;">Fecha de Baja</th>
                                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600" style="width: 22%;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            <?php $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="bg-amber-50 hover:bg-amber-100 transition-colors">
                                                    <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($item->id_kit ?? $item->id); ?></span></td>
                                                    <td class="px-3 py-3">
                                                        <strong class="text-slate-800"><?php echo e(optional($item->insumo)->nombre ?? '—'); ?></strong>
                                                        <?php if($item->es_obligatorio): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 ml-2 border border-red-200">Obligatorio</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600"><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                    <td class="px-3 py-3 text-slate-600">
                                                        <i class="bi bi-clock-history text-slate-400"></i>
                                                        <small class="text-xs"><?php echo e($item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '—'); ?></small>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <button wire:click="restaurar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="inline-flex items-center gap-2 px-3 py-1 bg-green-600 text-white hover:bg-green-700 rounded text-sm transition-colors" title="Restaurar">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 rounded-lg bg-slate-50 border border-slate-200">
                                    <i class="bi bi-archive text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-slate-600 font-medium">No hay bajas registradas para este kit</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tab 2: Listado de Kits -->
        <?php if($activeTab === 'listado'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-list-ul"></i> Kits Registrados por Maquinaria
                    </h5>
                </div>
                <div class="p-6">
                    <?php if(count($kits_registrados) > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $kits_registrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maqId => $kit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg border border-slate-200 overflow-hidden">
                                    <div class="flex items-center justify-between bg-slate-50 border-b border-slate-200 px-6 py-4">
                                        <div>
                                            <h6 class="mb-1 font-semibold text-slate-800 flex items-center gap-2">
                                                <i class="bi bi-gear-fill text-blue-600"></i> 
                                                <strong><?php echo e(optional($kit['maquinaria'])->modelo); ?></strong> 
                                                <span class="text-slate-500">(<?php echo e(optional(optional($kit['maquinaria'])->tipoMaquinaria)->nombre); ?>)</span>
                                            </h6>
                                            <small class="text-slate-500">ID: <?php echo e(optional($kit['maquinaria'])->id_maquinaria); ?></small>
                                        </div>
                                        <div class="flex gap-2">
                                            <button wire:click="editarKit(<?php echo e($maqId); ?>)" type="button" class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded text-sm transition-colors border border-blue-200" title="Editar kit">
                                                <i class="bi bi-pencil"></i> Editar
                                            </button>
                                            <button wire:click="eliminarKit(<?php echo e($maqId); ?>)" type="button" class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded text-sm transition-colors border border-red-200" onclick="return confirm('¿Está seguro de eliminar este kit completo?')" title="Eliminar kit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4">
                                        <div class="mb-4 flex gap-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"><?php echo e($kit['total_items']); ?> Insumos</span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700"><?php echo e($kit['obligatorios']); ?> Obligatorios</span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($kit['opcionales']); ?> Opcionales</span>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-slate-200 bg-slate-50">
                                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Insumo</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Cantidad</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Stock</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Tipo</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200">
                                                    <?php $__currentLoopData = $kit['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $ins = optional($item->insumo);
                                                            $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                        ?>
                                                        <tr class="hover:bg-slate-50">
                                                            <td class="px-3 py-2 font-semibold text-slate-700"><?php echo e($ins->nombre); ?></td>
                                                            <td class="px-3 py-2 text-slate-600"><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e(($stock >= $item->cantidad_requerida) ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'); ?>">
                                                                    <?php echo e(number_format($stock, 2)); ?>

                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($item->es_obligatorio ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-slate-100 text-slate-600 border border-slate-200'); ?>">
                                                                    <?php echo e($item->es_obligatorio ? 'Obligatorio' : 'Opcional'); ?>

                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 rounded-lg bg-slate-50 border border-slate-200">
                            <i class="bi bi-inbox text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-600 font-medium mb-4">No hay kits registrados aún</p>
                            <button class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'" wire:click="$set('activeTab','nuevo')">
                                <i class="bi bi-plus-circle"></i> Crear Primer Kit
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Agregar/Editar Insumo -->
    <?php if($modal_item): ?>
        <div class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 p-4 py-8" style="overflow-y: auto;">
            <div class="w-full max-w-md rounded-lg bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b px-6 py-4" style="background-color: #2d7a4f; color: white;">
                    <h5 class="font-semibold flex items-center gap-2">
                        <i class="bi bi-<?php echo e($item_id ? 'pencil-square' : 'plus-circle'); ?>"></i>
                        <?php echo e($item_id ? 'Editar' : 'Agregar'); ?> Insumo al Kit
                    </h5>
                    <button type="button" wire:click="cerrarModal" class="text-white hover:text-gray-200">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Insumo <span class="text-red-500">*</span></label>
                        <select wire:model="insumo_id" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['insumo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Seleccionar insumo...</option>
                            <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($insumo->id_insumo); ?>">
                                    <?php echo e($insumo->nombre); ?> (Stock: <?php echo e(number_format($insumo->stock ?? 0, 2)); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['insumo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Cantidad Requerida <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="cantidad_requerida" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['cantidad_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" step="0.1" min="0.01" placeholder="Ej: 10.00">
                        <?php $__errorArgs = ['cantidad_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="es_obligatorio" class="w-4 h-4 rounded">
                            <span class="text-sm font-semibold text-slate-700">¿Es obligatorio?</span>
                        </label>
                        <small class="text-slate-500 text-xs mt-1 block">Los insumos obligatorios deben estar disponibles para aprobar el mantenimiento</small>
                    </div>
                </div>
                <div class="flex gap-2 justify-end border-t px-6 py-4 bg-slate-50">
                    <button type="button" wire:click="cerrarModal" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium text-sm">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardar" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                        <i class="bi bi-save"></i> <?php echo e($item_id ? 'Actualizar' : 'Agregar'); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('[x-data*="open"]');
            if (successAlert && window.Alpine) {
                setTimeout(() => {
                    successAlert.remove?.();
                }, 3000);
            }
        });
    </script>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/configuracion-kits.blade.php ENDPATH**/ ?>