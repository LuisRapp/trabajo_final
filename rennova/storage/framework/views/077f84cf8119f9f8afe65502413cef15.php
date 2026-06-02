<div>
    <div class="mx-auto max-w-7xl px-4 py-8" x-data="{ tab: 'listado' }">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-geo-alt"></i> Lotes
        </h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php if(session()->has('error')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="flex-1 font-medium"><?php echo e(session('error')); ?></span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
        <button type="button" @click="tab = 'nuevo'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all"
            :class="tab === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            :style="tab === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''">
            <i class="bi bi-plus-circle"></i> Nuevo Lote
        </button>
        <?php endif; ?>
        <button type="button" @click="tab = 'listado'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all"
            :class="tab === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            :style="tab === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''">
            <i class="bi bi-list-ul"></i> Listado de Lotes
        </button>
    </div>

    <div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
        <div x-show="tab === 'nuevo'" x-transition>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-700">
                        <i class="bi bi-<?php echo e($lote_id ? 'pencil-square' : 'plus-circle'); ?>"></i>
                        <?php echo e($lote_id ? 'Modificar Lote' : 'Nuevo Lote'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Propietario <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="propietario"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('propietario') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Nombre del propietario">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['propietario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Ubicación <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="ubicacion"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('ubicacion') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Ubicación del lote">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['ubicacion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Especie</label>
                                <input type="text" wire:model="especie"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('especie') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="Especie de madera">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['especie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Superficie (ha)</label>
                                <input type="number" wire:model="superficie" step="0.1" min="0"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('superficie') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="0.00">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['superficie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Condición de compra</label>
                                <select wire:model="condicion_compra"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('condicion_compra') ? 'ring-2 ring-red-500' : ''); ?>">
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Vuelo Forestal</option>
                                    <option value="alquilado">Compra por tonelada</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['condicion_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Estado</label>
                                <select wire:model="estado"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('estado') ? 'ring-2 ring-red-500' : ''); ?>">
                                    <option value="activo">Activo</option>
                                    <option value="en_proceso">En Explotación</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="baja">Baja</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Tarea principal <span class="text-red-500">*</span></label>
                            <select wire:model="main_task_type"
                                class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('main_task_type') ? 'ring-2 ring-red-500' : ''); ?>">
                                <option value="">Seleccione...</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tt->value); ?>"><?php echo e($tt->label()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['main_task_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            <div class="flex items-center gap-2 font-semibold">
                                <i class="bi bi-info-circle"></i>
                                Coordenadas GPS (Opcional)
                            </div>
                            <p class="mt-1 text-blue-700">
                                Agregue las coordenadas para habilitar pronóstico de lluvia y alertas climáticas.
                                <a href="https://www.google.com/maps" target="_blank" class="font-medium underline hover:text-blue-900">Buscar coordenadas</a>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700"><i class="bi bi-geo"></i> Latitud</label>
                                <input type="number" wire:model="latitud" step="0.00000001" min="-90" max="90"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('latitud') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="-27.469771">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['latitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -27.469771 (entre -90 y 90)</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700"><i class="bi bi-geo-alt"></i> Longitud</label>
                                <input type="number" wire:model="longitud" step="0.00000001" min="-180" max="180"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors <?php echo e($errors->has('longitud') ? 'ring-2 ring-red-500' : ''); ?>"
                                    placeholder="-58.832443">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['longitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -58.832443 (entre -180 y 180)</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="resetCampos"
                                class="lotes-form-btn lotes-form-btn--secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lotes', 'editar-lotes'])): ?>
                            <button type="submit"
                                class="lotes-form-btn lotes-form-btn--primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($lote_id ? 'Actualizar' : 'Guardar'); ?>

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
                            <i class="bi bi-search"></i>
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
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-3 py-4 text-center">
                                        <span class="inline-block rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600"><?php echo e($lote->id_lote); ?></span>
                                    </td>
                                    <td class="px-3 py-4 font-medium text-slate-900"><?php echo e($lote->propietario); ?></td>
                                    <td class="px-3 py-4 text-slate-500"><?php echo e($lote->ubicacion); ?></td>
                                    <td class="px-3 py-4 text-slate-500"><?php echo e($lote->especie ?? '-'); ?></td>
                                    <td class="px-3 py-4 text-right tabular-nums"><?php echo e(number_format($lote->superficie ?? 0, 2)); ?></td>
                                    <td class="px-3 py-4">
                                        <!--[if BLOCK]><![endif]--><?php if($lote->latitud && $lote->longitud): ?>
                                            <a href="https://www.google.com/maps?q=<?php echo e($lote->latitud); ?>,<?php echo e($lote->longitud); ?>" target="_blank"
                                                class="inline-flex items-center gap-2 text-sm text-blue-700 hover:text-blue-900">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                <span class="tabular-nums"><?php echo e(number_format($lote->latitud, 6)); ?>, <?php echo e(number_format($lote->longitud, 6)); ?></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400"><i class="bi bi-geo"></i> Sin coordenadas</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td class="px-3 py-4">
                                        <!--[if BLOCK]><![endif]--><?php if($lote->condicion_compra): ?>
                                            <?php
                                                $condicionLabel = $lote->condicion_compra === 'propio'
                                                    ? 'Vuelo Forestal'
                                                    : 'Compra por tonelada';
                                            ?>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($lote->condicion_compra == 'propio' ? 'bg-green-50 border border-green-200' : 'bg-blue-100 text-blue-800'); ?>" style="<?php echo e($lote->condicion_compra == 'propio' ? 'color: #2d7a4f' : ''); ?>">
                                                <?php echo e($condicionLabel); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            <?php echo e($lote->estado === 'activo' ? 'bg-green-50 border border-green-200' : ($lote->estado === 'en_proceso' ? 'bg-amber-100 text-amber-800' : ($lote->estado === 'cerrado' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800'))); ?>"
                                            style="<?php echo e($lote->estado === 'activo' ? 'color: #2d7a4f' : ''); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($lote->estado === 'cerrado'): ?>
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
                                            <!--[if BLOCK]><![endif]--><?php if($esCerrado): ?>
                                                <span class="inline-flex h-8 w-24 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border text-[10px] font-bold uppercase bg-green-50" style="color: #2d7a4f; border-color: #2d7a4f;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Finalizado
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
                                                    <i class="bi bi-<?php echo e($accionIcon); ?> text-base"></i>
                                                    <span><?php echo e($accionLabel); ?></span>
                                                </button>
                                                
                                                <!--[if BLOCK]><![endif]--><?php if($esEnProceso): ?>
                                                    <button type="button" 
                                                        wire:click="finalizarLote(<?php echo e($lote->id_lote); ?>)"
                                                        onclick="return confirm('¿Finalizar este lote? Se liberarán todos los empleados y maquinarias asignadas.')"
                                                        class="inline-flex h-8 w-24 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border text-xs font-bold uppercase text-white transition-all hover:shadow-md"
                                                        style="background-color: #2d7a4f; border-color: #2d7a4f;"
                                                        onmouseover="this.style.backgroundColor='#245c3d'"
                                                        onmouseout="this.style.backgroundColor='#2d7a4f'">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>Finalizar</span>
                                                    </button>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['editar-lotes', 'eliminar-lotes'])): ?>
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                                                    <i class="bi bi-three-dots-vertical text-lg"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                    class="absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-lg border border-slate-100 bg-white shadow-xl ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-lotes')): ?>
                                                        <button wire:click="editar(<?php echo e($lote->id_lote); ?>)" onclick="cambiarAPestanaFormulario()"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                            <i class="bi bi-pencil me-3"></i> Editar
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-lotes')): ?>
                                                        <button wire:click="eliminar(<?php echo e($lote->id_lote); ?>)" onclick="return confirm('¿Está seguro de eliminar este lote?')"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                            <i class="bi bi-trash me-3"></i> Eliminar
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
                                        <i class="bi bi-inbox text-4xl mb-3 block"></i>
                                        <p class="mb-0">No hay lotes registrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>


<!--[if BLOCK]><![endif]--><?php if($mostrarModalRecomendaciones && $modalLoteId): ?>
    <div x-data="{ open: true }" x-show="open" x-transition.opacity
        class="lotes-modal-overlay"
        style="position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.4); display: flex; justify-content: center; align-items: flex-start; backdrop-filter: blur(6px); overflow-y: auto; padding: 2rem;"
        wire:click.self="cerrarModalRecomendaciones">
        <div class="lotes-modal-container" style="width: 100%; max-width: 80rem; margin: auto;">
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="lotes-modal-card"
                    style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); padding: 2rem; max-height: 85vh; display: flex; flex-direction: column;">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6 flex-shrink-0">
                    <div class="space-y-2">
                        <h3 class="text-2xl font-bold text-slate-800">Recomendaciones automáticas</h3>
                        <span class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-sm font-mono text-slate-500">
                            Lote #<?php echo e($modalLoteId); ?>

                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="generarRecomendaciones"
                            class="lotes-modal-btn lotes-modal-btn--primary min-w-[140px]">
                            <i class="bi bi-gear"></i> Generar ahora
                        </button>
                        <button type="button" wire:click="refrescarRecomendaciones"
                            class="lotes-modal-btn lotes-modal-btn--secondary min-w-[120px]">
                            <i class="bi bi-arrow-clockwise"></i> Refrescar
                        </button>
                        <!--[if BLOCK]><![endif]--><?php if(!empty($recomendaciones) && collect($recomendaciones)->where('status', 'draft')->count() > 0): ?>
                            <button type="button" wire:click="eliminarBorradores"
                                wire:confirm="¿Eliminar todas las recomendaciones en borrador?"
                                class="lotes-modal-btn lotes-modal-btn--danger min-w-[140px]">
                                <i class="bi bi-trash"></i> Eliminar borradores
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <button type="button" wire:click="cerrarModalRecomendaciones"
                            class="lotes-modal-btn lotes-modal-btn--ghost px-3">
                            <i class="bi bi-x-lg"></i> Cerrar
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto pt-6 flex-1 min-h-0" style="max-height: calc(85vh - 140px);">
                    <!--[if BLOCK]><![endif]--><?php if($recomendacionesError): ?>
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <?php echo e($recomendacionesError); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php if($recomendacionesMensaje): ?>
                        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            <?php echo e($recomendacionesMensaje); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!--[if BLOCK]><![endif]--><?php if(empty($recomendaciones)): ?>
                        <div class="flex flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 px-6 py-10 text-center">
                            <i class="bi bi-stars text-4xl text-slate-300"></i>
                            <div class="text-sm font-semibold text-slate-600">Sin recomendaciones todavía</div>
                            <p class="max-w-md text-sm text-slate-500">
                                Estamos procesando datos climáticos y de suelo. Podés refrescar en unos minutos o generar manualmente.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full mx-auto text-left text-sm text-slate-600">
                                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">#</th>
                                        <th class="px-4 py-3 font-semibold">Tarea</th>
                                        <th class="px-4 py-3 font-semibold">Estimación</th>
                                        <th class="px-4 py-3 font-semibold">Estado</th>
                                        <th class="px-4 py-3 font-semibold">Creada</th>
                                        <th class="px-4 py-3 text-right font-semibold">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recomendaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $badgeClass = match($rec->status) {
                                                'applied' => 'bg-green-100 text-green-800',
                                                'confirmed' => 'bg-blue-100 text-blue-800',
                                                'closed' => 'bg-slate-100 text-slate-800',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                            $statusLabel = match($rec->status) {
                                                'applied' => 'Aplicada',
                                                'confirmed' => 'Confirmada',
                                                'closed' => 'Cerrada',
                                                'draft' => 'Borrador',
                                                default => ucfirst((string) $rec->status),
                                            };
                                        ?>
                                        <tr class="cursor-pointer hover:bg-slate-50" wire:click="toggleExpand(<?php echo e($rec->id_allocation_proposal); ?>)">
                                            <td class="px-4 py-3 font-semibold text-slate-900">#<?php echo e($rec->id_allocation_proposal); ?></td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-slate-900"><?php echo e($rec->tipo_tarea); ?></div>
                                                <div class="text-xs text-slate-500">Tarea lote: <?php echo e($rec->id_lote_tarea ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="px-4 py-3 text-xs">
                                                <!--[if BLOCK]><![endif]--><?php if($editProposalId === $rec->id_allocation_proposal): ?>
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <label class="text-[11px] text-slate-500">
                                                            Persona-día
                                                            <input type="number" step="0.1" min="0" wire:model.defer="editData.estimated_person_days"
                                                                class="mt-1 w-full rounded border border-slate-200 px-2 py-1 text-xs" />
                                                        </label>
                                                        <label class="text-[11px] text-slate-500">
                                                            Máquina-día
                                                            <input type="number" step="0.1" min="0" wire:model.defer="editData.estimated_machine_days"
                                                                class="mt-1 w-full rounded border border-slate-200 px-2 py-1 text-xs" />
                                                        </label>
                                                        <label class="text-[11px] text-slate-500">
                                                            Duración (días)
                                                            <input type="number" step="0.1" min="0" wire:model.defer="editData.estimated_duration_days"
                                                                class="mt-1 w-full rounded border border-slate-200 px-2 py-1 text-xs" />
                                                        </label>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <label class="text-[11px] text-slate-500">
                                                                Equipo
                                                                <input type="number" min="1" wire:model.defer="editData.suggested_team_size"
                                                                    class="mt-1 w-full rounded border border-slate-200 px-2 py-1 text-xs" />
                                                            </label>
                                                            <label class="text-[11px] text-slate-500">
                                                                Maquinarias
                                                                <input type="number" min="1" wire:model.defer="editData.suggested_machinery_count"
                                                                    class="mt-1 w-full rounded border border-slate-200 px-2 py-1 text-xs" />
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div>Persona-día: <span class="font-semibold"><?php echo e($rec->estimated_person_days ?? 'N/A'); ?></span></div>
                                                    <div>Máquina-día: <span class="font-semibold"><?php echo e($rec->estimated_machine_days ?? 'N/A'); ?></span></div>
                                                    <div>Duración: <span class="font-semibold"><?php echo e($rec->estimated_duration_days ?? 'N/A'); ?></span></div>
                                                    <div>Equipo: <span class="font-semibold"><?php echo e($rec->suggested_team_size ?? 'N/A'); ?></span></div>
                                                    <div>Maquinarias: <span class="font-semibold"><?php echo e($rec->suggested_machinery_count ?? 'N/A'); ?></span></div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($badgeClass); ?>">
                                                    <?php echo e($statusLabel); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-xs text-slate-500">
                                                <?php echo e($rec->created_at); ?>

                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <!--[if BLOCK]><![endif]--><?php if($editProposalId === $rec->id_allocation_proposal): ?>
                                                    <div class="inline-flex items-center gap-2" wire:click.stop>
                                                        <button type="button"
                                                            wire:click.stop="saveEdit(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-blue-700">
                                                            <i class="bi bi-save"></i>
                                                            <span class="ml-2">Guardar</span>
                                                        </button>
                                                        <button type="button"
                                                            wire:click.stop="cancelEdit"
                                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                            <i class="bi bi-x-circle"></i>
                                                            <span class="ml-2">Cancelar</span>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="inline-flex items-center gap-2" wire:click.stop>
                                                        <button type="button"
                                                            wire:click.stop="startEdit(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                            <?php if($rec->status === 'applied'): ?> disabled title="No se pueden editar recomendaciones aplicadas" <?php endif; ?>>
                                                            <i class="bi bi-pencil"></i>
                                                            <span class="ml-2">Editar</span>
                                                        </button>
                                                        <!--[if BLOCK]><![endif]--><?php if($rec->status === 'draft'): ?>
                                                            <button type="button"
                                                                wire:click.stop="eliminarRecomendacion(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                                wire:confirm="¿Eliminar esta recomendación?"
                                                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                                <i class="bi bi-trash"></i>
                                                                <span class="ml-2">Eliminar</span>
                                                            </button>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        <button type="button"
                                                            wire:click.stop="confirmarRecomendacion(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-emerald-700"
                                                            <?php if($rec->status === 'applied'): ?> disabled <?php endif; ?>>
                                                            <i class="bi bi-check2-circle"></i>
                                                            <span class="ml-2">Confirmar</span>
                                                        </button>
                                                    </div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        </tr>
                                        <!--[if BLOCK]><![endif]--><?php if($expandedProposalId === $rec->id_allocation_proposal): ?>
                                            <tr class="bg-slate-50/70">
                                                <td colspan="6" class="px-4 py-4 text-xs text-slate-600">
                                                    <!--[if BLOCK]><![endif]--><?php if($editProposalId === $rec->id_allocation_proposal): ?>
                                                        <!-- MODO EDICIÓN -->
                                                        <div class="space-y-4">
                                                            <!-- Sección Estimaciones (lectura) -->
                                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
                                                                <div>Persona-día estimado: <span class="font-semibold"><?php echo e($rec->estimated_person_days ?? 'N/A'); ?></span></div>
                                                                <div>Máquina-día estimado: <span class="font-semibold"><?php echo e($rec->estimated_machine_days ?? 'N/A'); ?></span></div>
                                                                <div>Duración estimada: <span class="font-semibold"><?php echo e($rec->estimated_duration_days ?? 'N/A'); ?></span></div>
                                                            </div>

                                                            <!-- Sección Empleados Editable -->
                                                            <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3">
                                                                <div class="mb-3 text-xs font-semibold text-blue-700">✎ Empleados sugeridos (editable)</div>
                                                                <!--[if BLOCK]><![endif]--><?php if(empty($rec->proposedEmployees)): ?>
                                                                    <div class="text-slate-500">Sin empleados disponibles.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rec->proposedEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $empRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center gap-3 rounded border border-blue-100 bg-white p-2">
                                                                                <input 
                                                                                    type="checkbox" 
                                                                                    wire:model.live="editingProposals.<?php echo e($rec->id_allocation_proposal); ?>.employees.<?php echo e($idx); ?>.selected"
                                                                                    class="h-4 w-4 rounded border-slate-300"
                                                                                />
                                                                                <div class="flex-1">
                                                                                    <div class="text-sm font-medium text-slate-700">
                                                                                        <?php echo e($empRow['empleado']['apellido'] ?? ''); ?> <?php echo e($empRow['empleado']['nombre'] ?? ''); ?>

                                                                                    </div>
                                                                                    <div class="text-xs text-slate-500">
                                                                                        <?php echo e($empRow['empleado']['rolLaboral']['nombre'] ?? $empRow['rol_sugerido'] ?? 'Rol'); ?>

                                                                                    </div>
                                                                                </div>
                                                                                <!--[if BLOCK]><![endif]--><?php if($empRow['selected'] ?? false): ?>
                                                                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                                                                        <i class="bi bi-check-circle-fill me-1"></i> Seleccionado
                                                                                    </span>
                                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>

                                                            <!-- Sección Maquinarias Editable -->
                                                            <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
                                                                <div class="mb-3 text-xs font-semibold text-purple-700">✎ Maquinarias sugeridas (editable)</div>
                                                                <!--[if BLOCK]><![endif]--><?php if(empty($rec->proposedMaquinarias)): ?>
                                                                    <div class="text-slate-500">Sin maquinarias disponibles.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rec->proposedMaquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $maqRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center gap-3 rounded border border-purple-100 bg-white p-2">
                                                                                <input 
                                                                                    type="checkbox" 
                                                                                    wire:model.live="editProposedMaquinarias.<?php echo e($idx); ?>.selected"
                                                                                    class="h-4 w-4 rounded border-slate-300"
                                                                                />
                                                                                <div class="flex-1">
                                                                                    <div class="text-sm font-medium text-slate-700">
                                                                                        <?php echo e($maqRow['maquinaria']['modelo'] ?? 'Maquinaria'); ?>

                                                                                    </div>
                                                                                    <div class="text-xs text-slate-500">
                                                                                        <?php echo e($maqRow['maquinaria']['tipoMaquinaria']['nombre'] ?? $maqRow['tipo_sugerido'] ?? 'Tipo'); ?>

                                                                                    </div>
                                                                                </div>
                                                                                <!--[if BLOCK]><![endif]--><?php if($maqRow['selected'] ?? false): ?>
                                                                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700">
                                                                                        <i class="bi bi-check-circle-fill me-1"></i> Seleccionada
                                                                                    </span>
                                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>

                                                            <!-- Sección Insumos Editable -->
                                                            <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
                                                                <div class="mb-3 text-xs font-semibold text-green-700">✎ Insumos semana 1 (editable)</div>
                                                                <!--[if BLOCK]><![endif]--><?php if(empty($rec->proposedInsumos)): ?>
                                                                    <div class="text-slate-500">Sin insumos disponibles.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rec->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $insumoRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center gap-3 rounded border border-green-100 bg-white p-2">
                                                                                <input 
                                                                                    type="checkbox" 
                                                                                    wire:model.live="editingProposals.<?php echo e($rec->id_allocation_proposal); ?>.insumos.<?php echo e($idx); ?>.selected"
                                                                                    class="h-4 w-4 rounded border-slate-300"
                                                                                />
                                                                                <div class="flex-1">
                                                                                    <div class="text-sm font-medium text-slate-700">
                                                                                        <?php echo e($insumoRow['insumo']['nombre'] ?? 'Insumo'); ?>

                                                                                    </div>
                                                                                </div>
                                                                                <!--[if BLOCK]><![endif]--><?php if($insumoRow['selected'] ?? false): ?>
                                                                                    <input 
                                                                                        type="number" 
                                                                                        wire:model.defer="editingProposals.<?php echo e($rec->id_allocation_proposal); ?>.insumos.<?php echo e($idx); ?>.cantidad_semana_1"
                                                                                        placeholder="Cant."
                                                                                        step="1"
                                                                                        min="1"
                                                                                        class="w-20 rounded border border-slate-300 px-2 py-1 text-xs"
                                                                                    />
                                                                                    <span class="text-xs text-slate-500">
                                                                                        <?php echo e($insumoRow['insumo']['unidadMedida']['nombre'] ?? ''); ?>

                                                                                    </span>
                                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>

                                                            <!-- Botones de acción -->
                                                            <div class="flex gap-2 border-t border-slate-200 pt-3">
                                                                <button 
                                                                    wire:click="saveEdit(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                                    class="rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                                                >
                                                                    Guardar
                                                                </button>
                                                                <button 
                                                                    wire:click="cancelEdit"
                                                                    class="rounded bg-slate-400 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-500"
                                                                >
                                                                    Cancelar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- MODO LECTURA -->
                                                        <!--[if BLOCK]><![endif]--><?php if($rec->status === 'applied'): ?>
                                                            <!-- Vista cuando está APLICADA -->
                                                            <div class="space-y-4">
                                                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                                                    <div class="mb-2 text-xs font-semibold text-blue-700">✅ Asignación confirmada</div>
                                                                    <div class="text-xs text-slate-600 mb-2">
                                                                        Confirmada: <?php echo e($rec->confirmed_at ? \Carbon\Carbon::parse($rec->confirmed_at)->format('d/m/Y H:i') : 'N/A'); ?>

                                                                    </div>
                                                                    <div class="text-xs text-slate-600">
                                                                        Aplicada: <?php echo e($rec->applied_at ? \Carbon\Carbon::parse($rec->applied_at)->format('d/m/Y H:i') : 'N/A'); ?>

                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                        <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
                                                                        <div class="text-xs">Persona-día: <span class="font-semibold"><?php echo e($rec->estimated_person_days ?? 'N/A'); ?></span></div>
                                                                        <div class="text-xs">Máquina-día: <span class="font-semibold"><?php echo e($rec->estimated_machine_days ?? 'N/A'); ?></span></div>
                                                                        <div class="text-xs">Duración: <span class="font-semibold"><?php echo e($rec->estimated_duration_days ?? 'N/A'); ?></span> días</div>
                                                                    </div>
                                                                </div>

                                                                <!-- Empleados Seleccionados -->
                                                                <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3">
                                                                    <div class="mb-2 text-xs font-semibold text-blue-700">👤 Empleados asignados</div>
                                                                    <?php
                                                                        $empleadosSeleccionados = collect($rec->proposedEmployees ?? [])->where('selected', true);
                                                                    ?>
                                                                    <!--[if BLOCK]><![endif]--><?php if($empleadosSeleccionados->isEmpty()): ?>
                                                                        <div class="text-xs text-slate-500">Sin empleados asignados.</div>
                                                                    <?php else: ?>
                                                                        <div class="space-y-1.5">
                                                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $empleadosSeleccionados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                                                                    <span class="font-medium text-slate-700"><?php echo e($emp->empleado->apellido ?? ''); ?> <?php echo e($emp->empleado->nombre ?? ''); ?></span>
                                                                                    <span class="text-slate-500"><?php echo e($emp->empleado->rolLaboral->nombre ?? 'Rol'); ?></span>
                                                                                </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </div>

                                                                <!-- Maquinarias Seleccionadas -->
                                                                <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
                                                                    <div class="mb-2 text-xs font-semibold text-purple-700">🏗️ Maquinarias asignadas</div>
                                                                    <?php
                                                                        $maquinariasSeleccionadas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                                                                    ?>
                                                                    <!--[if BLOCK]><![endif]--><?php if($maquinariasSeleccionadas->isEmpty()): ?>
                                                                        <div class="text-xs text-slate-500">Sin maquinarias asignadas.</div>
                                                                    <?php else: ?>
                                                                        <div class="space-y-1.5">
                                                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $maquinariasSeleccionadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                                                                    <span class="font-medium text-slate-700"><?php echo e($maq->maquinaria->modelo ?? 'Maquinaria'); ?></span>
                                                                                    <span class="text-slate-500"><?php echo e($maq->maquinaria->tipoMaquinaria->nombre ?? 'Tipo'); ?></span>
                                                                                </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </div>

                                                                <!-- Insumos Asignados -->
                                                                <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
                                                                    <div class="mb-2 text-xs font-semibold text-green-700">📦 Insumos semana 1 (asignados)</div>
                                                                    <?php
                                                                        $insumosSeleccionados = collect($rec->proposedInsumos ?? [])->where('selected', true);
                                                                    ?>
                                                                    <!--[if BLOCK]><![endif]--><?php if($insumosSeleccionados->isEmpty()): ?>
                                                                        <div class="text-xs text-slate-500">Sin insumos asignados.</div>
                                                                    <?php else: ?>
                                                                        <div class="space-y-1.5">
                                                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumosSeleccionados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                                                                    <span class="font-medium text-slate-700"><?php echo e($insumo->insumo->nombre ?? 'Insumo'); ?></span>
                                                                                    <span class="text-slate-500"><?php echo e($insumo->cantidad_semana_1 ?? 'N/A'); ?> <?php echo e($insumo->insumo->unidadMedida->nombre ?? ''); ?></span>
                                                                                </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <!-- Vista cuando NO está aplicada (borrador o confirmada) -->
                                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                <div class="mb-2 text-xs font-semibold text-slate-700">Estimaciones</div>
                                                                <div>Persona-día estimado: <span class="font-semibold"><?php echo e($rec->estimated_person_days ?? 'N/A'); ?></span></div>
                                                                <div>Máquina-día estimado: <span class="font-semibold"><?php echo e($rec->estimated_machine_days ?? 'N/A'); ?></span></div>
                                                                <div>Duración estimada: <span class="font-semibold"><?php echo e($rec->estimated_duration_days ?? 'N/A'); ?></span></div>
                                                            </div>
                                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                <div class="mb-2 text-xs font-semibold text-slate-700">Insumos semana 1</div>
                                                                <!--[if BLOCK]><![endif]--><?php if(empty($rec->proposedInsumos)): ?>
                                                                    <div class="text-slate-500">Sin insumos sugeridos.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $rec->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumoRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <div class="text-slate-700">
                                                                                    <?php echo e($insumoRow->insumo->nombre ?? 'Insumo'); ?>

                                                                                </div>
                                                                                <div class="text-slate-500">
                                                                                    <?php echo e($insumoRow->cantidad_semana_1 ?? 'N/A'); ?>

                                                                                    <?php echo e($insumoRow->insumo->unidadMedida->nombre ?? ''); ?>

                                                                                </div>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                <div class="mb-2 text-xs font-semibold text-slate-700">Empleados sugeridos (libres)</div>
                                                                <?php
                                                                    $empleadosSugeridos = collect($rec->proposedEmployees ?? [])->where('selected', true);
                                                                ?>
                                                                <!--[if BLOCK]><![endif]--><?php if($empleadosSugeridos->isEmpty()): ?>
                                                                    <div class="text-slate-500">Sin empleados sugeridos.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $empleadosSugeridos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <div class="text-slate-700">
                                                                                    <?php echo e($empRow->empleado->apellido ?? ''); ?> <?php echo e($empRow->empleado->nombre ?? ''); ?>

                                                                                </div>
                                                                                <div class="text-slate-500">
                                                                                    <?php echo e($empRow->empleado->rolLaboral->nombre ?? $empRow->rol_sugerido ?? 'Rol'); ?>

                                                                                </div>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                                <div class="mb-2 text-xs font-semibold text-slate-700">Maquinarias sugeridas (libres)</div>
                                                                <?php
                                                                    $maquinariasSugeridas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                                                                ?>
                                                                <!--[if BLOCK]><![endif]--><?php if($maquinariasSugeridas->isEmpty()): ?>
                                                                    <div class="text-slate-500">Sin maquinarias sugeridas.</div>
                                                                <?php else: ?>
                                                                    <div class="space-y-2">
                                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $maquinariasSugeridas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maqRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <div class="text-slate-700">
                                                                                    <?php echo e($maqRow->maquinaria->modelo ?? 'Maquinaria'); ?>

                                                                                </div>
                                                                                <div class="text-slate-500">
                                                                                    <?php echo e($maqRow->maquinaria->tipoMaquinaria->nombre ?? $maqRow->tipo_sugerido ?? 'Tipo'); ?>

                                                                                </div>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                    </div>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                        </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                            </tr>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

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

</div><?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/lotes.blade.php ENDPATH**/ ?>