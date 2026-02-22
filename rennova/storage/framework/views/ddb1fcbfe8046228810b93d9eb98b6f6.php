<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><i class="bi bi-file-earmark-text"></i> Auditorías del Sistema</h1>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <strong><i class="bi bi-clock-history"></i> Registro de Cambios</strong>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-light" wire:click="toggleFiltros" aria-controls="filtrosAuditoria" aria-expanded="<?php echo e($mostrarFiltros ? 'true' : 'false'); ?>">
                        <i class="bi bi-funnel"></i> Filtros
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros Colapsables -->
            <?php if($mostrarFiltros): ?>
            <div id="filtrosAuditoria">
                <div class="row g-3 mb-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Buscar</label>
                        <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control" placeholder="URL, IP o tag...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Modelo</label>
                        <select wire:model.live="filtroModelo" class="form-select">
                            <option value="">Todos los modelos</option>
                            <?php $__currentLoopData = $modelos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($modelo['value']); ?>"><?php echo e($modelo['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Evento</label>
                        <select wire:model.live="filtroEvento" class="form-select">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Usuario</label>
                        <select wire:model.live="filtroUsuario" class="form-select">
                            <option value="">Todos los usuarios</option>
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($usuario['id']); ?>"><?php echo e($usuario['nombre']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Desde</label>
                        <input type="date" wire:model.live="filtroFechaDesde" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Hasta</label>
                        <input type="date" wire:model.live="filtroFechaHasta" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" wire:click="limpiarFiltros" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Auditorías -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Modelo / Registro</th>
                            <th>Evento</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-secondary">#<?php echo e($auditoria->id); ?></span></td>
                                <td>
                                    <strong><?php echo e(class_basename($auditoria->auditable_type)); ?></strong><br>
                                    <small class="text-muted">ID: <?php echo e($auditoria->auditable_id); ?></small>
                                </td>
                                <td>
                                    <?php if($auditoria->event === 'created'): ?>
                                        <span class="badge bg-success"><i class="bi bi-plus-circle"></i> Creado</span>
                                    <?php elseif($auditoria->event === 'updated'): ?>
                                        <span class="badge bg-primary"><i class="bi bi-pencil"></i> Actualizado</span>
                                    <?php elseif($auditoria->event === 'deleted'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-trash"></i> Eliminado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(ucfirst($auditoria->event)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($auditoria->user): ?>
                                        <i class="bi bi-person-circle text-primary"></i> <?php echo e($auditoria->user->name); ?><br>
                                        <small class="text-muted"><?php echo e($auditoria->ip_address ?? 'N/A'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="bi bi-robot"></i> Sistema</span><br>
                                        <small class="text-muted"><?php echo e($auditoria->ip_address ?? 'N/A'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo e($auditoria->created_at->format('d/m/Y H:i')); ?><br>
                                    <small class="text-muted"><?php echo e($auditoria->created_at->diffForHumans()); ?></small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalDetalle<?php echo e($auditoria->id); ?>">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay auditorías registradas con los filtros aplicados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?php echo e($auditorias->links()); ?>

        </div>
    </div>

    <!-- Modales de Detalles -->
    <?php $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="modalDetalle<?php echo e($auditoria->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle"></i> Detalles de Auditoría #<?php echo e($auditoria->id); ?>

                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Modelo:</strong> <?php echo e(class_basename($auditoria->auditable_type)); ?></p>
                                <p class="mb-2"><strong>ID del Registro:</strong> #<?php echo e($auditoria->auditable_id); ?></p>
                                <p class="mb-0"><strong>Evento:</strong> 
                                    <?php if($auditoria->event === 'created'): ?>
                                        <span class="badge bg-success">Creado</span>
                                    <?php elseif($auditoria->event === 'updated'): ?>
                                        <span class="badge bg-primary">Actualizado</span>
                                    <?php elseif($auditoria->event === 'deleted'): ?>
                                        <span class="badge bg-danger">Eliminado</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Usuario:</strong> <?php echo e($auditoria->user->name ?? 'Sistema'); ?></p>
                                <p class="mb-2"><strong>IP:</strong> <?php echo e($auditoria->ip_address ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Fecha:</strong> <?php echo e($auditoria->created_at->format('d/m/Y H:i:s')); ?></p>
                            </div>
                        </div>

                        <?php if($auditoria->url): ?>
                            <div class="mb-3">
                                <strong>URL:</strong> <code class="d-block bg-light p-2 rounded"><?php echo e($auditoria->url); ?></code>
                            </div>
                        <?php endif; ?>

                        <?php if($auditoria->event === 'updated' && $auditoria->old_values && $auditoria->new_values): ?>
                            <h6 class="mb-3"><i class="bi bi-arrow-left-right"></i> Cambios Realizados</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Campo</th>
                                            <th style="width: 35%;">Valor Anterior</th>
                                            <th style="width: 35%;">Valor Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $auditoria->new_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $valorNuevo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(isset($auditoria->old_values[$campo]) && $auditoria->old_values[$campo] != $valorNuevo): ?>
                                                <tr>
                                                    <td><strong><?php echo e($campo); ?></strong></td>
                                                    <td>
                                                        <div class="text-break">
                                                            <?php echo e(is_array($auditoria->old_values[$campo]) ? json_encode($auditoria->old_values[$campo], JSON_UNESCAPED_UNICODE) : ($auditoria->old_values[$campo] ?? 'null')); ?>

                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-break">
                                                            <?php echo e(is_array($valorNuevo) ? json_encode($valorNuevo, JSON_UNESCAPED_UNICODE) : ($valorNuevo ?? 'null')); ?>

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif($auditoria->event === 'created' && $auditoria->new_values): ?>
                            <h6 class="mb-3"><i class="bi bi-plus-circle"></i> Datos Creados</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code><?php echo e(json_encode($auditoria->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                            </div>
                        <?php elseif($auditoria->event === 'deleted' && $auditoria->old_values): ?>
                            <h6 class="mb-3"><i class="bi bi-trash"></i> Datos Eliminados</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code><?php echo e(json_encode($auditoria->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH D:\trabajo_final\rennova\resources\views\livewire\auditorias.blade.php ENDPATH**/ ?>