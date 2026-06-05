<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <?php if(!empty($loteId)): ?>
        <div class="flex flex-wrap justify-between items-center mb-4">
            <div>
                <h4 class="text-xl font-bold text-slate-900">✨ Recomendaciones del Lote #<?php echo e($loteId); ?></h4>
                <div class="text-slate-500 text-sm">Al pasar el lote a <strong>en proceso</strong> se generan estas propuestas.</div>
            </div>
            <div class="flex gap-2">
                <a class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" href="<?php echo e(route('lotes.index')); ?>">
                    ← Volver a Lotes
                </a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-brand bg-white text-brand rounded-lg text-sm font-medium hover:bg-brand/5 transition-colors" wire:click="generarAhora" <?php if($guardando): ?> disabled <?php endif; ?>>
                    ⚙️ Generar ahora
                </button>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="refreshProposals" <?php if($guardando): ?> disabled <?php endif; ?>>
                    ↻ Refrescar
                </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex border-b border-slate-200 mb-6" role="tablist">
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e($mostrar_listado ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', true)"
        >
            ⚡ Propuestas
        </button>
        <button
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo e(!$mostrar_listado ? 'border-brand text-brand' : 'border-transparent text-slate-500 hover:text-slate-700'); ?>"
            type="button"
            role="tab"
            wire:click="$set('mostrar_listado', false)"
            <?php if(!$selected_proposal_id): ?> disabled <?php endif; ?>
        >
            📋 Detalle / Confirmar
        </button>
    </div>

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

    <div>
        <div class="<?php echo e($mostrar_listado ? '' : 'hidden'); ?>">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">✨ Propuestas Automáticas</h5>
                    <div class="flex gap-2">
                        <?php if(empty($loteId)): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                            <button class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="refreshProposals" <?php if($guardando): ?> disabled <?php endif; ?>>
                                ↻ Refrescar
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-6">
                    <?php if(empty($loteId)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lote</label>
                            <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filter_lote_id">
                                <option value="">Todos</option>
                                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($l->id_lote); ?>" wire:key="option-<?php echo e($l->id_lote); ?>">Lote #<?php echo e($l->id_lote); ?> - <?php echo e($l->ubicacion); ?> (<?php echo e($l->estado); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                            <select class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20" wire:model.live="filter_status">
                                <option value="">Todos</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="applied">Applied</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="text-slate-500 text-sm">
                                Mostrando <?php echo e(is_countable($proposals) ? count($proposals) : 0); ?> propuestas
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tarea</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estimación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Creada</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__empty_1 = true; $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr wire:key="row-<?php echo e($p->id_allocation_proposal); ?>" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5"><strong>#<?php echo e($p->id_allocation_proposal); ?></strong></td>
                                        <td class="px-4 py-2.5">
                                            <div><strong>Lote #<?php echo e($p->id_lote); ?></strong></div>
                                            <div class="text-slate-500 text-xs"><?php echo e($p->lote->ubicacion ?? ''); ?></div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e($p->tipo_tarea); ?></span>
                                            <?php if($p->id_lote_tarea): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700">Tarea #<?php echo e($p->id_lote_tarea); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs">
                                            <div>Persona-día: <strong><?php echo e($p->estimated_person_days ?? 'N/A'); ?></strong></div>
                                            <div>Máquina-día: <strong><?php echo e($p->estimated_machine_days ?? 'N/A'); ?></strong></div>
                                            <div>Duración: <strong><?php echo e($p->estimated_duration_days ?? 'N/A'); ?></strong></div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <?php
                                                $badge = match($p->status) {
                                                    'applied' => 'bg-emerald-100 text-emerald-700',
                                                    'confirmed' => 'bg-brand/10 text-brand',
                                                    default => 'bg-amber-100 text-amber-700'
                                                };
                                            ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($badge); ?>"><?php echo e($p->status); ?></span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs text-slate-500"><?php echo e($p->created_at); ?></td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-brand bg-white text-brand rounded-lg text-xs font-medium hover:bg-brand/5 transition-colors" wire:click="seleccionar(<?php echo e($p->id_allocation_proposal); ?>)">
                                                👁️ Ver
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-12 text-slate-400">
                                            <div class="text-5xl mb-2">📥</div>
                                            <p>No hay propuestas para los filtros seleccionados.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mt-4">
                        <span>ℹ️</span>
                        <small>
                            Estas propuestas se generan en base a histórico (persona-día / máquina-día). Podés confirmar y aplicar para cargar asignaciones del lote.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="<?php echo e(!$mostrar_listado ? '' : 'hidden'); ?>">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">📋 Detalle de Propuesta</h5>
                    <div class="flex gap-2">
                        <button class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="volver">
                            ← Volver
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <?php if(!$selectedProposal): ?>
                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                            <small>Seleccione una propuesta desde la pestaña "Propuestas".</small>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="border border-slate-200 rounded-lg p-4 bg-white">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="font-semibold">Propuesta #<?php echo e($selectedProposal->id_allocation_proposal); ?></div>
                                        <div class="text-slate-500 text-xs">Lote #<?php echo e($selectedProposal->id_lote); ?> - <?php echo e($selectedProposal->lote->ubicacion ?? ''); ?></div>
                                    </div>
                                    <div>
                                        <?php
                                            $badge = match($selectedProposal->status) {
                                                'applied' => 'bg-emerald-100 text-emerald-700',
                                                'confirmed' => 'bg-brand/10 text-brand',
                                                default => 'bg-amber-100 text-amber-700'
                                            };
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($badge); ?>"><?php echo e($selectedProposal->status); ?></span>
                                    </div>
                                </div>

                                <hr class="border-slate-200 my-2">
                                <div class="text-sm">
                                    <div>Tarea: <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e($selectedProposal->tipo_tarea); ?></span></div>
                                    <div>Especie: <strong><?php echo e($selectedProposal->especie ?? 'N/A'); ?></strong></div>
                                    <div>Superficie: <strong><?php echo e($selectedProposal->superficie_ha ?? 'N/A'); ?></strong> ha</div>
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-lg p-4 bg-white">
                                <div class="font-semibold mb-2">Estimación</div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>Persona-día</div>
                                    <div class="text-right"><strong><?php echo e($selectedProposal->estimated_person_days ?? 'N/A'); ?></strong></div>
                                    <div>Máquina-día</div>
                                    <div class="text-right"><strong><?php echo e($selectedProposal->estimated_machine_days ?? 'N/A'); ?></strong></div>
                                    <div>Duración (días)</div>
                                    <div class="text-right"><strong><?php echo e($selectedProposal->estimated_duration_days ?? 'N/A'); ?></strong></div>
                                </div>
                                <div class="text-slate-400 text-xs mt-2">
                                    Fallback: <?php echo e($selectedProposal->meta['fallback_used'] ?? 'N/A'); ?>

                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-xl shadow-sm border border-slate-300 overflow-hidden">
                                <div class="bg-slate-600 text-white px-6 py-4 flex justify-between items-center">
                                    <strong>👥 Empleados sugeridos</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white"><?php echo e($selectedProposal->proposedEmployees->count()); ?></span>
                                </div>
                                <div class="p-6">
                                    <?php if($selectedProposal->proposedEmployees->isEmpty()): ?>
                                        <div class="text-slate-400 text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    <?php else: ?>
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            <?php $__currentLoopData = $selectedProposal->proposedEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center gap-2 py-1" wire:key="emp-<?php echo e($row->id_allocation_proposal_employee); ?>">
                                                    <input
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                        type="checkbox"
                                                        id="ape-<?php echo e($row->id_allocation_proposal_employee); ?>"
                                                        wire:model.live="employeeSelected.<?php echo e($row->id_allocation_proposal_employee); ?>"
                                                        <?php if($guardando): ?> disabled <?php endif; ?>
                                                    >
                                                    <label class="text-sm text-slate-700" for="ape-<?php echo e($row->id_allocation_proposal_employee); ?>">
                                                        <?php echo e($row->empleado->apellido ?? ''); ?>, <?php echo e($row->empleado->nombre ?? ''); ?>

                                                        <small class="text-slate-500">- <?php echo e($row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? 'Sin rol')); ?></small>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700 ml-1">score: <?php echo e($row->score ?? 'N/A'); ?></span>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                                <div class="bg-brand text-white px-6 py-4 flex justify-between items-center">
                                    <strong>🚛 Maquinarias sugeridas</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white"><?php echo e($selectedProposal->proposedMaquinarias->count()); ?></span>
                                </div>
                                <div class="p-6">
                                    <?php if($selectedProposal->proposedMaquinarias->isEmpty()): ?>
                                        <div class="text-slate-400 text-sm">Sin sugerencias (falta histórico/pivotes).</div>
                                    <?php else: ?>
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            <?php $__currentLoopData = $selectedProposal->proposedMaquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center gap-2 py-1" wire:key="maq-<?php echo e($row->id_allocation_proposal_maquinaria); ?>">
                                                    <input
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                        type="checkbox"
                                                        id="apm-<?php echo e($row->id_allocation_proposal_maquinaria); ?>"
                                                        wire:model.live="maquinariaSelected.<?php echo e($row->id_allocation_proposal_maquinaria); ?>"
                                                        <?php if($guardando): ?> disabled <?php endif; ?>
                                                    >
                                                    <label class="text-sm text-slate-700" for="apm-<?php echo e($row->id_allocation_proposal_maquinaria); ?>">
                                                        <?php echo e($row->maquinaria->modelo ?? ''); ?>

                                                        <small class="text-slate-500">- <?php echo e($row->tipo_sugerido ?? ($row->maquinaria->tipoMaquinaria->nombre ?? 'N/A')); ?></small>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700 ml-1">score: <?php echo e($row->score ?? 'N/A'); ?></span>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-emerald-300 overflow-hidden">
                                <div class="bg-emerald-600 text-white px-6 py-4 flex justify-between items-center">
                                    <strong>📦 Insumos (semana 1)</strong>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white"><?php echo e($selectedProposal->proposedInsumos->count()); ?></span>
                                </div>
                                <div class="p-6">
                                    <?php if($selectedProposal->proposedInsumos->isEmpty()): ?>
                                        <div class="text-slate-400 text-sm">Sin sugerencias.</div>
                                    <?php else: ?>
                                        <div class="max-h-[320px] overflow-y-auto border border-slate-200 rounded-lg p-3">
                                            <?php $__currentLoopData = $selectedProposal->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-start gap-2 py-1" wire:key="insumo-<?php echo e($row->id_allocation_proposal_insumo); ?>">
                                                    <div class="pt-0.5">
                                                        <input
                                                            class="rounded border-slate-300 text-brand focus:ring-brand/20"
                                                            type="checkbox"
                                                            id="api-<?php echo e($row->id_allocation_proposal_insumo); ?>"
                                                            wire:model.live="insumoSelected.<?php echo e($row->id_allocation_proposal_insumo); ?>"
                                                            <?php if($guardando): ?> disabled <?php endif; ?>
                                                        >
                                                    </div>
                                                    <label class="w-full text-sm" for="api-<?php echo e($row->id_allocation_proposal_insumo); ?>">
                                                        <div class="font-semibold text-slate-800"><?php echo e($row->insumo->nombre ?? ''); ?></div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo e($row->insumo->unidadMedida->nombre ?? ''); ?>

                                                            <?php if(!is_null($row->cantidad_semana_1)): ?>
                                                                · cant. semana 1: <strong><?php echo e($row->cantidad_semana_1); ?></strong>
                                                            <?php else: ?>
                                                                · cant. semana 1: <strong>N/A</strong>
                                                            <?php endif; ?>

                                                            <?php if(!is_null($row->costo_estimado_semana_1)): ?>
                                                                · costo: <strong>$<?php echo e($row->costo_estimado_semana_1); ?></strong>
                                                            <?php else: ?>
                                                                · costo: <strong>N/A</strong>
                                                            <?php endif; ?>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-6">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                            <button class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="guardarSeleccion" <?php if($guardando): ?> disabled <?php endif; ?>>
                                💾 Guardar selección
                            </button>

                            <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="confirmar" <?php if($guardando): ?> disabled <?php endif; ?>>
                                ✓ Confirmar
                            </button>

                            <button class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors" wire:click="aplicar" <?php if($guardando): ?> disabled <?php endif; ?>>
                                📥 Aplicar al lote
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm mt-4">
                            <span>⚠</span>
                            <small>
                                "Aplicar" reemplaza las asignaciones actuales del lote por la selección de esta propuesta.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/allocation-proposals.blade.php ENDPATH**/ ?>