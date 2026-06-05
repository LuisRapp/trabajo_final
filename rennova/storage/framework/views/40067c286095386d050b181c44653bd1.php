<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">📦 Gestión de Stock (FIFO)</h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crear-gestion-stock')): ?>
        <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="abrirModal">
            ➕ Registrar Entrada
        </button>
        <?php endif; ?>
    </div>

    <?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border <?php echo e(session('alert-type', 'success') === 'danger' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'); ?> px-5 py-3 shadow-sm" role="alert">
            <span class="<?php echo e(session('alert-type', 'success') === 'danger' ? 'text-red-600' : 'text-emerald-600'); ?>">✓</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="<?php echo e(session('alert-type', 'success') === 'danger' ? 'text-red-600 hover:text-red-800' : 'text-emerald-600 hover:text-emerald-800'); ?>" @click="open = false">✕</button>
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">📦 Lotes Activos</h6>
            <h3 class="text-2xl font-bold text-brand"><?php echo e($estadisticas['total_lotes']); ?></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">📦 Stock Total</h6>
            <h3 class="text-2xl font-bold text-cyan-600"><?php echo e(number_format($estadisticas['stock_total'], 2)); ?></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">💰 Valor Inventario</h6>
            <h3 class="text-2xl font-bold text-emerald-600">$<?php echo e(number_format($estadisticas['valor_inventario'], 2)); ?></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h6 class="text-slate-500 mb-2">⚠ Próximos a Agotar</h6>
            <h3 class="text-2xl font-bold <?php echo e($estadisticas['lotes_proximos_agotar'] > 0 ? 'text-amber-500' : 'text-slate-400'); ?>"><?php echo e($estadisticas['lotes_proximos_agotar']); ?></h3>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-slate-800">🔍 Filtros</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Insumo</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_insumo">
                        <option value="">Todos los insumos</option>
                        <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($insumo->id_insumo); ?>" wire:key="option-<?php echo e($insumo->id_insumo); ?>"><?php echo e($insumo->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Proveedor</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_proveedor">
                        <option value="">Todos los proveedores</option>
                        <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($proveedor->id_proveedor); ?>" wire:key="option-<?php echo e($proveedor->id_proveedor); ?>"><?php echo e($proveedor->razon_social); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo Movimiento</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_tipo">
                        <option value="">Todos</option>
                        <option value="compra">Compra</option>
                        <option value="ajuste_entrada">Ajuste Entrada</option>
                        <option value="devolucion">Devolución</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                    <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_estado">
                        <option value="disponibles">Disponibles</option>
                        <option value="agotados">Agotados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors w-full justify-center" wire:click="limpiarFiltros">
                        ✕ Limpiar
                    </button>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Desde</label>
                    <input type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_fecha_inicio">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Hasta</label>
                    <input type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filtro_fecha_fin">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de lotes -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-slate-800">📋 Lotes de Inventario</h5>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insumo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Compra</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cant. Inicial</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Disponible</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor Disp.</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($lote->agotado ? 'bg-slate-50' : ''); ?> hover:bg-slate-50 transition-colors" wire:key="row-<?php echo e($lote->id_lote_inventario); ?>">
                                <td class="px-4 py-2.5"><strong><?php echo e($lote->id_lote_inventario); ?></strong></td>
                                <td class="px-4 py-2.5 text-slate-700"><?php echo e($lote->insumo->nombre ?? 'N/A'); ?></td>
                                <td class="px-4 py-2.5 text-slate-700"><?php echo e($lote->proveedor->razon_social ?? 'N/A'); ?></td>
                                <td class="px-4 py-2.5 text-slate-600"><?php echo e($lote->fecha_compra->format('d/m/Y')); ?></td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($lote->tipo_movimiento === 'compra' ? 'bg-brand/10 text-brand' : 'bg-cyan-100 text-cyan-700'); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $lote->tipo_movimiento))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-600"><?php echo e(number_format($lote->cantidad_inicial, 2)); ?></td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="<?php echo e(\App\Services\InventarioService::estaProximoAgotar($lote) ? 'text-amber-500 font-bold' : 'text-slate-700'); ?>">
                                        <?php echo e(number_format($lote->cantidad_disponible, 2)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-600">$<?php echo e(number_format($lote->precio_unitario, 2)); ?></td>
                                <td class="px-4 py-2.5 text-right text-slate-600">$<?php echo e(number_format($lote->valor_disponible, 2)); ?></td>
                                <td class="px-4 py-2.5 text-center">
                                    <?php if($lote->agotado): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">✖ Agotado</span>
                                    <?php elseif(\App\Services\InventarioService::estaProximoAgotar($lote)): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">⚠ Bajo</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">✓ Disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-xs font-medium shadow-sm transition-colors"
                                        wire:click="verDetalle(<?php echo e($lote->id_lote_inventario); ?>)" title="Ver detalle">
                                        👁️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="text-center py-8 text-slate-400">
                                    <div class="text-3xl mb-2">📥</div>
                                    No hay lotes de inventario registrados con los filtros aplicados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            <?php echo e($lotes->links()); ?>

        </div>
    </div>

    <!-- Modal: Registrar Entrada -->
    <?php if($mostrarModal): ?>
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-brand text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold">
                        📦 Registrar Entrada de Stock
                    </h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarModal">✕</button>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Insumo <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="id_insumo">
                                    <option value="">Seleccione un insumo</option>
                                    <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($insumo->id_insumo); ?>" wire:key="option-<?php echo e($insumo->id_insumo); ?>">
                                            <?php echo e($insumo->nombre); ?> (Stock: <?php echo e(number_format($insumo->stock ?? 0, 2)); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Proveedor</label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="id_proveedor">
                                    <option value="">Seleccione un proveedor</option>
                                    <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($proveedor->id_proveedor); ?>" wire:key="option-<?php echo e($proveedor->id_proveedor); ?>"><?php echo e($proveedor->razon_social); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cantidad <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" min="0"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    wire:model.live="cantidad">
                                <?php $__errorArgs = ['cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Precio Unitario <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" min="0"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['precio_unitario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    wire:model.live="precio_unitario">
                                <?php $__errorArgs = ['precio_unitario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Costo Total</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-slate-300 bg-slate-50 rounded-lg text-sm text-slate-500 cursor-not-allowed"
                                    value="$<?php echo e(number_format(floatval($cantidad ?? 0) * floatval($precio_unitario ?? 0), 2)); ?>"
                                    disabled readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Número de Factura</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['numero_factura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    wire:model="numero_factura">
                                <?php $__errorArgs = ['numero_factura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Compra <span class="text-red-500">*</span></label>
                                <input type="date"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['fecha_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    wire:model="fecha_compra">
                                <?php $__errorArgs = ['fecha_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Movimiento <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['tipo_movimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="tipo_movimiento">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="compra">Compra</option>
                                    <option value="ajuste_entrada">Ajuste de Entrada</option>
                                    <option value="devolucion">Devolución</option>
                                </select>
                                <?php $__errorArgs = ['tipo_movimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Observaciones</label>
                                <textarea class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                          rows="3" wire:model="observaciones" placeholder="Observaciones adicionales..."></textarea>
                                <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModal">
                        ✕ Cancelar
                    </button>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crear-gestion-stock')): ?>
                    <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="guardar">
                        💾 Guardar Entrada
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal: Detalle de Lote -->
    <?php if($loteSeleccionado): ?>
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:ignore.self>
            <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-cyan-600 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold">
                        ℹ️ Detalle del Lote #<?php echo e($loteSeleccionado?->id_lote_inventario); ?>

                    </h5>
                    <button type="button" class="text-white/80 hover:text-white" wire:click="cerrarDetalle">✕</button>
                </div>
                <div class="p-6">
                    <!-- Información del Lote -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                                <h6 class="font-semibold text-slate-800">📄 Información General</h6>
                            </div>
                            <div class="p-6">
                                <table class="w-full text-sm">
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 w-1/2 text-slate-500 font-medium">Insumo:</th>
                                        <td class="py-2"><?php echo e($loteSeleccionado?->insumo?->nombre ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Proveedor:</th>
                                        <td class="py-2"><?php echo e($loteSeleccionado?->proveedor?->razon_social ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Fecha Compra:</th>
                                        <td class="py-2"><?php echo e(optional($loteSeleccionado?->fecha_compra)->format('d/m/Y')); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-slate-500 font-medium">Número Factura:</th>
                                        <td class="py-2"><?php echo e($loteSeleccionado?->numero_factura ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                                <h6 class="font-semibold text-slate-800">📈 Cantidades y Valores</h6>
                            </div>
                            <div class="p-6">
                                <table class="w-full text-sm mb-4">
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 w-1/2 text-slate-500 font-medium">Cantidad Inicial:</th>
                                        <td class="py-2 text-right"><?php echo e(number_format($loteSeleccionado?->cantidad_inicial ?? 0, 2)); ?></td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <th class="text-left py-2 text-slate-500 font-medium">Cantidad Disponible:</th>
                                        <td class="py-2 text-right font-bold text-emerald-600"><?php echo e(number_format($loteSeleccionado?->cantidad_disponible ?? 0, 2)); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-left py-2 text-slate-500 font-medium">Precio Unitario:</th>
                                        <td class="py-2 text-right">$<?php echo e(number_format($loteSeleccionado?->precio_unitario ?? 0, 2)); ?></td>
                                    </tr>
                                </table>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Porcentaje Consumido:</label>
                                    <div class="w-full bg-slate-200 rounded-full h-6">
                                        <div class="h-full rounded-full text-xs text-white text-center leading-6 <?php echo e(($loteSeleccionado?->porcentaje_consumido ?? 0) > 80 ? 'bg-amber-500' : 'bg-brand'); ?>"
                                             style="width: <?php echo e($loteSeleccionado?->porcentaje_consumido ?? 0); ?>%">
                                            <?php echo e(number_format($loteSeleccionado?->porcentaje_consumido ?? 0, 1)); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(($loteSeleccionado?->observaciones ?? null)): ?>
                        <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mb-6">
                            <span>💬</span> <strong>Observaciones:</strong> <?php echo e($loteSeleccionado?->observaciones); ?>

                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                            <h6 class="font-semibold text-slate-800">🕐 Historial de Movimientos</h6>
                        </div>
                        <div class="p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Unit.</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php $__empty_1 = true; $__currentLoopData = ($loteSeleccionado?->movimientos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr wire:key="row-<?php echo e($mov->id); ?>" class="hover:bg-slate-50 transition-colors">
                                                <td class="px-4 py-2.5 text-slate-600"><?php echo e(optional(\Carbon\Carbon::parse($mov->fecha))->format('d/m/Y H:i')); ?></td>
                                                <td class="px-4 py-2.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($mov->tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                                                        <?php echo e(ucfirst($mov->tipo)); ?>

                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 text-right text-slate-600"><?php echo e(number_format($mov->cantidad, 2)); ?></td>
                                                <td class="px-4 py-2.5 text-right text-slate-600">$<?php echo e(number_format($mov->precio_unitario, 2)); ?></td>
                                                <td class="px-4 py-2.5 text-right text-slate-600">$<?php echo e(number_format($mov->costo_total_movimiento ?? 0, 2)); ?></td>
                                                <td class="px-4 py-2.5 text-slate-600"><?php echo e($mov->motivo ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-8 text-slate-400">
                                                    <div class="text-xl mb-2">📥</div> No hay movimientos registrados
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarDetalle">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/gestion-stock.blade.php ENDPATH**/ ?>