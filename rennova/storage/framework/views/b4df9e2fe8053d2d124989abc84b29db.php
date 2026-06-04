<div
    class="max-w-7xl mx-auto px-4 py-6 relative"
    x-data="{
        openOverrideModal: false,
        modalError: '',
        requiereOverride: <?php if ((object) ('clima_requiere_override') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_requiere_override'->value()); ?>')<?php echo e('clima_requiere_override'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_requiere_override'); ?>')<?php endif; ?>.live,
        esDiaCaido: <?php if ((object) ('es_dia_caido') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('es_dia_caido'->value()); ?>')<?php echo e('es_dia_caido'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('es_dia_caido'); ?>')<?php endif; ?>.live,
        overrideConfirmado: <?php if ((object) ('clima_override_confirmado') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_override_confirmado'->value()); ?>')<?php echo e('clima_override_confirmado'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_override_confirmado'); ?>')<?php endif; ?>.live,
        overrideMotivo: <?php if ((object) ('clima_override_motivo') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_override_motivo'->value()); ?>')<?php echo e('clima_override_motivo'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('clima_override_motivo'); ?>')<?php endif; ?>.live
    }"
>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><i class="bi bi-clipboard-check mr-3"></i>Partes Diarios</h1>
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

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-partes-diarios', 'editar-partes-diarios'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Parte Diario
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Partes Diarios
        </button>
    </div>

    <!-- Pestaña 1: Nuevo Parte Diario -->
    <?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-partes-diarios', 'editar-partes-diarios'])): ?>
        <div id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab" class="tab-pane-content">
            
            <!-- SECCIÓN 1: Datos Maestros -->
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
                    <h5 class="text-lg font-semibold text-slate-900 mb-0">
                        <i class="bi bi-<?php echo e($parte_id ? 'pencil-square' : 'plus-circle'); ?> mr-2"></i> 
                        <?php echo e($parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario'); ?>

                    </h5>
                </div>
                <div class="p-6">
                    <!-- Fila 1: Lote, Fecha, Día Caído -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione un lote...</option>
                                <?php $__currentLoopData = $this->lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lote->id_lote); ?>"><?php echo e($lote->propietario); ?> - <?php echo e($lote->ubicacion); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div wire:loading wire:target="id_lote" class="text-slate-600 text-sm mt-1">
                                <i class="bi bi-arrow-repeat animate-spin"></i> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tarea del lote <span class="text-red-500">*</span></label>
                            <select wire:model.live="id_lote_tarea" wire:key="lote-tareas-<?php echo e($id_lote); ?>" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['id_lote_tarea'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione una tarea...</option>
                                <?php $__currentLoopData = $this->loteTareas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tarea->id_lote_tarea); ?>">
                                        #<?php echo e($tarea->id_lote_tarea); ?> - <?php echo e($tarea->tipo_tarea_label); ?> (<?php echo e($tarea->estado); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['id_lote_tarea'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php if(!$id_lote): ?>
                                <div class="text-slate-500 text-sm mt-2">Seleccioná un lote para cargar las tareas.</div>
                            <?php elseif($this->loteTareas->isEmpty()): ?>
                                <div class="text-slate-500 text-sm mt-2">Este lote no tiene tareas cargadas.</div>
                            <?php endif; ?>

                            <?php if($id_lote && $this->loteTareas->isEmpty()): ?>
                                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3" wire:key="tarea-rapida-<?php echo e($id_lote); ?>">
                                    <div class="text-sm font-semibold text-slate-800 mb-2">No hay tareas para este lote</div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-end">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
                                            <select wire:model.live="nueva_tarea_tipo_tarea" class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none">
                                                <option value="">Seleccione...</option>
                                                <?php $__currentLoopData = $this->taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taskType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($taskType->value); ?>"><?php echo e($taskType->label()); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sup. (ha)</label>
                                            <input type="number" wire:model.live="nueva_tarea_superficie_afectada_ha" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded text-sm focus:border-green-700 focus:ring-1 focus:ring-green-600 focus:outline-none" placeholder="(opc.)">
                                        </div>
                                        <div>
                                            <button type="button" class="w-full px-3 py-2 bg-green-700 text-white rounded text-sm font-semibold hover:bg-green-800 transition-colors disabled:opacity-50" wire:click.prevent="crearTareaRapida" <?php if(!$nueva_tarea_tipo_tarea): echo 'disabled'; endif; ?>
                                                wire:loading.attr="disabled" wire:target="crearTareaRapida">
                                                <span wire:loading.remove wire:target="crearTareaRapida"><i class="bi bi-plus-circle mr-1"></i>Crear</span>
                                                <span wire:loading wire:target="crearTareaRapida"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   wire:model="fecha" 
                                   max="<?php echo e(date('Y-m-d')); ?>" 
                                   min="<?php echo e(date('Y-m-d', strtotime('-7 days'))); ?>"
                                   class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['fecha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['fecha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Día Caído</label>
                            <div class="flex items-center mt-2">
                                <input type="checkbox" id="diaCaidoSwitch" wire:model.live="es_dia_caido" class="w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-600">
                                <label for="diaCaidoSwitch" class="ml-2">
                                    <span class="inline-block px-3 py-1 rounded text-sm font-medium <?php echo e($es_dia_caido ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-200 text-slate-800'); ?>">
                                        <?php echo e($es_dia_caido ? 'SÍ - Jornal' : 'NO - Destajo'); ?>

                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php if($id_lote && $fecha): ?>
                        <?php
                            $estado = strtoupper($clima_estado ?? 'OPERATIVO');
                            $estadoLabel = $estado === 'INACTIVO'
                                ? 'No operativo'
                                : ($estado === 'OPERATIVO_CONDICIONAL' ? 'Operativo condicional' : 'Operativo');
                            $estadoClass = $estado === 'INACTIVO'
                                ? 'bg-rose-100 text-rose-800'
                                : ($estado === 'OPERATIVO_CONDICIONAL' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                        ?>
                        <div class="mb-6">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold <?php echo e($estadoClass); ?>">
                                        Estado pronostico: <?php echo e($estadoLabel); ?>

                                    </span>
                                    <?php if($clima_es_fin_de_semana): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-slate-200 text-slate-700">
                                            Fin de semana
                                        </span>
                                    <?php endif; ?>
                                    <?php if($clima_fuente === 'fallback'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Fallback: sin datos de API
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if($clima_razon): ?>
                                    <p class="text-sm text-slate-600 mt-2">Motivo: <?php echo e($clima_razon); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endif; ?>

                    <!-- Fila 2: Observaciones -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Observaciones</label>
                            <textarea wire:model="observaciones" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" rows="2" placeholder="Observaciones adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Registro de Producción (Si NO es día caído) -->
            <?php if(!$es_dia_caido): ?>
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-blue-600 text-white px-6 py-4">
                        <h5 class="text-lg font-semibold mb-0"><i class="bi bi-truck mr-2"></i>Registro de Producción</h5>
                    </div>
                    <div class="p-6">
                        <!-- Errores generales de validación de carga -->
                        <?php if($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias')): ?>
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i class="bi bi-exclamation-triangle text-red-600 mt-0.5"></i>
                                    <div>
                                        <strong class="text-red-800">Errores en la carga:</strong>
                                        <ul class="list-disc list-inside mt-2 text-red-700 text-sm">
                                            <?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario agregar carga -->
                        <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                            <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-plus-circle-fill mr-2"></i>Registrar Carga</h6>
                            
                            <!-- Fila 1: Categoría, Maquinaria -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría <span class="text-red-500">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Seleccione...</option>
                                        <?php $__currentLoopData = $this->categoriasMadera; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cat->id_categoria_madera); ?>"><?php echo e($cat->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Maquinarias <span class="text-red-500">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Cargando maquinarias...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border border-default rounded-lg p-3 max-h-32 overflow-y-auto bg-white <?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <?php $__empty_1 = true; $__currentLoopData = $this->maquinariasFiltrada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="flex items-center mb-2">
                                                    <input type="checkbox" value="<?php echo e($maq->id_maquinaria); ?>" id="maq-<?php echo e($maq->id_maquinaria); ?>" wire:model="carga_maquinarias" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                                    <label for="maq-<?php echo e($maq->id_maquinaria); ?>" class="ml-2 text-sm text-slate-700">
                                                        <?php echo e($maq->modelo); ?> - <small class="text-slate-500"><?php echo e($maq->tipoMaquinaria->nombre ?? 'Sin tipo'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <div class="text-slate-500 text-sm p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver maquinarias disponibles
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente con búsqueda -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chofer <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 <?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                                        <?php $__empty_1 = true; $__currentLoopData = $this->choferesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chofer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($chofer->id_chofer); ?>"><?php echo e($chofer->apellido); ?>, <?php echo e($chofer->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="" disabled>No hay resultados</option>
                                        <?php endif; ?>
                                    </select>
                                    <?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Destino (Cliente) <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none mb-2 <?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" size="3">
                                        <?php $__empty_1 = true; $__currentLoopData = $this->clientesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="" disabled>No hay resultados</option>
                                        <?php endif; ?>
                                    </select>
                                    <?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <!-- Fila 3: Pesajes con cálculo reactivo Alpine -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ticket <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="carga_ticket" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="TKT-12345">
                                    <?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Bruto (Ton) <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.live="carga_peso_bruto" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                    <?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tara (Ton) <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.live="carga_tara" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                    <?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-blue-600 mb-2">Neto (Ton) <span class="text-sm">Calculado</span></label>
                                    <input type="text" value="<?php echo e(is_numeric($carga_peso_neto) ? number_format((float) $carga_peso_neto, 2, '.', '') : '0.00'); ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-100 text-slate-700" readonly>
                                    <?php $__errorArgs = ['carga_peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1 block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <!-- Fila 4: Empleados -->
                            <div class="grid grid-cols-1 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Empleados <span class="text-red-500">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Cargando empleados...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border border-default rounded-lg p-3 max-h-40 overflow-y-auto bg-white <?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <?php $__empty_1 = true; $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="flex items-center mb-2">
                                                    <input type="checkbox" value="<?php echo e($emp->id_empleado); ?>" id="emp-<?php echo e($emp->id_empleado); ?>" wire:model="carga_empleados" class="w-4 h-4 rounded border-slate-300 text-green-600">
                                                    <label for="emp-<?php echo e($emp->id_empleado); ?>" class="ml-2 text-sm text-slate-700">
                                                        <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <small class="text-slate-500"><?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <div class="text-slate-500 text-sm p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver empleados disponibles
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" wire:click.prevent="agregarCarga" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarCarga">
                                    <span wire:loading.remove wire:target="agregarCarga"><i class="bi bi-plus-circle mr-2"></i>Agregar Carga</span>
                                    <span wire:loading wire:target="agregarCarga"><i class="bi bi-arrow-repeat animate-spin mr-2"></i>Agregando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Listado cargas -->
                        <?php if(count($cargas) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-sm">
                                    <thead class="bg-slate-100 border-b border-slate-300">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Ticket</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Categoría</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Neto (Ton)</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Chofer</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Destino</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Empleados</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Maquinarias</th>
                                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $cargas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $carga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                                <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded"><?php echo e($carga['ticket']); ?></span></td>
                                                <td class="px-4 py-2">
                                                    <?php
                                                        $cat = $this->categoriasMadera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                                    ?>
                                                    <?php echo e($cat->nombre ?? '-'); ?>

                                                </td>
                                                <td class="px-4 py-2 font-semibold"><?php echo e(number_format($carga['peso_neto'], 2)); ?></td>
                                                <td class="px-4 py-2">
                                                    <?php
                                                        $chofer = $this->choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                                    ?>
                                                    <?php echo e($chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-'); ?>

                                                </td>
                                                <td class="px-4 py-2"><?php echo e($carga['destino_nombre'] ?? '-'); ?></td>
                                                <td class="px-4 py-2"><small><?php echo e(count($carga['empleados'] ?? [])); ?> emp</small></td>
                                                <td class="px-4 py-2"><small><?php echo e(count($carga['maquinarias'] ?? [])); ?> maq</small></td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" wire:click.prevent="eliminarCarga(<?php echo e($index); ?>)" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                    <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                                        <tr>
                                            <td colspan="7" class="px-4 py-2 text-right">Total:</td>
                                            <td class="px-4 py-2 text-center text-blue-600"><?php echo e(number_format($total_toneladas, 2)); ?> Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm">
                                <i class="bi bi-info-circle mr-2"></i> Sin cargas registradas. Agregue al menos una carga.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- SECCIÓN 3: Jornales (Si ES día caído) -->
            <?php if($es_dia_caido): ?>
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-yellow-500 text-slate-900 px-6 py-4">
                        <h5 class="text-lg font-semibold mb-0"><i class="bi bi-cash-coin mr-2"></i>Asignación de Jornales</h5>
                    </div>
                    <div class="p-6">
                        <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                            <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-person-plus-fill mr-2"></i>Agregar Empleado al Jornal</h6>
                            <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end">
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Empleado <span class="text-red-500">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['jornal_id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Seleccione...</option>
                                        <?php $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($emp->id_empleado); ?>">
                                                <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?>

                                                <?php if(isset($jornal_por_empleado[$emp->id_empleado])): ?>
                                                    (Jornal: $<?php echo e(number_format($jornal_por_empleado[$emp->id_empleado], 2)); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['jornal_id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="md:col-span-2">
                                    <button type="button" wire:click.prevent="agregarJornal" class="w-full px-6 py-3 bg-yellow-500 text-slate-900 rounded-lg font-semibold hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarJornal">
                                        <span wire:loading.remove wire:target="agregarJornal"><i class="bi bi-plus-circle mr-2"></i>Agregar</span>
                                        <span wire:loading wire:target="agregarJornal"><i class="bi bi-arrow-repeat animate-spin mr-2"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if(count($jornales) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-sm">
                                    <thead class="bg-slate-100 border-b border-slate-300">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Empleado</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Rol</th>
                                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Jornal</th>
                                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $jornales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $jornal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                                <td class="px-4 py-2"><?php echo e($jornal['nombre_completo'] ?? '-'); ?></td>
                                                <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded"><?php echo e($jornal['rol'] ?? '-'); ?></span></td>
                                                <td class="px-4 py-2 text-green-600 font-bold">$<?php echo e(number_format($jornal['jornal_diario'] ?? 0, 2)); ?></td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" wire:click.prevent="eliminarJornal(<?php echo e($index); ?>)" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                    <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-right">Total Jornales:</td>
                                            <td class="px-4 py-2 text-center text-yellow-600">$<?php echo e(number_format(array_sum(array_column($jornales, 'jornal_diario')), 2)); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                                <i class="bi bi-info-circle mr-2"></i> Sin empleados asignados. Agregue al menos un empleado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- SECCIÓN 4: Movimientos de Insumos -->
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold mb-0"><i class="bi bi-box-seam mr-2"></i>Movimientos de Insumos</h5>
                </div>
                <div class="p-6">
                    <div id="alertaMovimiento"></div>
                    
                    <!-- Errores generales de validación de movimiento -->
                    <?php if($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo')): ?>
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <i class="bi bi-exclamation-triangle text-red-600 mt-0.5"></i>
                                <div>
                                    <strong class="text-red-800">Errores en el movimiento:</strong>
                                    <ul class="list-disc list-inside mt-2 text-red-700 text-sm">
                                        <?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                        <h6 class="font-semibold mb-4 text-slate-900"><i class="bi bi-box-arrow-right mr-2"></i>Registrar Consumo</h6>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Insumo <span class="text-red-500">*</span></label>
                                <select wire:model.live="movimiento_id_insumo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <?php $__currentLoopData = $this->insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($insumo->id_insumo); ?>"><?php echo e($insumo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php if($stock_disponible_insumo !== null): ?>
                                    <small class="text-slate-600 mt-1 block">
                                        Stock disponible: <strong class="<?php echo e($stock_disponible_insumo > 0 ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($stock_disponible_insumo); ?></strong>
                                    </small>
                                <?php endif; ?>
                                <?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Cantidad <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="movimiento_cantidad" step="0.1" min="0" 
                                       <?php if($stock_disponible_insumo !== null): ?> max="<?php echo e($stock_disponible_insumo); ?>" <?php endif; ?>
                                       class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                <?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Motivo <span class="text-red-500">*</span></label>
                                <select wire:model="movimiento_motivo" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none <?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="Producción">Producción</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                    <option value="Varios">Varios</option>
                                </select>
                                <?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click.prevent="agregarMovimiento" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                                    <span wire:loading.remove wire:target="agregarMovimiento"><i class="bi bi-plus-circle mr-2"></i>Agregar</span>
                                    <span wire:loading wire:target="agregarMovimiento"><i class="bi bi-arrow-repeat animate-spin mr-2"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if(count($movimientos) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-slate-100 border-b border-slate-300">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Insumo</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Cantidad</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Motivo</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Observaciones</th>
                                        <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                                            <td class="px-4 py-2"><strong><?php echo e($mov['nombre_insumo']); ?></strong></td>
                                            <td class="px-4 py-2"><?php echo e(number_format($mov['cantidad'], 2)); ?> <?php echo e($mov['unidad'] ?? ''); ?></td>
                                            <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded"><?php echo e($mov['motivo']); ?></span></td>
                                            <td class="px-4 py-2"><small><?php echo e($mov['observaciones'] ?? '-'); ?></small></td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" wire:click.prevent="eliminarMovimiento(<?php echo e($index); ?>)" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 text-sm">
                            <i class="bi bi-info-circle mr-2"></i> Sin movimientos registrados. Esta sección es opcional.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click.prevent="cancelarEdicion" class="px-8 py-3 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle mr-2"></i>Cancelar
                        </button>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-partes-diarios', 'editar-partes-diarios'])): ?>
                        <button
    type="button"
    @click.prevent="
        if (!esDiaCaido && requiereOverride && !overrideConfirmado) {
            modalError = '';
            openOverrideModal = true;
        } else {
            $wire.guardar();
        }
    "
    class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    wire:loading.attr="disabled"
    wire:target="guardar"
>
    <span wire:loading.remove wire:target="guardar"><i class="bi bi-check-circle mr-2"></i>Guardar Parte Diario</span>
    <span wire:loading wire:target="guardar"><i class="bi bi-arrow-repeat animate-spin mr-2"></i>Guardando...</span>
</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php echo $__env->make('livewire.partials.clima-override-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($tab_activo === 'listado'): ?>
        <?php echo $__env->make('livewire.partials.partes-listado', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/partes-diarios.blade.php ENDPATH**/ ?>