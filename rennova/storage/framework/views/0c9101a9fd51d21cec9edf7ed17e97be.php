
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rec']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['rec']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($editProposalId === $rec->id_allocation_proposal): ?>
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
            <?php if(empty($rec->proposedEmployees)): ?>
                <div class="text-slate-500">Sin empleados disponibles.</div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $rec->proposedEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $empRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($empRow['selected'] ?? false): ?>
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                    ✅  Seleccionado
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección Maquinarias Editable -->
        <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
            <div class="mb-3 text-xs font-semibold text-purple-700">✎ Maquinarias sugeridas (editable)</div>
            <?php if(empty($rec->proposedMaquinarias)): ?>
                <div class="text-slate-500">Sin maquinarias disponibles.</div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $rec->proposedMaquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $maqRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($maqRow['selected'] ?? false): ?>
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700">
                                    ✅  Seleccionada
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección Insumos Editable -->
        <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
            <div class="mb-3 text-xs font-semibold text-green-700">✎ Insumos semana 1 (editable)</div>
            <?php if(empty($rec->proposedInsumos)): ?>
                <div class="text-slate-500">Sin insumos disponibles.</div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $rec->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $insumoRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <?php if($insumoRow['selected'] ?? false): ?>
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
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
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
    <?php if($rec->status === 'applied'): ?>
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
                <?php if($empleadosSeleccionados->isEmpty()): ?>
                    <div class="text-xs text-slate-500">Sin empleados asignados.</div>
                <?php else: ?>
                    <div class="space-y-1.5">
                        <?php $__currentLoopData = $empleadosSeleccionados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700"><?php echo e($emp->empleado->apellido ?? ''); ?> <?php echo e($emp->empleado->nombre ?? ''); ?></span>
                                <span class="text-slate-500"><?php echo e($emp->empleado->rolLaboral->nombre ?? 'Rol'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Maquinarias Seleccionadas -->
            <div class="rounded-lg border border-purple-200 bg-purple-50/50 p-3">
                <div class="mb-2 text-xs font-semibold text-purple-700">🏗️ Maquinarias asignadas</div>
                <?php
                    $maquinariasSeleccionadas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                ?>
                <?php if($maquinariasSeleccionadas->isEmpty()): ?>
                    <div class="text-xs text-slate-500">Sin maquinarias asignadas.</div>
                <?php else: ?>
                    <div class="space-y-1.5">
                        <?php $__currentLoopData = $maquinariasSeleccionadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700"><?php echo e($maq->maquinaria->modelo ?? 'Maquinaria'); ?></span>
                                <span class="text-slate-500"><?php echo e($maq->maquinaria->tipoMaquinaria->nombre ?? 'Tipo'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Insumos Asignados -->
            <div class="rounded-lg border border-green-200 bg-green-50/50 p-3">
                <div class="mb-2 text-xs font-semibold text-green-700">📦 Insumos semana 1 (asignados)</div>
                <?php
                    $insumosSeleccionados = collect($rec->proposedInsumos ?? [])->where('selected', true);
                ?>
                <?php if($insumosSeleccionados->isEmpty()): ?>
                    <div class="text-xs text-slate-500">Sin insumos asignados.</div>
                <?php else: ?>
                    <div class="space-y-1.5">
                        <?php $__currentLoopData = $insumosSeleccionados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-2 rounded bg-white px-2 py-1.5 text-xs">
                                <span class="font-medium text-slate-700"><?php echo e($insumo->insumo->nombre ?? 'Insumo'); ?></span>
                                <span class="text-slate-500"><?php echo e($insumo->cantidad_semana_1 ?? 'N/A'); ?> <?php echo e($insumo->insumo->unidadMedida->nombre ?? ''); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
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
                <?php if(empty($rec->proposedInsumos)): ?>
                    <div class="text-slate-500">Sin insumos sugeridos.</div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $rec->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumoRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    <?php echo e($insumoRow->insumo->nombre ?? 'Insumo'); ?>

                                </div>
                                <div class="text-slate-500">
                                    <?php echo e($insumoRow->cantidad_semana_1 ?? 'N/A'); ?>

                                    <?php echo e($insumoRow->insumo->unidadMedida->nombre ?? ''); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Empleados sugeridos (libres)</div>
                <?php
                    $empleadosSugeridos = collect($rec->proposedEmployees ?? [])->where('selected', true);
                ?>
                <?php if($empleadosSugeridos->isEmpty()): ?>
                    <div class="text-slate-500">Sin empleados sugeridos.</div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $empleadosSugeridos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    <?php echo e($empRow->empleado->apellido ?? ''); ?> <?php echo e($empRow->empleado->nombre ?? ''); ?>

                                </div>
                                <div class="text-slate-500">
                                    <?php echo e($empRow->empleado->rolLaboral->nombre ?? $empRow->rol_sugerido ?? 'Rol'); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-2 text-xs font-semibold text-slate-700">Maquinarias sugeridas (libres)</div>
                <?php
                    $maquinariasSugeridas = collect($rec->proposedMaquinarias ?? [])->where('selected', true);
                ?>
                <?php if($maquinariasSugeridas->isEmpty()): ?>
                    <div class="text-slate-500">Sin maquinarias sugeridas.</div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $maquinariasSugeridas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maqRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-700">
                                    <?php echo e($maqRow->maquinaria->modelo ?? 'Maquinaria'); ?>

                                </div>
                                <div class="text-slate-500">
                                    <?php echo e($maqRow->maquinaria->tipoMaquinaria->nombre ?? $maqRow->tipo_sugerido ?? 'Tipo'); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/lotes/proposal-detail.blade.php ENDPATH**/ ?>