<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-tools"></i> Mantenimientos
        </h1>
        <div class="flex items-center gap-2">
            <button type="button"
                wire:click="ejecutarFlujoPresentacion"
                class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-200">
                <i class="bi bi-play-circle"></i> Ejecutar flujo presentacion
            </button>
        </div>
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-mantenimientos', 'editar-mantenimientos'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Mantenimientos
        </button>
    </div>

    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-mantenimientos', 'editar-mantenimientos'])): ?>
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-<?php echo e($mantenimiento_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($mantenimiento_id ? 'Editar Orden' : 'Nueva Orden de Mantenimiento'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <!-- Alerta para tipo preventivo -->
                    <?php if(count($kitPreventivo) > 0): ?>
                        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700">
                            <h6 class="mb-2 flex items-center gap-2 font-semibold">
                                <i class="bi bi-box-seam"></i> Kit de Mantenimiento Preventivo
                            </h6>
                            <small>Se utilizarán los siguientes insumos del kit configurado:</small>
                            <ul class="mb-0 mt-2 list-inside space-y-1 text-sm">
                                <?php $__currentLoopData = $kitPreventivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($item['nombre'] ?? 'N/A'); ?>: <?php echo e(number_format($item['cantidad_requerida'], 2)); ?> unidades</li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php elseif($id_maquinaria && $id_tipo_mantenimiento): ?>
                        <?php
                            $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                            $esPreventivo = $tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo');
                        ?>
                        <?php if($esPreventivo): ?>
                            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-700">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Advertencia:</strong> No hay kit de mantenimiento preventivo configurado para esta maquinaria.
                                <a href="/kits-mantenimiento" class="font-semibold hover:underline">Configurar kit</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Maquinaria <span class="text-red-500">*</span></label>
                                <select wire:model.live="id_maquinaria" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maquinaria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($maquinaria->id_maquinaria); ?>">
                                            <?php echo e($maquinaria->modelo); ?> - <?php echo e($maquinaria->tipoMaquinaria?->nombre ?? 'N/A'); ?>

                                            <?php if($maquinaria->umbral_toneladas): ?>
                                                (<?php echo e(number_format($maquinaria->toneladas_acumuladas ?? 0, 0)); ?>/<?php echo e(number_format($maquinaria->umbral_toneladas, 0)); ?> ton)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Mantenimiento <span class="text-red-500">*</span></label>
                                <select wire:model.live="id_tipo_mantenimiento" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['id_tipo_mantenimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tipo->id_tipo_mantenimiento); ?>"><?php echo e($tipo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_tipo_mantenimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php if($id_tipo_mantenimiento): ?>
                                    <?php
                                        $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                                    ?>
                                    <?php if($tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo')): ?>
                                        <small class="text-blue-600 text-xs mt-1 block">
                                            <i class="bi bi-info-circle"></i> Se utilizará el kit de mantenimiento preventivo
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="fecha_inicio" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Programada</label>
                                <input type="date" wire:model="fecha_programada" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_programada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_programada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-slate-500 text-xs mt-1 block">
                                    <i class="bi bi-info-circle"></i> Debe estar dentro de los próximos 7 días (<?php echo e(\Carbon\Carbon::now()->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::now()->addDays(7)->format('d/m/Y')); ?>)
                                </small>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Estado <span class="text-red-500">*</span></label>
                            <select wire:model="estado" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="programado">Programado</option>
                                <option value="en curso">En Curso</option>
                            </select>
                            <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-slate-500 text-xs mt-1 block">La orden se completará desde el listado</small>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <?php if($mantenimiento_id): ?>
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-mantenimientos', 'editar-mantenimientos'])): ?>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> <?php echo e($mantenimiento_id ? 'Actualizar' : 'Crear Orden'); ?>

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
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por maquinaria, tipo, estado o costo..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Maquinaria</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Tipo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Inicio</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Programada</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Fin</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Costo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php $__empty_1 = true; $__currentLoopData = $mantenimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mantenimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($mantenimiento->id_mantenimiento); ?></span></td>
                                        <td class="px-3 py-3 font-semibold text-slate-800"><?php echo e($mantenimiento->maquinaria?->modelo ?? 'N/A'); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($mantenimiento->tipoMantenimiento?->nombre ?? 'N/A'); ?></td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A'); ?></td>
                                        <td class="px-3 py-3">
                                            <?php if($mantenimiento->fecha_programada): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    <?php echo e(\Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y')); ?>

                                                </span>
                                                <?php if($mantenimiento->estado === 'programado' && \Carbon\Carbon::parse($mantenimiento->fecha_programada)->isToday()): ?>
                                                    <i class="bi bi-exclamation-circle text-amber-500 ml-1" title="Programado para hoy"></i>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600"><?php echo e($mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A'); ?></td>
                                        <td class="px-3 py-3 text-right text-slate-600">$<?php echo e(number_format($mantenimiento->costo_total, 2, ',', '.')); ?></td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo e($mantenimiento->estado == 'completado' ? 'bg-green-50 text-green-700 border border-green-200' : 
                                                   ($mantenimiento->estado == 'en curso' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 
                                                   ($mantenimiento->estado == 'vencido' ? 'bg-red-50 text-red-700 border border-red-200' : 
                                                   'bg-slate-100 text-slate-600 border border-slate-200'))); ?>">
                                                <?php echo e(ucfirst($mantenimiento->estado)); ?>

                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <?php 
                                                $isCompletado = strtolower(trim($mantenimiento->estado ?? '')) === 'completado';
                                                $isProgramado = strtolower(trim($mantenimiento->estado ?? '')) === 'programado';
                                                $isVencido = strtolower(trim($mantenimiento->estado ?? '')) === 'vencido';
                                            ?>
                                            <div class="flex gap-1 justify-center">
                                                <?php if($isProgramado): ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('confirmar-mantenimiento')): ?>
                                                    <button type="button" wire:click="confirmarMantenimiento(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Confirmar realización" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                        <i class="bi bi-check2-circle text-sm"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if($isVencido): ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reprogramar-mantenimiento')): ?>
                                                    <button type="button" wire:click="reprogramarMantenimiento(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Reprogramar" class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded transition-colors border border-amber-200">
                                                        <i class="bi bi-calendar-plus text-sm"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-mantenimientos')): ?>
                                                <button type="button" wire:click.prevent="abrirModalCompletar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Completar" class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded transition-colors border border-green-200 <?php echo e(($isCompletado || $isVencido) ? 'opacity-50 cursor-not-allowed' : ''); ?>" <?php if($isCompletado || $isVencido): ?> disabled <?php endif; ?>>
                                                    <i class="bi bi-check-circle text-sm"></i>
                                                </button>
                                                <button type="button" wire:click="editar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Editar" class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded transition-colors border border-purple-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-mantenimientos')): ?>
                                                <button type="button" wire:click="eliminar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" onclick="return confirm('¿Está seguro de eliminar este mantenimiento?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="px-3 py-8 text-center">
                                            <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-slate-500 font-medium">No hay mantenimientos registrados.</p>
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

    <!-- CSS y Modal - preservados del original -->
    <?php if (! $__env->hasRenderedOnce('a87397e3-1514-403c-9ff6-21fee128dc71')): $__env->markAsRenderedOnce('a87397e3-1514-403c-9ff6-21fee128dc71'); ?>
        <style>
            .lw-modal-overlay {
                position: fixed;
                inset: 0;
                background-color: rgba(15, 23, 42, 0.6);
                z-index: 2050;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 2rem 1rem;
                overflow-y: auto;
            }
            .lw-modal-card {
                width: min(900px, 100%);
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
                overflow: hidden;
            }
            .lw-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.5rem;
                background: #198754;
                color: #fff;
            }
            .lw-modal-body { padding: 1.5rem; }
            .lw-modal-footer {
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: .75rem;
                background: #f8f9fa;
            }
            .lw-close {
                background: transparent;
                border: none;
                color: inherit;
                font-size: 1.25rem;
                line-height: 1;
                cursor: pointer;
            }
        </style>
    <?php endif; ?>

    <?php if($mostrarModalCompletar): ?>
        <div class="lw-modal-overlay" wire:key="modal-overlay">
            <div class="lw-modal-card" wire:key="modal-card-<?php echo e($orden_completar_id); ?>">
                <div class="lw-modal-header" style="background-color: #2d7a4f;">
                    <h5 class="mb-0 flex items-center gap-2"><i class="bi bi-check-circle"></i> Completar Orden de Mantenimiento</h5>
                    <button type="button" class="lw-close" wire:click="cerrarModalCompletar" aria-label="Cerrar">&times;</button>
                </div>
                <div class="lw-modal-body">
                    <?php if(session()->has('error')): ?>
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 flex items-start gap-3">
                            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                            <div class="flex-1"><?php echo e(session('error')); ?></div>
                            <button type="button" class="text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php $__errorArgs = ['general'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 flex items-start gap-3">
                            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                            <div class="flex-1"><?php echo e($message); ?></div>
                            <button type="button" class="text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700 text-sm">
                        <strong>Orden #<?php echo e($orden_completar_info['id'] ?? 'N/A'); ?></strong><br>
                        <strong>Maquinaria:</strong> <?php echo e($orden_completar_info['maquinaria'] ?? 'N/A'); ?><br>
                        <strong>Tipo:</strong> <?php echo e($orden_completar_info['tipo'] ?? 'N/A'); ?><br>
                        <strong>Fecha Inicio:</strong> <?php echo e(isset($orden_completar_info['fecha_inicio']) ? \Carbon\Carbon::parse($orden_completar_info['fecha_inicio'])->format('d/m/Y') : 'N/A'); ?>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Finalización <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_fin_completar" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_fin_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha_fin_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Costo Total (opcional)</label>
                            <div class="flex items-center gap-2">
                                <span class="px-4 py-3 bg-slate-100 text-slate-600 rounded-lg border border-slate-300">$</span>
                                <input type="number" wire:model="costo_total_completar" step="1" min="0" class="flex-1 px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['costo_total_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                            </div>
                            <?php $__errorArgs = ['costo_total_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-slate-500 text-xs mt-1 block">Costo adicional (ej: mano de obra). Los insumos se calculan automáticamente con FIFO</small>
                        </div>
                    </div>

                    <hr class="my-6 border-slate-200">
                    <h6 class="mb-4 font-semibold text-slate-700 flex items-center gap-2">
                        <i class="bi bi-box-seam"></i> Insumos Utilizados 
                        <?php if(!$orden_es_correctivo): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">Kit Preventivo</span>
                        <?php endif; ?>
                    </h6>

                    <?php $__currentLoopData = $insumos_usados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Insumo</label>
                                <select wire:model.live="insumos_usados.<?php echo e($index); ?>.id_insumo" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors text-sm">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = \App\Models\Insumo::orderBy('nombre')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $stockDisp = \App\Models\MovimientoStock::stockDisponible($ins->id_insumo);
                                            $precioProm = \App\Models\MovimientoStock::precioPromedio($ins->id_insumo);
                                        ?>
                                        <option value="<?php echo e($ins->id_insumo); ?>">
                                            <?php echo e($ins->nombre); ?> (Stock: <?php echo e(number_format($stockDisp, 2)); ?> - $<?php echo e(number_format($precioProm, 2)); ?>/u)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Cantidad a usar</label>
                                <input type="number" wire:model="insumos_usados.<?php echo e($index); ?>.cantidad" step="0.1" min="0" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors" placeholder="0">
                                <?php if(!empty($insumo['id_insumo'])): ?>
                                    <?php
                                        $stockDisponible = \App\Models\MovimientoStock::stockDisponible($insumo['id_insumo']);
                                    ?>
                                    <small class="text-slate-500 text-xs mt-1 block">Disponible: <?php echo e(number_format($stockDisponible, 2)); ?></small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Precio Unitario</label>
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-600">$</span>
                                    <input type="number" wire:model="insumos_usados.<?php echo e($index); ?>.precio_unitario" step="0.01" min="0" class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors" readonly style="background-color: #f8fafc;">
                                </div>
                            </div>
                            <div class="flex items-end justify-end">
                                <?php if($index > 0 || count($insumos_usados) > 1): ?>
                                    <button type="button" wire:click="eliminarInsumo(<?php echo e($index); ?>)" class="inline-flex items-center px-2 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200" title="Eliminar">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <button type="button" wire:click="agregarInsumo" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200 font-medium text-sm mt-4">
                        <i class="bi bi-plus-circle"></i> Agregar Insumo
                    </button>
                </div>
                <div class="lw-modal-footer">
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white hover:bg-slate-700 rounded transition-colors font-medium text-sm" wire:click="cerrarModalCompletar">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'" wire:click="completarOrden" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="completarOrden">
                            <i class="bi bi-check-circle"></i> Completar Orden
                        </span>
                        <span wire:loading wire:target="completarOrden">
                            <svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
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

        window.addEventListener('modal-completar-opened', (e) => {
            console.log('[Livewire] Modal Completar abierto para ID:', e.detail?.id);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/mantenimientos.blade.php ENDPATH**/ ?>