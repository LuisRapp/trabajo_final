<div class="container py-4">
    <?php if(!empty($loteId)): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0"><i class="bi bi-magic"></i> Recomendaciones del Lote #<?php echo e($loteId); ?></h4>
                <div class="text-muted small">Al pasar el lote a <strong>en proceso</strong> se generan estas propuestas.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="<?php echo e(route('lotes.index')); ?>">
                    <i class="bi bi-arrow-left"></i> Volver a Lotes
                </a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                <button class="btn btn-outline-primary" wire:click="generarAhora" <?php if($guardando): ?> disabled <?php endif; ?>>
                    <i class="bi bi-gear"></i> Generar ahora
                </button>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                <button class="btn btn-outline-secondary" wire:click="refreshProposals" <?php if($guardando): ?> disabled <?php endif; ?>>
                    <i class="bi bi-arrow-clockwise"></i> Refrescar
                </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link <?php echo e($mostrar_listado ? 'active' : ''); ?>"
                type="button"
                role="tab"
                wire:click="$set('mostrar_listado', true)"
            >
                <i class="bi bi-lightning-charge"></i> Propuestas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link <?php echo e(!$mostrar_listado ? 'active' : ''); ?>"
                type="button"
                role="tab"
                wire:click="$set('mostrar_listado', false)"
                <?php if(!$selected_proposal_id): ?> disabled <?php endif; ?>
            >
                <i class="bi bi-clipboard-check"></i> Detalle / Confirmar
            </button>
        </li>
    </ul>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="tab-content">
        <div class="tab-pane fade <?php echo e($mostrar_listado ? 'show active' : ''); ?>">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-magic"></i> Propuestas Automáticas</h5>
                    <div class="d-flex gap-2">
                        <?php if(empty($loteId)): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                            <button class="btn btn-outline-secondary btn-sm" wire:click="refreshProposals" <?php if($guardando): ?> disabled <?php endif; ?>>
                                <i class="bi bi-arrow-clockwise"></i> Refrescar
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(empty($loteId)): ?>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote</label>
                            <select class="form-select" wire:model.live="filter_lote_id">
                                <option value="">Todos</option>
                                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($l->id_lote); ?>">Lote #<?php echo e($l->id_lote); ?> - <?php echo e($l->ubicacion); ?> (<?php echo e($l->estado); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Estado</label>
                            <select class="form-select" wire:model.live="filter_status">
                                <option value="">Todos</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="applied">Applied</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="text-muted small">
                                Mostrando <?php echo e(is_countable($proposals) ? count($proposals) : 0); ?> propuestas
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lote</th>
                                    <th>Tarea</th>
                                    <th>Estimación</th>
                                    <th>Estado</th>
                                    <th>Creada</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong>#<?php echo e($p->id_allocation_proposal); ?></strong></td>
                                        <td>
                                            <div><strong>Lote #<?php echo e($p->id_lote); ?></strong></div>
                                            <div class="text-muted small"><?php echo e($p->lote->ubicacion ?? ''); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo e($p->tipo_tarea); ?></span>
                                            <?php if($p->id_lote_tarea): ?>
                                                <span class="badge bg-light text-dark">Tarea #<?php echo e($p->id_lote_tarea); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <div>Persona-día: <strong><?php echo e($p->estimated_person_days ?? 'N/A'); ?></strong></div>
                                            <div>Máquina-día: <strong><?php echo e($p->estimated_machine_days ?? 'N/A'); ?></strong></div>
                                            <div>Duración: <strong><?php echo e($p->estimated_duration_days ?? 'N/A'); ?></strong></div>
                                        </td>
                                        <td>
                                            <?php
                                                $badge = match($p->status) {
                                                    'applied' => 'success',
                                                    'confirmed' => 'primary',
                                                    default => 'warning'
                                                };
                                            ?>
                                            <span class="badge bg-<?php echo e($badge); ?>"><?php echo e($p->status); ?></span>
                                        </td>
                                        <td class="small text-muted"><?php echo e($p->created_at); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-sm" wire:click="seleccionar(<?php echo e($p->id_allocation_proposal); ?>)">
                                                <i class="bi bi-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay propuestas para los filtros seleccionados.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Estas propuestas se generan en base a histórico (persona-día / máquina-día). Podés confirmar y aplicar para cargar asignaciones del lote.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php echo e(!$mostrar_listado ? 'show active' : ''); ?>">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Detalle de Propuesta</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" wire:click="volver">
                            <i class="bi bi-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(!$selectedProposal): ?>
                        <div class="alert alert-warning mb-0">
                            <small>Seleccione una propuesta desde la pestaña "Propuestas".</small>
                        </div>
                    <?php else: ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-semibold">Propuesta #<?php echo e($selectedProposal->id_allocation_proposal); ?></div>
                                            <div class="text-muted small">Lote #<?php echo e($selectedProposal->id_lote); ?> - <?php echo e($selectedProposal->lote->ubicacion ?? ''); ?></div>
                                        </div>
                                        <div>
                                            <?php
                                                $badge = match($selectedProposal->status) {
                                                    'applied' => 'success',
                                                    'confirmed' => 'primary',
                                                    default => 'warning'
                                                };
                                            ?>
                                            <span class="badge bg-<?php echo e($badge); ?>"><?php echo e($selectedProposal->status); ?></span>
                                        </div>
                                    </div>

                                    <hr class="my-2">
                                    <div class="small">
                                        <div>Tarea: <span class="badge bg-secondary"><?php echo e($selectedProposal->tipo_tarea); ?></span></div>
                                        <div>Especie: <strong><?php echo e($selectedProposal->especie ?? 'N/A'); ?></strong></div>
                                        <div>Superficie: <strong><?php echo e($selectedProposal->superficie_ha ?? 'N/A'); ?></strong> ha</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <div class="fw-semibold mb-2">Estimación</div>
                                    <div class="row small">
                                        <div class="col-6">Persona-día</div>
                                        <div class="col-6 text-end"><strong><?php echo e($selectedProposal->estimated_person_days ?? 'N/A'); ?></strong></div>
                                        <div class="col-6">Máquina-día</div>
                                        <div class="col-6 text-end"><strong><?php echo e($selectedProposal->estimated_machine_days ?? 'N/A'); ?></strong></div>
                                        <div class="col-6">Duración (días)</div>
                                        <div class="col-6 text-end"><strong><?php echo e($selectedProposal->estimated_duration_days ?? 'N/A'); ?></strong></div>
                                    </div>
                                    <div class="text-muted small mt-2">
                                        Fallback: <?php echo e($selectedProposal->meta['fallback_used'] ?? 'N/A'); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-secondary h-100">
                                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-people-fill"></i> Empleados sugeridos</strong>
                                        <span class="badge bg-light text-dark"><?php echo e($selectedProposal->proposedEmployees->count()); ?></span>
                                    </div>
                                    <div class="card-body">
                                        <?php if($selectedProposal->proposedEmployees->isEmpty()): ?>
                                            <div class="text-muted small">Sin sugerencias (falta histórico/pivotes).</div>
                                        <?php else: ?>
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                <?php $__currentLoopData = $selectedProposal->proposedEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="ape-<?php echo e($row->id_allocation_proposal_employee); ?>"
                                                            wire:model.live="employeeSelected.<?php echo e($row->id_allocation_proposal_employee); ?>"
                                                            <?php if($guardando): ?> disabled <?php endif; ?>
                                                        >
                                                        <label class="form-check-label w-100" for="ape-<?php echo e($row->id_allocation_proposal_employee); ?>">
                                                            <?php echo e($row->empleado->apellido ?? ''); ?>, <?php echo e($row->empleado->nombre ?? ''); ?>

                                                            <small class="text-muted">- <?php echo e($row->rol_sugerido ?? ($row->empleado->rolLaboral->nombre ?? 'Sin rol')); ?></small>
                                                            <span class="badge bg-light text-dark ms-1">score: <?php echo e($row->score ?? 'N/A'); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-primary h-100">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-truck"></i> Maquinarias sugeridas</strong>
                                        <span class="badge bg-light text-dark"><?php echo e($selectedProposal->proposedMaquinarias->count()); ?></span>
                                    </div>
                                    <div class="card-body">
                                        <?php if($selectedProposal->proposedMaquinarias->isEmpty()): ?>
                                            <div class="text-muted small">Sin sugerencias (falta histórico/pivotes).</div>
                                        <?php else: ?>
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                <?php $__currentLoopData = $selectedProposal->proposedMaquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="apm-<?php echo e($row->id_allocation_proposal_maquinaria); ?>"
                                                            wire:model.live="maquinariaSelected.<?php echo e($row->id_allocation_proposal_maquinaria); ?>"
                                                            <?php if($guardando): ?> disabled <?php endif; ?>
                                                        >
                                                        <label class="form-check-label w-100" for="apm-<?php echo e($row->id_allocation_proposal_maquinaria); ?>">
                                                            <?php echo e($row->maquinaria->modelo ?? ''); ?>

                                                            <small class="text-muted">- <?php echo e($row->tipo_sugerido ?? ($row->maquinaria->tipoMaquinaria->nombre ?? 'N/A')); ?></small>
                                                            <span class="badge bg-light text-dark ms-1">score: <?php echo e($row->score ?? 'N/A'); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-box-seam"></i> Insumos (semana 1)</strong>
                                        <span class="badge bg-light text-dark"><?php echo e($selectedProposal->proposedInsumos->count()); ?></span>
                                    </div>
                                    <div class="card-body">
                                        <?php if($selectedProposal->proposedInsumos->isEmpty()): ?>
                                            <div class="text-muted small">Sin sugerencias.</div>
                                        <?php else: ?>
                                            <div style="max-height: 320px; overflow-y: auto;" class="border rounded p-2">
                                                <?php $__currentLoopData = $selectedProposal->proposedInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="d-flex align-items-start gap-2 py-1">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                id="api-<?php echo e($row->id_allocation_proposal_insumo); ?>"
                                                                wire:model.live="insumoSelected.<?php echo e($row->id_allocation_proposal_insumo); ?>"
                                                                <?php if($guardando): ?> disabled <?php endif; ?>
                                                            >
                                                        </div>
                                                        <label class="w-100" for="api-<?php echo e($row->id_allocation_proposal_insumo); ?>">
                                                            <div class="fw-semibold"><?php echo e($row->insumo->nombre ?? ''); ?></div>
                                                            <div class="small text-muted">
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
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-propuestas-asignacion', 'editar-propuestas-asignacion'])): ?>
                            <button class="btn btn-outline-secondary" wire:click="guardarSeleccion" <?php if($guardando): ?> disabled <?php endif; ?>>
                                <i class="bi bi-save"></i> Guardar selección
                            </button>

                            <button class="btn btn-primary" wire:click="confirmar" <?php if($guardando): ?> disabled <?php endif; ?>>
                                <i class="bi bi-check2-circle"></i> Confirmar
                            </button>

                            <button class="btn btn-success" wire:click="aplicar" <?php if($guardando): ?> disabled <?php endif; ?>>
                                <i class="bi bi-box-arrow-in-down"></i> Aplicar al lote
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i>
                                "Aplicar" reemplaza las asignaciones actuales del lote por la selección de esta propuesta.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/allocation-proposals.blade.php ENDPATH**/ ?>