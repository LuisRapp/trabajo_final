<div>
    <div class="mx-auto max-w-7xl px-4 py-8" x-data="{ tab: 'listado' }">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            📍 Lotes
        </h1>
    </div>

    <?php if (isset($component)) { $__componentOriginal5b09c79149dfb771c232996af5f9dae4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b09c79149dfb771c232996af5f9dae4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-messages','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash-messages'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b09c79149dfb771c232996af5f9dae4)): ?>
<?php $attributes = $__attributesOriginal5b09c79149dfb771c232996af5f9dae4; ?>
<?php unset($__attributesOriginal5b09c79149dfb771c232996af5f9dae4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b09c79149dfb771c232996af5f9dae4)): ?>
<?php $component = $__componentOriginal5b09c79149dfb771c232996af5f9dae4; ?>
<?php unset($__componentOriginal5b09c79149dfb771c232996af5f9dae4); ?>
<?php endif; ?>

    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
        <button type="button" @click="tab = 'nuevo'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all"
            :class="tab === 'nuevo' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
            ➕ Nuevo Lote
        </button>
        <?php endif; ?>
        <button type="button" @click="tab = 'listado'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all"
            :class="tab === 'listado' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
            📋 Listado de Lotes
        </button>
    </div>

    <div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
        <div x-show="tab === 'nuevo'" x-transition>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-700">
                        <?php echo e($lote_id ? '✏️' : '➕'); ?>

                        <?php echo e($lote_id ? 'Modificar Lote' : 'Nuevo Lote'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Propietario <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="propietario"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('propietario') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Nombre del propietario">
                                <?php $__errorArgs = ['propietario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Ubicación <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="ubicacion"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('ubicacion') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Ubicación del lote">
                                <?php $__errorArgs = ['ubicacion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Especie</label>
                                <input type="text" wire:model="especie"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('especie') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Especie de madera">
                                <?php $__errorArgs = ['especie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Superficie (ha)</label>
                                <input type="number" wire:model="superficie" step="0.1" min="0"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('superficie') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="0.00">
                                <?php $__errorArgs = ['superficie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Condición de compra</label>
                                <select wire:model="condicion_compra"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('condicion_compra') ? 'ring-2 ring-red-500' : ''); ?>">
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Vuelo Forestal</option>
                                    <option value="alquilado">Compra por tonelada</option>
                                </select>
                                <?php $__errorArgs = ['condicion_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Estado</label>
                                <select wire:model="estado"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('estado') ? 'ring-2 ring-red-500' : ''); ?>">
                                    <option value="activo">Activo</option>
                                    <option value="en_proceso">En Explotación</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="baja">Baja</option>
                                </select>
                                <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Tarea principal <span class="text-red-500">*</span></label>
                            <select wire:model="main_task_type"
                                class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('main_task_type') ? 'ring-2 ring-red-500' : ''); ?>">
                                <option value="">Seleccione...</option>
                                <?php $__currentLoopData = $this->taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tt->value); ?>" wire:key="option-<?php echo e($tt->value); ?>"><?php echo e($tt->label()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['main_task_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            <div class="flex items-center gap-2 font-semibold">
                                ℹ️
                                Coordenadas GPS (Opcional)
                            </div>
                            <p class="mt-1 text-blue-700">
                                Agregue las coordenadas para habilitar pronóstico de lluvia y alertas climáticas.
                                <a href="https://www.google.com/maps" target="_blank" class="font-medium underline hover:text-blue-900">Buscar coordenadas</a>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">🌐 Latitud</label>
                                <input type="number" wire:model="latitud" step="0.00000001" min="-90" max="90"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('latitud') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="-27.469771">
                                <?php $__errorArgs = ['latitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -27.469771 (entre -90 y 90)</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">📍 Longitud</label>
                                <input type="number" wire:model="longitud" step="0.00000001" min="-180" max="180"
                                    class="w-full rounded-lg border border-slate-300 py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('longitud') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="-58.832443">
                                <?php $__errorArgs = ['longitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -58.832443 (entre -180 y 180)</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="resetCampos"
                                class="lotes-form-btn lotes-form-btn--secondary">
                                ✕ Cancelar
                            </button>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
                            <button type="submit"
                                class="lotes-form-btn lotes-form-btn--primary">
                                ✓ <?php echo e($lote_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div x-show="tab === 'listado'" x-transition>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                <div class="border-b border-slate-200 bg-slate-50 p-6">
                    <div class="relative max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            🔍
                        </span>
                        <input type="text" wire:model.live="busqueda"
                            class="block w-full rounded-lg border-slate-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="Buscar por propietario, ubicación o especie...">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-3 py-4 text-center font-semibold">ID</th>
                                <th class="px-3 py-4 font-semibold">Propietario</th>
                                <th class="px-3 py-4 font-semibold">Ubicación</th>
                                <th class="px-3 py-4 font-semibold">Especie</th>
                                <th class="px-3 py-4 text-right font-semibold">Superficie (ha)</th>
                                <th class="px-3 py-4 font-semibold">Coordenadas GPS</th>
                                <th class="px-3 py-4 font-semibold">Condición</th>
                                <th class="px-3 py-4 font-semibold">Estado</th>
                                <th class="px-3 py-4 text-right font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="transition-colors hover:bg-slate-50" wire:key="row-<?php echo e($lote->id_lote); ?>">
                                    <td class="px-3 py-4 text-center">
                                        <span class="inline-block rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600"><?php echo e($lote->id_lote); ?></span>
                                    </td>
                                    <td class="px-3 py-4 font-medium text-slate-900"><?php echo e($lote->propietario); ?></td>
                                    <td class="px-3 py-4 text-slate-500"><?php echo e($lote->ubicacion); ?></td>
                                    <td class="px-3 py-4 text-slate-500"><?php echo e($lote->especie ?? '-'); ?></td>
                                    <td class="px-3 py-4 text-right tabular-nums"><?php echo e(number_format($lote->superficie ?? 0, 2)); ?></td>
                                    <td class="px-3 py-4">
                                        <?php if($lote->latitud && $lote->longitud): ?>
                                            <a href="https://www.google.com/maps?q=<?php echo e($lote->latitud); ?>,<?php echo e($lote->longitud); ?>" target="_blank"
                                                class="inline-flex items-center gap-2 text-sm text-blue-700 hover:text-blue-900">
                                                📍
                                                <span class="tabular-nums"><?php echo e(number_format($lote->latitud, 6)); ?>, <?php echo e(number_format($lote->longitud, 6)); ?></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400">🌐 Sin coordenadas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-4">
                                        <?php if($lote->condicion_compra): ?>
                                            <?php
                                                $condicionLabel = $lote->condicion_compra === 'propio'
                                                    ? 'Vuelo Forestal'
                                                    : 'Compra por tonelada';
                                            ?>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($lote->condicion_compra == 'propio' ? 'bg-green-50 border border-green-200 text-brand' : 'bg-blue-100 text-blue-800'); ?>">
                                                <?php echo e($condicionLabel); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            <?php echo e($lote->estado === 'activo' ? 'bg-green-50 border border-green-200 text-brand' : ($lote->estado === 'en_proceso' ? 'bg-amber-100 text-amber-800' : ($lote->estado === 'cerrado' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800'))); ?>">
                                            <?php if($lote->estado === 'cerrado'): ?>
                                                ✅
                                            <?php endif; ?>
                                            <?php echo e(ucfirst(str_replace('_', ' ', $lote->estado))); ?>

                                        </span>
                                    </td>
                                    <td class="pe-3 ps-3 py-4 text-right">
                                        <?php
                                            $estadoRaw = $lote->estado;
                                            if (is_object($estadoRaw) && property_exists($estadoRaw, 'value')) {
                                                $estadoRaw = $estadoRaw->value;
                                            }
                                            $estado = strtolower(trim((string) $estadoRaw));
                                            $estado = preg_replace('/\s+/', '_', $estado);
                                            $estado = str_replace('-', '_', $estado);

                                            $esActivo = $estado === 'activo';
                                            $esInactivo = $estado === 'inactivo';
                                            $esCerrado = $estado === 'cerrado';
                                            $esEnProceso = $estado === 'en_proceso';

                                            $accionLabel = $esActivo ? 'Iniciar' : 'Ver';
                                            $accionIcon = $esActivo ? 'play-fill' : 'eye-fill';
                                            $accionClass = $esActivo
                                                ? 'lotes-accion-btn lotes-accion-btn--iniciar'
                                                : 'lotes-accion-btn lotes-accion-btn--ver';
                                        ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <?php if($esCerrado): ?>
                                                <span class="inline-flex h-8 w-24 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border text-[10px] font-bold uppercase bg-green-50 text-brand border-brand">
                                                    ✅ Finalizado
                                                </span>
                                            <?php elseif($esInactivo): ?>
                                                <span class="inline-flex h-8 w-24 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border border-slate-200 bg-slate-100 text-[10px] font-bold uppercase text-slate-400">
                                                    Pausado
                                                </span>
                                            <?php else: ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-lotes')): ?>
                                                <button type="button" 
                                                    wire:click="openLaunchpad(<?php echo e($lote->id_lote); ?>)"
                                                    class="<?php echo e($accionClass); ?>">
                                                    <?php if($accionIcon === 'play-fill'): ?>▶️ <?php elseif($accionIcon === 'eye-fill'): ?>👁️ <?php else: ?> ▶️ <?php endif; ?>
                                                    <span><?php echo e($accionLabel); ?></span>
                                                </button>
                                                
                                                <?php if($esEnProceso): ?>
                                                    <button type="button" 
                                                        wire:click="finalizarLote(<?php echo e($lote->id_lote); ?>)"
                                                        onclick="return confirm('¿Finalizar este lote? Se liberarán todos los empleados y maquinarias asignadas.')"
                                                        class="inline-flex h-8 w-24 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border text-xs font-bold uppercase text-white transition-all hover:shadow-md bg-brand border-brand hover:brightness-90">
                                                        🚩
                                                        <span>Finalizar</span>
                                                    </button>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['editar-lotes', 'eliminar-lotes'])): ?>
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                                                    ⋮
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                    class="absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-lg border border-slate-100 bg-white shadow-xl ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-lotes')): ?>
                                                        <button wire:click="editar(<?php echo e($lote->id_lote); ?>)" onclick="cambiarAPestanaFormulario()"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                            ✏️ Editar
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-lotes')): ?>
                                                        <button wire:click="eliminar(<?php echo e($lote->id_lote); ?>)" onclick="return confirm('¿Está seguro de eliminar este lote?')"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                            🗑️ Eliminar
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                        📭
                                        <p class="mb-0">No hay lotes registrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                </table>
            </div>

            <div class="mt-4">
                <?php echo e($lotes->links()); ?>

            </div>
            </div>
        </div>
    </div>
    </div>


<?php if($mostrarModalRecomendaciones && $modalLoteId): ?>
    <?php if (isset($component)) { $__componentOriginal54bc31532429e0dc5df7a2dcd2b5d9e0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54bc31532429e0dc5df7a2dcd2b5d9e0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.lotes.recomendaciones-modal','data' => ['recomendaciones' => $recomendaciones,'recomendacionesError' => $recomendacionesError,'recomendacionesMensaje' => $recomendacionesMensaje,'modalLoteId' => $modalLoteId,'editProposalId' => $editProposalId,'editData' => $editData,'expandedProposalId' => $expandedProposalId,'editingProposals' => $editingProposals,'editProposedMaquinarias' => $editProposedMaquinarias]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lotes.recomendaciones-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['recomendaciones' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recomendaciones),'recomendaciones-error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recomendacionesError),'recomendaciones-mensaje' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recomendacionesMensaje),'modal-lote-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modalLoteId),'edit-proposal-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editProposalId),'edit-data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editData),'expanded-proposal-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($expandedProposalId),'editing-proposals' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editingProposals),'edit-proposed-maquinarias' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editProposedMaquinarias)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54bc31532429e0dc5df7a2dcd2b5d9e0)): ?>
