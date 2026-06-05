<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-slate-900">📄 Auditorías del Sistema</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-600 text-white px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <strong>🕐 Registro de Cambios</strong>
                </div>
                <div>
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/20 text-white rounded-lg text-sm font-medium hover:bg-white/30 transition-colors" wire:click="toggleFiltros" aria-controls="filtrosAuditoria" aria-expanded="<?php echo e($mostrarFiltros ? 'true' : 'false'); ?>">
                        🔍 Filtros
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Filtros Colapsables -->
            <?php if($mostrarFiltros): ?>
            <div id="filtrosAuditoria">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 pb-4 border-b border-slate-200">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Buscar</label>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                            placeholder="URL, IP o tag...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Modelo</label>
                        <select wire:model.live="filtroModelo"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos los modelos</option>
                            <?php $__currentLoopData = $modelos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($modelo['value']); ?>" wire:key="option-<?php echo e($modelo['value']); ?>"><?php echo e($modelo['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Evento</label>
                        <select wire:model.live="filtroEvento"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario</label>
                        <select wire:model.live="filtroUsuario"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos los usuarios</option>
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($usuario['id']); ?>" wire:key="option-<?php echo e($usuario['id']); ?>"><?php echo e($usuario['nombre']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Desde</label>
                        <input type="date" wire:model.live="filtroFechaDesde"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hasta</label>
                        <input type="date" wire:model.live="filtroFechaHasta"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors w-full justify-center">
                            ✕ Limpiar
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Auditorías -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Modelo / Registro</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Evento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr wire:key="row-<?php echo e($auditoria->id); ?>" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">#<?php echo e($auditoria->id); ?></span></td>
                                <td class="px-4 py-2.5">
                                    <strong><?php echo e(class_basename($auditoria->auditable_type)); ?></strong><br>
                                    <small class="text-slate-500">ID: <?php echo e($auditoria->auditable_id); ?></small>
                                </td>
                                <td class="px-4 py-2.5">
                                    <?php if($auditoria->event === 'created'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">➕ Creado</span>
                                    <?php elseif($auditoria->event === 'updated'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">✏️ Actualizado</span>
                                    <?php elseif($auditoria->event === 'deleted'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">🗑️ Eliminado</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e(ucfirst($auditoria->event)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <?php if($auditoria->user): ?>
                                        <span class="text-brand">👤</span> <?php echo e($auditoria->user->name); ?><br>
                                        <small class="text-slate-500"><?php echo e($auditoria->ip_address ?? 'N/A'); ?></small>
                                    <?php else: ?>
                                        <span class="text-slate-500">🤖 Sistema</span><br>
                                        <small class="text-slate-500"><?php echo e($auditoria->ip_address ?? 'N/A'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <?php echo e($auditoria->created_at->format('d/m/Y H:i')); ?><br>
                                    <small class="text-slate-500"><?php echo e($auditoria->created_at->diffForHumans()); ?></small>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-xs font-medium shadow-sm transition-colors"
                                        wire:click="$set('modalDetalle', <?php echo e($auditoria->id); ?>)">
                                        👁️ Ver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="text-5xl mb-2">📥</div>
                                    <p>No hay auditorías registradas con los filtros aplicados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            <?php echo e($auditorias->links()); ?>

        </div>
    </div>

    <!-- Modales de Detalles -->
    <?php $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(isset($modalDetalle) && $modalDetalle == $auditoria->id): ?>
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:key="modal-<?php echo e($auditoria->id); ?>">
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">
                        ℹ️ Detalles de Auditoría #<?php echo e($auditoria->id); ?>

                    </h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600" wire:click="$set('modalDetalle', null)">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-slate-200">
                        <div>
                            <p class="mb-2"><strong>Modelo:</strong> <?php echo e(class_basename($auditoria->auditable_type)); ?></p>
                            <p class="mb-2"><strong>ID del Registro:</strong> #<?php echo e($auditoria->auditable_id); ?></p>
                            <p><strong>Evento:</strong>
                                <?php if($auditoria->event === 'created'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Creado</span>
                                <?php elseif($auditoria->event === 'updated'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">Actualizado</span>
                                <?php elseif($auditoria->event === 'deleted'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Eliminado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <p class="mb-2"><strong>Usuario:</strong> <?php echo e($auditoria->user->name ?? 'Sistema'); ?></p>
                            <p class="mb-2"><strong>IP:</strong> <?php echo e($auditoria->ip_address ?? 'N/A'); ?></p>
                            <p><strong>Fecha:</strong> <?php echo e($auditoria->created_at->format('d/m/Y H:i:s')); ?></p>
                        </div>
                    </div>

                    <?php if($auditoria->url): ?>
                        <div class="mb-4">
                            <strong>URL:</strong> <code class="block bg-slate-100 p-3 rounded-lg mt-1 text-sm"><?php echo e($auditoria->url); ?></code>
                        </div>
                    <?php endif; ?>

                    <?php if($auditoria->event === 'updated' && $auditoria->old_values && $auditoria->new_values): ?>
                        <h6 class="mb-3 font-semibold text-slate-800">↔️ Cambios Realizados</h6>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border border-slate-200 rounded-lg">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[30%]">Campo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[35%]">Valor Anterior</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[35%]">Valor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php $__currentLoopData = $auditoria->new_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $valorNuevo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($auditoria->old_values[$campo]) && $auditoria->old_values[$campo] != $valorNuevo): ?>
                                            <tr wire:key="field-<?php echo e($campo); ?>">
                                                <td class="px-4 py-2.5"><strong><?php echo e($campo); ?></strong></td>
                                                <td class="px-4 py-2.5">
                                                    <div class="break-all">
                                                        <?php echo e(is_array($auditoria->old_values[$campo]) ? json_encode($auditoria->old_values[$campo], JSON_UNESCAPED_UNICODE) : ($auditoria->old_values[$campo] ?? 'null')); ?>

                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <div class="break-all">
                                                        <?php echo e(is_array($valorNuevo) ? json_encode($valorNuevo, JSON_UNESCAPED_UNICODE) : ($valorNuevo ?? 'null')); ?>

                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif($auditoria->event === 'created' && $auditoria->new_values): ?>
                        <h6 class="mb-3 font-semibold text-slate-800">➕ Datos Creados</h6>
                        <div class="bg-slate-100 p-4 rounded-lg">
                            <pre class="text-sm max-h-[400px] overflow-y-auto"><code><?php echo e(json_encode($auditoria->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                        </div>
                    <?php elseif($auditoria->event === 'deleted' && $auditoria->old_values): ?>
                        <h6 class="mb-3 font-semibold text-slate-800">🗑️ Datos Eliminados</h6>
                        <div class="bg-slate-100 p-4 rounded-lg">
                            <pre class="text-sm max-h-[400px] overflow-y-auto"><code><?php echo e(json_encode($auditoria->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                        wire:click="$set('modalDetalle', null)">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/auditorias.blade.php ENDPATH**/ ?>