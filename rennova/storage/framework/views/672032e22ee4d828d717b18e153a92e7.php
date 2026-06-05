<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Pestañas (Tabs) -->
    <div class="flex border-b border-slate-200 mb-6" id="asignacionesTabs" role="tablist">
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($mostrar_historial ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
                id="historial-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', true)">
            📋 Historial de Asignaciones
        </button>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-asignaciones-lote', 'editar-asignaciones-lote'])): ?>
        <button class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e(!$mostrar_historial ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
                id="formulario-tab"
                type="button"
                role="tab"
                wire:click="$set('mostrar_historial', false)">
            <?php echo e($modo === 'editar' ? '✏️ Modificar Asignación' : '➕ Nueva Asignación'); ?>

        </button>
        <?php endif; ?>
    </div>

    <div id="asignacionesTabContent">
        <!-- Pestaña 1: Historial de Asignaciones -->
        <div class="<?php echo e($mostrar_historial ? '' : 'hidden'); ?>"
             id="historial-asignaciones"
             role="tabpanel">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">📋 Historial de Asignaciones por Lote</h5>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crear-asignaciones-lote')): ?>
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="nuevaAsignacion">
                        ➕ Nueva Asignación
                    </button>
                    <?php endif; ?>
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

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Empleados Asignados</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Maquinarias Asignadas</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__empty_1 = true; $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr wire:key="row-<?php echo e($lote->id_lote); ?>" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <strong>Lote #<?php echo e($lote->id_lote); ?></strong><br>
                                            <small class="text-slate-500"><?php echo e($lote->ubicacion); ?></small>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($lote->estado === 'activo' ? 'bg-emerald-100 text-emerald-700' : ($lote->estado === 'cerrado' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700')); ?>">
                                                <?php echo e(ucfirst($lote->estado)); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <?php if($lote->empleados->count() > 0): ?>
                                                <small>
                                                    <?php $__currentLoopData = $lote->empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 mr-1" wire:key="emp-<?php echo e($emp->id_empleado); ?>"><?php echo e($emp->apellido); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </small>
                                                <br><small class="text-slate-500">Total: <?php echo e($lote->empleados->count()); ?></small>
                                            <?php else: ?>
                                                <span class="text-slate-400">Sin empleados</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <?php if($lote->maquinarias->count() > 0): ?>
                                                <small>
                                                    <?php $__currentLoopData = $lote->maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand mr-1" wire:key="maq-<?php echo e($maq->id_maquinaria); ?>"><?php echo e($maq->modelo); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </small>
                                                <br><small class="text-slate-500">Total: <?php echo e($lote->maquinarias->count()); ?></small>
                                            <?php else: ?>
                                                <span class="text-slate-400">Sin maquinarias</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <div class="inline-flex rounded-lg shadow-sm">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-asignaciones-lote')): ?>
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-brand bg-white text-brand hover:bg-brand/5 rounded-l-lg text-xs font-medium transition-colors"
                                                        wire:click="editarAsignacion(<?php echo e($lote->id_lote); ?>)"
                                                        title="Modificar asignaciones">
                                                    ✏️
                                                </button>
                                                <?php endif; ?>
                                                <?php if($lote->estado !== 'cerrado'): ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-asignaciones-lote')): ?>
                                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-amber-500 bg-white text-amber-600 hover:bg-amber-50 text-xs font-medium transition-colors"
                                                            wire:click="liberar(<?php echo e($lote->id_lote); ?>)"
                                                            onclick="return confirm('¿Cerrar este lote y liberar recursos?')"
                                                            title="Finalizar y liberar">
                                                        ✓
                                                    </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-asignaciones-lote')): ?>
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-300 bg-white text-red-600 hover:bg-red-50 rounded-r-lg text-xs font-medium transition-colors"
                                                        wire:click="eliminarAsignacion(<?php echo e($lote->id_lote); ?>)"
                                                        onclick="return confirm('¿Eliminar todas las asignaciones de este lote?')"
                                                        title="Eliminar asignaciones">
                                                    🗑️
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-slate-400">
                                            <div class="text-5xl mb-2">📥</div>
                                            <p>No hay asignaciones registradas.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Formulario de Asignación -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-asignaciones-lote', 'editar-asignaciones-lote'])): ?>
        <div class="<?php echo e(!$mostrar_historial ? '' : 'hidden'); ?>"
             id="formulario-asignacion"
             role="tabpanel">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6" id="formulario-asignacion-card">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center">
                    <h5 class="text-lg font-semibold text-slate-800">
                        <?php echo e($modo === 'editar' ? '✏️ Modificar Asignación' : '➕ Nueva Asignación'); ?>

                    </h5>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lote <span class="text-red-500">*</span></label>
                            <select class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="id_lote">
                                <option value="">Seleccione un lote</option>
                                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($l->id_lote); ?>" wire:key="option-<?php echo e($l->id_lote); ?>">Lote #<?php echo e($l->id_lote); ?> - <?php echo e($l->ubicacion); ?> (<?php echo e($l->estado); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-slate-500 text-xs mt-1 block">Primero seleccione el Lote para ver y editar sus asignaciones.</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-300 overflow-hidden">
                            <div class="bg-slate-600 text-white px-6 py-4 flex justify-between items-center">
                                <strong>👥 Empleados asignados</strong>
                                <?php if($id_lote): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white"><?php echo e(count($empleados_seleccionados)); ?> seleccionados</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <?php if($id_lote): ?>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                               placeholder="Buscar empleado..."
                                               wire:model.live="busqueda_empleado">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                        <?php $__empty_1 = true; $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="flex items-center gap-2 py-1" wire:key="item-<?php echo e($emp->id_empleado); ?>">
                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       value="<?php echo e($emp->id_empleado); ?>"
                                                       id="emp-<?php echo e($emp->id_empleado); ?>"
                                                       wire:model.live="empleados_seleccionados">
                                                <label class="text-sm text-slate-700" for="emp-<?php echo e($emp->id_empleado); ?>">
                                                    <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?>

                                                    <small class="text-slate-500">- <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?></small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <small class="text-slate-400">No se encontraron empleados.</small>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-slate-500 block mt-3">
                                        ℹ️ Seleccione todos los empleados que trabajarán en este lote.
                                    </small>
                                <?php else: ?>
                                    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                                        <small>Seleccione un Lote para habilitar esta sección.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                            <div class="bg-brand text-white px-6 py-4 flex justify-between items-center">
                                <strong>🚛 Maquinarias asignadas</strong>
                                <?php if($id_lote): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white"><?php echo e(count($maquinarias_seleccionadas)); ?> seleccionadas</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <?php if($id_lote): ?>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                                               placeholder="Buscar maquinaria..."
                                               wire:model.live="busqueda_maquinaria">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                        <?php $__empty_1 = true; $__currentLoopData = $this->maquinariasFiltrada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="flex items-center gap-2 py-1" wire:key="item-<?php echo e($maq->id_maquinaria); ?>">
                                                <input class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                       type="checkbox"
                                                       value="<?php echo e($maq->id_maquinaria); ?>"
                                                       id="maq-<?php echo e($maq->id_maquinaria); ?>"
                                                       wire:model.live="maquinarias_seleccionadas">
                                                <label class="text-sm text-slate-700" for="maq-<?php echo e($maq->id_maquinaria); ?>">
                                                    <?php echo e($maq->modelo); ?>

                                                    <small class="text-slate-500">- <?php echo e($maq->estado); ?> - <?php echo e($maq->tipoMaquinaria->nombre ?? 'N/A'); ?></small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <small class="text-slate-400">No se encontraron maquinarias.</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mt-3">
                                        <small>
                                            ℹ️ Si solo hay una maquinaria asignada al lote, se preseleccionará en el Parte Diario.
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                                        <small>Seleccione un Lote para habilitar esta sección.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-asignaciones-lote', 'editar-asignaciones-lote'])): ?>
                        <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors"
                                wire:click="guardar"
                                wire:loading.attr="disabled"
                                <?php if(!$id_lote): echo 'disabled'; endif; ?>>
                            💾 Guardar asignaciones
                        </button>
                        <?php endif; ?>
                        <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cancelar">
                            ✕ Cancelar
                        </button>
                        <div wire:loading wire:target="guardar" class="text-slate-500 self-center">
                            ↻ Guardando...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            document.getElementById('formulario-asignacion-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/asignaciones-lote.blade.php ENDPATH**/ ?>