<div x-data="{ open: <?php if ((object) ('showModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'->value()); ?>')<?php echo e('showModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'); ?>')<?php endif; ?> }" x-cloak>
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40"></div>

    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div>
                    <h2 class="text-xl font-semibold">Launchpad · Lote #<?php echo e($loteId); ?></h2>
                    <p class="text-sm text-slate-500">Recomendaciones precalculadas (Week 1).</p>
                </div>
                <button type="button" wire:click="close" class="rounded-md p-2 text-slate-500 hover:bg-slate-100">✕</button>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                <div class="rounded-xl border p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-600">Team (Top 5)</h3>
                        <span class="text-xs text-slate-500">Seleccionados: <?php echo e(collect($employeeSelected)->filter()->count()); ?></span>
                    </div>
                    <div class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = ($employees ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <label class="flex items-center justify-between gap-3 rounded-lg border px-3 py-2">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="h-4 w-4" wire:model.live="employeeSelected.<?php echo e($row->id_allocation_proposal_employee); ?>">
                                    <div>
                                        <div class="text-sm font-medium"><?php echo e($row->empleado->nombre ?? 'Empleado'); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo e($row->empleado->rolLaboral->nombre ?? 'Sin rol'); ?></div>
                                    </div>
                                </div>
                                <span class="text-xs text-emerald-600">Eficiencia ↑</span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-slate-500">Sin recomendaciones de personal.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-xl border p-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-600">Machinery</h3>
                    <div class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $machinery ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $ocupada = (bool) ($row->maquinaria->ocupada ?? false);
                            ?>
                            <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                                <div>
                                    <div class="text-sm font-medium"><?php echo e($row->maquinaria->nombre ?? 'Maquinaria'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo e($row->maquinaria->tipoMaquinaria->nombre ?? 'Tipo'); ?></div>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?php echo e($ocupada ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                    <?php echo e($ocupada ? 'Ocupada' : 'Libre'); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-slate-500">Sin recomendaciones de maquinaria.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-xl border p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-600">Week 1 Supplies</h3>
                        <div class="text-xs text-slate-500">
                            Fuel: <span class="font-semibold"><?php echo e(number_format($week_1_fuel, 2, ',', '.')); ?></span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $supplies ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                                <div>
                                    <div class="text-sm font-medium"><?php echo e($row->insumo->nombre ?? 'Insumo'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo e($row->insumo->unidadMedida->abreviatura ?? ''); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold"><?php echo e($row->cantidad_semana_1 ?? 0); ?></div>
                                    <div class="text-xs text-slate-500">$<?php echo e(number_format((float) ($row->costo_estimado_semana_1 ?? 0), 0, ',', '.')); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-sm text-slate-500">Sin insumos estimados.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 text-right text-sm">
                        Total semana 1: <span class="font-semibold">$<?php echo e(number_format($supplies_cost, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t px-6 py-4">
                <div class="text-xs text-slate-500">Recomendaciones pre‑calculadas desde histórico.</div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="close" class="rounded-lg border px-4 py-2 text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmAndLaunch" <?php if($guardando): ?> disabled <?php endif; ?>
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        CONFIRM & LAUNCH
                    </button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/launchpad-modal.blade.php ENDPATH**/ ?>