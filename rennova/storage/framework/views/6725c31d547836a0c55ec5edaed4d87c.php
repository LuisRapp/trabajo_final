<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Gestión de Mantenimientos</h3>
        </div>
        <div class="p-6">

            
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

            
            <div class="flex border-b border-slate-200 mb-4">
                <a class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($tab_activo === 'ordenes' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
                   wire:click="cambiarTab('ordenes')"
                   href="javascript:void(0)">
                    📋 Órdenes Activas
                </a>
                <a class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($tab_activo === 'completadas' ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
                   wire:click="cambiarTab('completadas')"
                   href="javascript:void(0)">
                    ✓ Completadas
                </a>
            </div>

            
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Maquinaria</label>
                    <select wire:model="filtro_maquinaria"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <option value="">Todas</option>
                        <?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($maq->id); ?>" wire:key="option-<?php echo e($maq->id); ?>">
                                <?php echo e($maq->modelo); ?> (<?php echo e($maq->tipoMaquinaria->nombre); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo</label>
                    <select wire:model="filtro_tipo"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <option value="">Todos</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>
                </div>

                <?php if($tab_activo === 'ordenes'): ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                    <select wire:model="filtro_estado"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <option value="">Todos</option>
                        <option value="programado">Programado</option>
                        <option value="en curso">En Curso</option>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Desde</label>
                    <input type="date" wire:model="filtro_fecha_desde"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hasta</label>
                    <input type="date" wire:model="filtro_fecha_hasta"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                </div>

                <div class="flex items-end">
                    <button wire:click="resetearFiltros"
                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                        ↻
                    </button>
                </div>
            </div>

            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Maquinaria</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Toneladas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Creación</th>
                            <?php if($tab_activo === 'completadas'): ?>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Completado</th>
                            <?php endif; ?>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $ordenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orden): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr wire:key="row-<?php echo e($orden->id); ?>" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-2.5 text-slate-700"><?php echo e($orden->id); ?></td>
                            <td class="px-4 py-2.5">
                                <strong class="text-slate-800"><?php echo e($orden->maquinaria->modelo); ?></strong><br>
                                <small class="text-slate-500"><?php echo e($orden->maquinaria->tipoMaquinaria->nombre); ?></small>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($orden->tipo_mantenimiento === 'preventivo' ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700'); ?>">
                                    <?php echo e(ucfirst($orden->tipo_mantenimiento)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <?php
                                    $badgeColor = match($orden->estado) {
                                        'programado' => 'bg-slate-100 text-slate-600',
                                        'en curso' => 'bg-brand/10 text-brand',
                                        'completado' => 'bg-emerald-100 text-emerald-700',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($badgeColor); ?>">
                                    <?php echo e(ucfirst($orden->estado)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600"><?php echo e(number_format($orden->toneladas_snapshot ?? 0, 2)); ?></td>
                            <td class="px-4 py-2.5 text-slate-600"><?php echo e($orden->created_at->format('d/m/Y H:i')); ?></td>

                            <?php if($tab_activo === 'completadas'): ?>
                                <td class="px-4 py-2.5 text-slate-600">$<?php echo e(number_format($orden->costo_total ?? 0, 2)); ?></td>
                                <td class="px-4 py-2.5 text-slate-600"><?php echo e($orden->fecha_completado ? $orden->fecha_completado->format('d/m/Y H:i') : '-'); ?></td>
                            <?php endif; ?>

                            <td class="px-4 py-2.5">
                                <div class="inline-flex rounded-lg shadow-sm">
                                    <button wire:click="verDetalle(<?php echo e($orden->id); ?>)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-l-lg text-xs font-medium transition-colors"
                                            title="Ver Detalle">
                                        👁️
                                    </button>

                                    <?php if($orden->estado === 'programado'): ?>
                                        <button wire:click="abrirModalAprobar(<?php echo e($orden->id); ?>)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition-colors <?php echo e(($orden->estado === 'en curso') ? 'rounded-r-lg' : ''); ?>"
                                                title="Aprobar">
                                            ✓
                                        </button>
                                    <?php endif; ?>

                                    <?php if($orden->estado === 'en curso'): ?>
                                        <button wire:click="abrirModalCompletar(<?php echo e($orden->id); ?>)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand hover:bg-brand-hover text-white rounded-r-lg text-xs font-medium transition-colors"
                                                title="Completar">
                                            🚩
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="20" class="text-center py-8 text-slate-400">
                                No hay órdenes para mostrar
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <?php if($modal_aprobar && $orden_seleccionada): ?>
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="bg-emerald-600 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                <h5 class="text-lg font-semibold">Aprobar Orden de Mantenimiento #<?php echo e($orden_seleccionada->id); ?></h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalAprobar">✕</button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> <?php echo e($orden_seleccionada->maquinaria->modelo); ?>

                    </div>
                    <div>
                        <strong>Tipo:</strong> <?php echo e(ucfirst($orden_seleccionada->tipo_mantenimiento)); ?>

                    </div>
                </div>

                <?php if($verificacion_stock): ?>
                    <h6 class="font-semibold text-slate-800 mt-4">Verificación de Stock:</h6>

                    <?php if($verificacion_stock['puede_aprobar']): ?>
                        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm my-3">
                            ✓ Todos los insumos están disponibles
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm my-3">
                            ⚠ Stock insuficiente para aprobar
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insumo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Requerido</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Disponible</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $verificacion_stock['kit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e(in_array($item['insumo_id'], array_column($verificacion_stock['insuficientes'], 'insumo_id')) ? 'bg-red-50' : 'bg-emerald-50'); ?>" wire:key="row-<?php echo e($item['insumo_id']); ?>">
                                    <td class="px-4 py-2.5"><?php echo e($item['nombre']); ?></td>
                                    <td class="px-4 py-2.5"><?php echo e($item['cantidad_requerida']); ?></td>
                                    <td class="px-4 py-2.5"><?php echo e($item['stock_disponible']); ?></td>
                                    <td class="px-4 py-2.5">
                                        <?php if($item['stock_disponible'] >= $item['cantidad_requerida']): ?>
                                            <span class="text-emerald-600">✓ OK</span>
                                        <?php else: ?>
                                            <span class="text-red-600">✖ Faltan <?php echo e($item['cantidad_requerida'] - $item['stock_disponible']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModalAprobar">
                    Cancelar
                </button>
                <?php if($verificacion_stock && $verificacion_stock['puede_aprobar']): ?>
                    <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="aprobarOrden">
                        ✓ Aprobar Orden
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($modal_completar && $orden_seleccionada): ?>
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="bg-brand text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                <h5 class="text-lg font-semibold">Completar Mantenimiento #<?php echo e($orden_seleccionada->id); ?></h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalCompletar">✕</button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> <?php echo e($orden_seleccionada->maquinaria->modelo); ?>

                    </div>
                    <div>
                        <strong>Tipo:</strong> <?php echo e(ucfirst($orden_seleccionada->tipo_mantenimiento)); ?>

                    </div>
                    <div>
                        <strong>Toneladas:</strong> <?php echo e(number_format($orden_seleccionada->toneladas_snapshot, 2)); ?>

                    </div>
                </div>

                <h6 class="font-semibold text-slate-800">Insumos Utilizados:</h6>
                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insumo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock Disponible</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__currentLoopData = $insumos_usados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr wire:key="row-<?php echo e($index); ?>" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5">
                                    <select wire:model="insumos_usados.<?php echo e($index); ?>.insumo_id"
                                            wire:change="actualizarInsumo(<?php echo e($index); ?>, $event.target.value)"
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            <?php if($insumo['es_obligatorio']): ?> disabled <?php endif; ?>>
                                        <option value="">Seleccionar...</option>
                                        <?php $__currentLoopData = $insumos_disponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($ins->id_insumo); ?>" wire:key="option-<?php echo e($ins->id_insumo); ?>"><?php echo e($ins->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ["insumos_usados.{$index}.insumo_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-red-600 text-xs"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="number"
                                           wire:model="insumos_usados.<?php echo e($index); ?>.cantidad"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                           step="0.1" min="0"
                                           min="0">
                                    <?php $__errorArgs = ["insumos_usados.{$index}.cantidad"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-red-600 text-xs"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <?php
                                        $stockDispo = $insumo['stock_disponible'] ?? 0;
                                        $cantidad = $insumo['cantidad'] ?? 0;
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($stockDispo >= $cantidad ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                                        <?php echo e(number_format($stockDispo, 2)); ?>

                                    </span>
                                    <?php if($stockDispo < $cantidad && $cantidad > 0): ?>
                                        <br><small class="text-red-600">Faltan <?php echo e(number_format($cantidad - $stockDispo, 2)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <?php if(!$insumo['es_obligatorio']): ?>
                                        <button type="button"
                                                wire:click="eliminarInsumo(<?php echo e($index); ?>)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium shadow-sm transition-colors">
                                            🗑️
                                        </button>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">Requerido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <button type="button" wire:click="agregarInsumo"
                    class="inline-flex items-center gap-1.5 px-4 py-2 border border-brand bg-white text-brand rounded-lg text-sm font-medium hover:bg-brand/5 transition-colors mb-4">
                    ➕ Agregar Insumo
                </button>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Costo Mano de Obra</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 py-2.5 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-sm text-slate-500">$</span>
                            <input type="number"
                                   wire:model="costo_mano_obra"
                                   class="flex-1 px-4 py-2.5 border rounded-r-lg text-sm transition-colors border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20"
                                   step="0.1" min="0"
                                   min="0">
                        </div>
                        <?php $__errorArgs = ['costo_mano_obra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-600 text-xs"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModalCompletar">
                    Cancelar
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="completarMantenimiento">
                    🚩 Completar Mantenimiento
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($modal_detalle && $detalle_orden): ?>
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="bg-cyan-600 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                <h5 class="text-lg font-semibold">Detalle Orden #<?php echo e($detalle_orden->id); ?></h5>
                <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModalDetalle">✕</button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Maquinaria:</strong> <?php echo e($detalle_orden->maquinaria->modelo); ?><br>
                        <strong>Tipo Maquinaria:</strong> <?php echo e($detalle_orden->maquinaria->tipoMaquinaria->nombre); ?>

                    </div>
                    <div>
                        <strong>Tipo Mantenimiento:</strong> <?php echo e(ucfirst($detalle_orden->tipo_mantenimiento)); ?><br>
                        <strong>Estado:</strong>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($detalle_orden->estado === 'completado' ? 'bg-emerald-100 text-emerald-700' : 'bg-brand/10 text-brand'); ?>">
                            <?php echo e(ucfirst($detalle_orden->estado)); ?>

                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <strong>Toneladas Snapshot:</strong> <?php echo e(number_format($detalle_orden->toneladas_snapshot, 2)); ?>

                    </div>
                    <div>
                        <strong>Fecha Creación:</strong> <?php echo e($detalle_orden->created_at->format('d/m/Y H:i')); ?>

                    </div>
                </div>

                <?php if($detalle_orden->estado === 'completado'): ?>
                    <hr class="border-slate-200 my-4">
                    <h6 class="font-semibold text-slate-800 mb-3">Insumos Utilizados:</h6>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insumo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo Unit.</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $detalle_orden->mantenimientoInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr wire:key="row-<?php echo e($item->id); ?>">
                                    <td class="px-4 py-2.5 text-slate-700"><?php echo e($item->insumo->nombre); ?></td>
                                    <td class="px-4 py-2.5 text-slate-600"><?php echo e($item->cantidad); ?></td>
                                    <td class="px-4 py-2.5 text-slate-600">$<?php echo e(number_format($item->costo_unitario, 2)); ?></td>
                                    <td class="px-4 py-2.5 text-slate-600">$<?php echo e(number_format($item->subtotal, 2)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <strong>Costo Mano de Obra:</strong> $<?php echo e(number_format($detalle_orden->costo_mano_obra, 2)); ?>

                        </div>
                        <div>
                            <strong>Costo Total:</strong>
                            <span class="text-xl font-bold text-brand">
                                $<?php echo e(number_format($detalle_orden->costo_total, 2)); ?>

                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>Fecha Completado:</strong> <?php echo e($detalle_orden->fecha_completado->format('d/m/Y H:i')); ?>

                    </div>
                <?php endif; ?>

                <?php if($detalle_orden->descripcion): ?>
                    <hr class="border-slate-200 my-4">
                    <strong>Descripción:</strong>
                    <p class="text-slate-600"><?php echo e($detalle_orden->descripcion); ?></p>
                <?php endif; ?>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModalDetalle">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/gestion-mantenimientos.blade.php ENDPATH**/ ?>