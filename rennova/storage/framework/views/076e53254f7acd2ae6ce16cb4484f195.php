
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['recomendaciones', 'recomendacionesError', 'recomendacionesMensaje', 'modalLoteId', 'editProposalId', 'editData', 'expandedProposalId', 'editingProposals', 'editProposedMaquinarias']));

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

foreach (array_filter((['recomendaciones', 'recomendacionesError', 'recomendacionesMensaje', 'modalLoteId', 'editProposalId', 'editData', 'expandedProposalId', 'editingProposals', 'editProposedMaquinarias']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($modalLoteId): ?>
<div x-data="{ open: true }" x-show="open" x-transition.opacity
    class="lotes-modal-overlay"
    style="position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.4); display: flex; justify-content: center; align-items: flex-start; backdrop-filter: blur(6px); overflow-y: auto; padding: 2rem;"
    wire:click.self="$dispatch('cerrarModalRecomendaciones')">
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
                    <?php if(!empty($recomendaciones) && collect($recomendaciones)->where('status', 'draft')->count() > 0): ?>
                        <button type="button" wire:click="eliminarBorradores"
                            wire:confirm="¿Eliminar todas las recomendaciones en borrador?"
                            class="lotes-modal-btn lotes-modal-btn--danger min-w-[140px]">
                            <i class="bi bi-trash"></i> Eliminar borradores
                        </button>
                    <?php endif; ?>
                    <button type="button" wire:click="$dispatch('cerrarModalRecomendaciones')"
                        class="lotes-modal-btn lotes-modal-btn--ghost px-3">
                        <i class="bi bi-x-lg"></i> Cerrar
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto pt-6 flex-1 min-h-0" style="max-height: calc(85vh - 140px);">
                <?php if($recomendacionesError): ?>
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <?php echo e($recomendacionesError); ?>

                    </div>
                <?php endif; ?>
                <?php if($recomendacionesMensaje): ?>
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        <?php echo e($recomendacionesMensaje); ?>

                    </div>
                <?php endif; ?>

                <?php if(empty($recomendaciones)): ?>
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
                                <?php $__currentLoopData = $recomendaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                    <tr class="cursor-pointer hover:bg-slate-50" wire:click="$dispatch('toggleExpand', { proposalId: <?php echo e($rec->id_allocation_proposal); ?> })">
                                        <td class="px-4 py-3 font-semibold text-slate-900">#<?php echo e($rec->id_allocation_proposal); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-900"><?php echo e($rec->tipo_tarea); ?></div>
                                            <div class="text-xs text-slate-500">Tarea lote: <?php echo e($rec->id_lote_tarea ?? 'N/A'); ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            <?php if($editProposalId === $rec->id_allocation_proposal): ?>
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
                                            <?php endif; ?>
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
                                            <?php if($editProposalId === $rec->id_allocation_proposal): ?>
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
                                                    <?php if($rec->status === 'draft'): ?>
                                                        <button type="button"
                                                            wire:click.stop="eliminarRecomendacion(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                            wire:confirm="¿Eliminar esta recomendación?"
                                                            class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                            <i class="bi bi-trash"></i>
                                                            <span class="ml-2">Eliminar</span>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button"
                                                        wire:click.stop="confirmarRecomendacion(<?php echo e($rec->id_allocation_proposal); ?>)"
                                                        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-emerald-700"
                                                        <?php if($rec->status === 'applied'): ?> disabled <?php endif; ?>>
                                                        <i class="bi bi-check2-circle"></i>
                                                        <span class="ml-2">Confirmar</span>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if($expandedProposalId === $rec->id_allocation_proposal): ?>
                                        <tr class="bg-slate-50/70">
                                            <td colspan="6" class="px-4 py-4 text-xs text-slate-600">
                                                <?php echo $__env->make('components.lotes.proposal-detail', ['rec' => $rec], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/lotes/recomendaciones-modal.blade.php ENDPATH**/ ?>