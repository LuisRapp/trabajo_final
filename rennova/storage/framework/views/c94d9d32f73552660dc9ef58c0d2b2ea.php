<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🚛 Cargas</h1>
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
        ['value' => 'nuevo', 'label' => 'Nueva Carga', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-cargas', 'editar-cargas'])],
        ['value' => 'listado', 'label' => 'Listado de Cargas', 'icon' => 'list-ul'],
    ],'activeTab' => ''.e($tab_activo).'','tabProperty' => 'tab_activo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tab-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['value' => 'nuevo', 'label' => 'Nueva Carga', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-cargas', 'editar-cargas'])],
        ['value' => 'listado', 'label' => 'Listado de Cargas', 'icon' => 'list-ul'],
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-cargas', 'editar-cargas'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    <?php echo e($carga_id ? '✏️ Editar Carga' : '➕ Nueva Carga'); ?>

                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label for="id_lote" class="block text-sm font-semibold text-slate-700 mb-1.5">Lote <span class="text-red-500">*</span></label>
                            <select id="id_lote" wire:model="id_lote"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lote->id_lote); ?>" wire:key="option-<?php echo e($lote->id_lote); ?>"><?php echo e($lote->id_lote); ?></option>
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
                        </div>
                        <div>
                            <label for="id_categoria_madera" class="block text-sm font-semibold text-slate-700 mb-1.5">Categoría Madera</label>
                            <select id="id_categoria_madera" wire:model="id_categoria_madera"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat->id_categoria_madera); ?>" wire:key="option-<?php echo e($cat->id_categoria_madera); ?>"><?php echo e($cat->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="id_chofer" class="block text-sm font-semibold text-slate-700 mb-1.5">Chofer (ID)</label>
                            <input type="number" id="id_chofer" wire:model="id_chofer"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="id_parte_diario" class="block text-sm font-semibold text-slate-700 mb-1.5">Parte Diario</label>
                            <select id="id_parte_diario" wire:model="id_parte_diario"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $partes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parte->id_parte_diario); ?>" wire:key="option-<?php echo e($parte->id_parte_diario); ?>"><?php echo e($parte->id_parte_diario); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_parte_diario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                        <div>
                            <label for="ticket" class="block text-sm font-semibold text-slate-700 mb-1.5">Ticket</label>
                            <input type="text" id="ticket" wire:model="ticket" maxlength="20"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="peso_bruto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Bruto</label>
                            <input type="number" id="peso_bruto" wire:model="peso_bruto" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="tara" class="block text-sm font-semibold text-slate-700 mb-1.5">Tara</label>
                            <input type="number" id="tara" wire:model="tara" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="peso_neto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Neto</label>
                            <input type="number" id="peso_neto" wire:model="peso_neto" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="destino" class="block text-sm font-semibold text-slate-700 mb-1.5">Destino</label>
                            <input type="text" id="destino" wire:model="destino" maxlength="100"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="fecha_carga" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Carga <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_carga" wire:model="fecha_carga" max="<?php echo e(now()->toDateString()); ?>"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['fecha_carga'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha_carga'];
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
                        <?php if($carga_id): ?>
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-cargas', 'editar-cargas'])): ?>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ <?php echo e($carga_id ? 'Actualizar' : 'Guardar'); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.search-input','data' => ['placeholder' => 'Buscar por lote, ticket, destino, chofer o fecha...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => 'Buscar por lote, ticket, destino, chofer o fecha...']); ?>
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
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Chofer</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Parte Diario</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ticket</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Bruto</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Tara</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Neto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Destino</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $cargas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $carga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr wire:key="row-<?php echo e($carga->id_carga); ?>" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e($carga->id_carga); ?></span></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($carga->lote->id_lote ?? $carga->id_lote); ?></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($carga->categoriaMadera->nombre ?? '-'); ?></td>
                                    <td class="px-3 py-2.5 text-slate-600">
                                        <?php if($carga->chofer): ?>
                                            <?php echo e($carga->chofer->apellido); ?> <?php echo e($carga->chofer->nombre); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($carga->id_parte_diario ?? '-'); ?></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($carga->ticket ?? '-'); ?></td>
                                    <td class="px-3 py-2.5 text-right text-slate-600"><?php echo e(number_format($carga->peso_bruto ?? 0, 2)); ?></td>
                                    <td class="px-3 py-2.5 text-right text-slate-600"><?php echo e(number_format($carga->tara ?? 0, 2)); ?></td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-slate-800"><?php echo e(number_format($carga->peso_neto ?? 0, 2)); ?></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e($carga->destino ?? '-'); ?></td>
                                    <td class="px-3 py-2.5 text-slate-600"><?php echo e(\Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y')); ?></td>
                                    <td class="px-3 py-2.5 text-right">
                                        <?php if (isset($component)) { $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-buttons','data' => ['editWireClick' => 'editar('.e($carga->id_carga).')','deleteWireClick' => 'eliminar('.e($carga->id_carga).')','deleteMessage' => '¿Eliminar esta carga?','canEdit' => auth()->user()->can('editar-cargas'),'canDelete' => auth()->user()->can('eliminar-cargas')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editWireClick' => 'editar('.e($carga->id_carga).')','deleteWireClick' => 'eliminar('.e($carga->id_carga).')','deleteMessage' => '¿Eliminar esta carga?','canEdit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('editar-cargas')),'canDelete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('eliminar-cargas'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => 12,'message' => 'No hay cargas registradas.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => 12,'message' => 'No hay cargas registradas.']); ?>
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

                <?php if($cargas->hasPages()): ?>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            Mostrando <span class="font-semibold"><?php echo e($cargas->firstItem()); ?></span> a
                            <span class="font-semibold"><?php echo e($cargas->lastItem()); ?></span> de
                            <span class="font-semibold"><?php echo e($cargas->total()); ?></span> cargas
                        </div>
                        <nav class="flex gap-1">
                            <?php if($cargas->onFirstPage()): ?>
                                <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg text-sm">← Anterior</span>
                            <?php else: ?>
                                <button wire:click="previousPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors">← Anterior</button>
                            <?php endif; ?>

                            <?php $__currentLoopData = $cargas->getUrlRange(1, $cargas->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $cargas->currentPage()): ?>
                                    <span class="px-3 py-2 text-white bg-brand rounded-lg text-sm" wire:key="page-<?php echo e($page); ?>"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <button wire:click="gotoPage(<?php echo e($page); ?>)" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors" wire:key="page-<?php echo e($page); ?>"><?php echo e($page); ?></button>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($cargas->hasMorePages()): ?>
                                <button wire:click="nextPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors">Siguiente →</button>
                            <?php else: ?>
                                <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg text-sm">Siguiente →</span>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/cargas.blade.php ENDPATH**/ ?>