<?php $attributes = $__attributesOriginal54bc31532429e0dc5df7a2dcd2b5d9e0; ?>
<?php unset($__attributesOriginal54bc31532429e0dc5df7a2dcd2b5d9e0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54bc31532429e0dc5df7a2dcd2b5d9e0)): ?>
<?php $component = $__componentOriginal54bc31532429e0dc5df7a2dcd2b5d9e0; ?>
<?php unset($__componentOriginal54bc31532429e0dc5df7a2dcd2b5d9e0); ?>
<?php endif; ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Prevenir scroll del body cuando el modal está abierto
    const checkModal = () => {
        const modal = document.querySelector('.lotes-modal-overlay');
        if (modal) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    };
    
    // Verificar al cargar
    document.addEventListener('DOMContentLoaded', checkModal);
    
    // Verificar después de actualizaciones de Livewire
    document.addEventListener('livewire:navigated', checkModal);
    
    // Observar cambios en el DOM
    const observer = new MutationObserver(checkModal);
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Función para cambiar a pestaña de formulario
    function cambiarAPestanaFormulario() {
        const event = new CustomEvent('cambiarTab', { detail: 'nuevo' });
        window.dispatchEvent(event);
    }
</script>
<?php $__env->stopPush(); ?>

</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/lotes.blade.php ENDPATH**/ ?>