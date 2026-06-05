<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🚜 Maquinarias</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal671874bf23aa9b9423bd98fb633269fa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal671874bf23aa9b9423bd98fb633269fa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tab-nav','data' => ['tabs' => [
        ['value' => 'nuevo', 'label' => 'Nueva Maquinaria', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-maquinarias', 'editar-maquinarias'])],
        ['value' => 'listado', 'label' => 'Listado de Maquinarias', 'icon' => 'list-ul'],
    ],'activeTab' => ''.e($tab_activo).'','tabProperty' => 'tab_activo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tab-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['value' => 'nuevo', 'label' => 'Nueva Maquinaria', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-maquinarias', 'editar-maquinarias'])],
        ['value' => 'listado', 'label' => 'Listado de Maquinarias', 'icon' => 'list-ul'],
    ]),'activeTab' => ''.e($tab_activo).'','tabProperty' => 'tab_activo']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal671874bf23aa9b9423bd98fb633269fa)): ?>
<?php $attributes = $__attributesOriginal671874bf23aa9b9423bd98fb633269fa; ?>
<?php unset($__attributesOriginal671874bf23aa9b9423bd98fb633269fa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal671874bf23aa9b9423bd98fb633269fa)): ?>
<?php $component = $__componentOriginal671874bf23aa9b9423bd98fb633269fa; ?>
<?php unset($__componentOriginal671874bf23aa9b9423bd98fb633269fa); ?>
<?php endif; ?>

    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-maquinarias', 'editar-maquinarias'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    <?php echo e($maquinaria_id ? '✏️ Editar Maquinaria' : '➕ Nueva Maquinaria'); ?>

                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_tipo_maquinaria" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Maquinaria <span class="text-red-500">*</span></label>
                            <select id="id_tipo_maquinaria" wire:model="id_tipo_maquinaria"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_tipo_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tipo->id_tipo_maquinaria); ?>" wire:key="option-<?php echo e($tipo->id_tipo_maquinaria); ?>"><?php echo e($tipo->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_tipo_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="modelo" class="block text-sm font-semibold text-slate-700 mb-1.5">Modelo <span class="text-red-500">*</span></label>
                            <input type="text" id="modelo" wire:model="modelo"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['modelo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Modelo de la maquinaria">
                            <?php $__errorArgs = ['modelo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                            <select id="estado" wire:model="estado"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione...</option>
                                <option value="disponible">Disponible</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_mantenimiento">En Mantenimiento</option>
                            </select>
                            <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="es_alquilada" class="block text-sm font-semibold text-slate-700 mb-1.5">¿Es alquilada?</label>
                            <select id="es_alquilada" wire:model="es_alquilada"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div>
                            <label for="fecha_inicio_actividades" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Inicio Actividades <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_inicio_actividades" wire:model="fecha_inicio_actividades"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['fecha_inicio_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha_inicio_actividades'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="umbral_toneladas" class="block text-sm font-semibold text-slate-700 mb-1.5">Umbral Toneladas <span class="text-red-500">*</span></label>
                            <input type="number" id="umbral_toneladas" wire:model="umbral_toneladas" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['umbral_toneladas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Ej: 1000">
                            <?php $__errorArgs = ['umbral_toneladas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <?php if($maquinaria_id): ?>
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-maquinarias', 'editar-maquinarias'])): ?>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ <?php echo e($maquinaria_id ? 'Actualizar' : 'Guardar'); ?>

                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php elseif($tab_activo === 'listado'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <?php if (isset($component)) { $__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.search-input','data' => ['placeholder' => 'Buscar por tipo, modelo o estado...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => 'Buscar por tipo, modelo o estado...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96)): ?>
<?php $attributes = $__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96; ?>
<?php unset($__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96)): ?>
<?php $component = $__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96; ?>
<?php unset($__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96); ?>
<?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Modelo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alquilada</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Umbral (ton)</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maquinaria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr wire:key="row-<?php echo e($maquinaria->id_maquinaria); ?>" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e($maquinaria->id_maquinaria); ?></span></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($maquinaria->tipoMaquinaria->nombre ?? 'N/A'); ?></td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800"><?php echo e($maquinaria->modelo); ?></td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            <?php echo e($maquinaria->estado === 'disponible' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ''); ?>

                                            <?php echo e($maquinaria->estado === 'asignada' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ''); ?>

                                            <?php echo e($maquinaria->estado === 'en_mantenimiento' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ''); ?>">
                                            <?php echo e(ucfirst($maquinaria->estado)); ?>

                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($maquinaria->es_alquilada ? 'Sí' : 'No'); ?></td>
                                    <td class="px-3 py-2.5 text-right text-slate-600"><?php echo e(number_format($maquinaria->umbral_toneladas, 2, ',', '.')); ?></td>
                                    <td class="px-3 py-2.5 text-right">
                                        <?php if (isset($component)) { $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-buttons','data' => ['editWireClick' => 'editar('.e($maquinaria->id_maquinaria).')','deleteWireClick' => 'eliminar('.e($maquinaria->id_maquinaria).')','deleteMessage' => '¿Está seguro de eliminar esta maquinaria?','canEdit' => auth()->user()->can('editar-maquinarias'),'canDelete' => auth()->user()->can('eliminar-maquinarias')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editWireClick' => 'editar('.e($maquinaria->id_maquinaria).')','deleteWireClick' => 'eliminar('.e($maquinaria->id_maquinaria).')','deleteMessage' => '¿Está seguro de eliminar esta maquinaria?','canEdit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('editar-maquinarias')),'canDelete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('eliminar-maquinarias'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd)): ?>
<?php $attributes = $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd; ?>
<?php unset($__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf9332b595ad3d3a806f9da4dda8769dd)): ?>
<?php $component = $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd; ?>
<?php unset($__componentOriginalf9332b595ad3d3a806f9da4dda8769dd); ?>
<?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => 7,'message' => 'No hay maquinarias registradas.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => 7,'message' => 'No hay maquinarias registradas.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/maquinarias.blade.php ENDPATH**/ ?>