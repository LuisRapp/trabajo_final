<div class="container py-4"> <!-- INICIO: ÚNICO ELEMENTO RAÍZ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Mantenimientos</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="mantenimientosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e($activeTab === 'nuevo' ? 'active' : ''); ?>" id="nuevo-tab" type="button" role="tab" aria-controls="nuevo-mantenimiento" aria-selected="<?php echo e($activeTab === 'nuevo' ? 'true' : 'false'); ?>" wire:click="$set('activeTab','nuevo')">
                <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e($activeTab === 'listado' ? 'active' : ''); ?>" id="listado-tab" type="button" role="tab" aria-controls="listado-mantenimientos" aria-selected="<?php echo e($activeTab === 'listado' ? 'true' : 'false'); ?>" wire:click="$set('activeTab','listado')">
                <i class="bi bi-list-ul"></i> Listado de Mantenimientos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="mantenimientosTabContent">
        <!-- Pestaña 1: Nuevo Mantenimiento (Formulario) -->
        <div class="tab-pane fade <?php echo e($activeTab === 'nuevo' ? 'show active' : ''); ?>" id="nuevo-mantenimiento" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($mantenimiento_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($mantenimiento_id ? 'Editar Orden' : 'Nueva Orden de Mantenimiento'); ?></h5>
                </div>
                <div class="card-body">
                    <!-- Alerta para tipo preventivo -->
                    <!--[if BLOCK]><![endif]--><?php if(count($kitPreventivo) > 0): ?>
                        <div class="alert alert-info border-info" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-box-seam"></i> Kit de Mantenimiento Preventivo
                            </h6>
                            <small>Se utilizarán los siguientes insumos del kit configurado:</small>
                            <ul class="mb-0 mt-2 small">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $kitPreventivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($item['nombre'] ?? 'N/A'); ?>: <?php echo e(number_format($item['cantidad_requerida'], 2)); ?> unidades</li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </ul>
                        </div>
                    <?php elseif($id_maquinaria && $id_tipo_mantenimiento): ?>
                        <?php
                            $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                            $esPreventivo = $tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo');
                        ?>
                        <!--[if BLOCK]><![endif]--><?php if($esPreventivo): ?>
                            <div class="alert alert-warning border-warning" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Advertencia:</strong> No hay kit de mantenimiento preventivo configurado para esta maquinaria.
                                <a href="/kits-mantenimiento" class="alert-link">Configurar kit</a>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Maquinaria <span class="text-danger">*</span></label>
                                <select wire:model.live="id_maquinaria" class="form-select <?php $__errorArgs = ['id_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maquinaria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($maquinaria->id_maquinaria); ?>">
                                            <?php echo e($maquinaria->modelo); ?> - <?php echo e($maquinaria->tipoMaquinaria?->nombre ?? 'N/A'); ?>

                                            <!--[if BLOCK]><![endif]--><?php if($maquinaria->umbral_toneladas): ?>
                                                (<?php echo e(number_format($maquinaria->toneladas_acumuladas ?? 0, 0)); ?>/<?php echo e(number_format($maquinaria->umbral_toneladas, 0)); ?> ton)
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                                <select wire:model.live="id_tipo_mantenimiento" class="form-select <?php $__errorArgs = ['id_tipo_mantenimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tipo->id_tipo_mantenimiento); ?>"><?php echo e($tipo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_tipo_mantenimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php if($id_tipo_mantenimiento): ?>
                                    <?php
                                        $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                                    ?>
                                    <!--[if BLOCK]><![endif]--><?php if($tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo')): ?>
                                        <small class="text-info">
                                            <i class="bi bi-info-circle"></i> Se utilizará el kit de mantenimiento preventivo configurado
                                        </small>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_inicio" class="form-control <?php $__errorArgs = ['fecha_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Programada</label>
                                <input type="date" wire:model="fecha_programada" class="form-control <?php $__errorArgs = ['fecha_programada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_programada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Debe estar dentro de los próximos 7 días desde hoy
                                    (<?php echo e(\Carbon\Carbon::now()->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::now()->addDays(7)->format('d/m/Y')); ?>)
                                </small>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select wire:model="estado" class="form-select <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="programado">Programado</option>
                                    <option value="en curso">En Curso</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <small class="text-muted">La orden se completará desde el listado</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <!--[if BLOCK]><![endif]--><?php if($mantenimiento_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($mantenimiento_id ? 'Actualizar' : 'Crear Orden'); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 		<!-- Pestaña 2: Listado de Mantenimientos (Tabla) -->
        <div class="tab-pane fade <?php echo e($activeTab === 'listado' ? 'show active' : ''); ?>" id="listado-mantenimientos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Mantenimientos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por maquinaria, tipo, estado o costo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Maquinaria</th>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Programada</th>
                                <th>Fecha Fin</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $mantenimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mantenimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo e($mantenimiento->id_mantenimiento); ?></span></td>
                                    <td class="fw-semibold"><?php echo e($mantenimiento->maquinaria?->modelo ?? 'N/A'); ?></td>
                                    <td><?php echo e($mantenimiento->tipoMantenimiento?->nombre ?? 'N/A'); ?></td>
                                    <td><?php echo e($mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td>
                                        <!--[if BLOCK]><![endif]--><?php if($mantenimiento->fecha_programada): ?>
                                            <span class="badge bg-info">
                                                <?php echo e(\Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y')); ?>

                                            </span>
                                            <!--[if BLOCK]><![endif]--><?php if($mantenimiento->estado === 'programado' && \Carbon\Carbon::parse($mantenimiento->fecha_programada)->isToday()): ?>
                                                <i class="bi bi-exclamation-circle text-warning" title="Programado para hoy"></i>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td><?php echo e($mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td>$<?php echo e(number_format($mantenimiento->costo_total, 2, ',', '.')); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($mantenimiento->estado == 'completado' ? 'success' : ($mantenimiento->estado == 'en curso' ? 'warning' : ($mantenimiento->estado == 'vencido' ? 'danger' : 'secondary'))); ?>">
                                            <?php echo e(ucfirst($mantenimiento->estado)); ?>

                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $isCompletado = strtolower(trim($mantenimiento->estado ?? '')) === 'completado';
                                            $isProgramado = strtolower(trim($mantenimiento->estado ?? '')) === 'programado';
                                            $isVencido = strtolower(trim($mantenimiento->estado ?? '')) === 'vencido';
                                        ?>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!--[if BLOCK]><![endif]--><?php if($isProgramado): ?>
                                                <button type="button" class="btn btn-outline-info" wire:click="confirmarMantenimiento(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Confirmar realización">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <!--[if BLOCK]><![endif]--><?php if($isVencido): ?>
                                                <button type="button" class="btn btn-outline-warning" wire:click="reprogramarMantenimiento(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Reprogramar">
                                                    <i class="bi bi-calendar-plus"></i>
                                                </button>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <button type="button" class="btn btn-outline-success" wire:click.prevent="abrirModalCompletar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Completar" <?php if($isCompletado || $isVencido): ?> disabled <?php endif; ?>>
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" wire:click="editar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($mantenimiento->id_mantenimiento); ?>)" onclick="return confirm('¿Está seguro de eliminar este mantenimiento?')" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mb-0 mt-2">No hay mantenimientos registrados.</p>
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

    <!-- ... (Aquí estaba el </div> que causaba el error) ... -->
    <!-- Todo lo que sigue (CSS, Modal, Script) AHORA ESTÁ DENTRO del div principal -->

    <?php if (! $__env->hasRenderedOnce('99cb243c-819f-4a1b-b21d-e3d47c892d8a')): $__env->markAsRenderedOnce('99cb243c-819f-4a1b-b21d-e3d47c892d8a'); ?>
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

    <!--[if BLOCK]><![endif]--><?php if($mostrarModalCompletar): ?>
        <div class="lw-modal-overlay" wire:key="modal-overlay">
            <div class="lw-modal-card" wire:key="modal-card-<?php echo e($orden_completar_id); ?>">
                <div class="lw-modal-header">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Completar Orden de Mantenimiento</h5>
                    <button type="button" class="lw-close" wire:click="cerrarModalCompletar" aria-label="Cerrar">&times;</button>
                </div>
                <div class="lw-modal-body">
                    <?php if(session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['general'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e($message); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="alert alert-info">
                        <strong>Orden #<?php echo e($orden_completar_info['id'] ?? 'N/A'); ?></strong><br>
                        <strong>Maquinaria:</strong> <?php echo e($orden_completar_info['maquinaria'] ?? 'N/A'); ?><br>
                        <strong>Tipo:</strong> <?php echo e($orden_completar_info['tipo'] ?? 'N/A'); ?><br>
                        <strong>Fecha Inicio:</strong> <?php echo e(isset($orden_completar_info['fecha_inicio']) ? \Carbon\Carbon::parse($orden_completar_info['fecha_inicio'])->format('d/m/Y') : 'N/A'); ?>

                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Finalización <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha_fin_completar" class="form-control <?php $__errorArgs = ['fecha_fin_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_fin_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Costo Total (opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" wire:model="costo_total_completar" step="1" min="0" class="form-control <?php $__errorArgs = ['costo_total_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['costo_total_completar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <small class="text-muted">Se sumará automáticamente el costo de los insumos</small>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">
                        <i class="bi bi-box-seam"></i> Insumos Utilizados 
                        <!--[if BLOCK]><![endif]--><?php if(!$orden_es_correctivo): ?>
                            <span class="badge bg-info ms-2">Kit Preventivo</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </h6>

                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos_usados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row g-2 mb-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small">Insumo</label>
                                <select wire:model="insumos_usados.<?php echo e($index); ?>.id_insumo" class="form-select form-select-sm">
                                    <option value="">Seleccione...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = \App\Models\Insumo::orderBy('nombre')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($ins->id_insumo); ?>"><?php echo e($ins->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Cantidad</label>
                                <input type="number" wire:model="insumos_usados.<?php echo e($index); ?>.cantidad" step="1" min="0" class="form-control form-control-sm" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Precio Unit.</label>
                                <input type="number" wire:model="insumos_usados.<?php echo e($index); ?>.precio_unitario" step="0.01" min="0" class="form-control form-control-sm" placeholder="0.00">
                            </div>
                            <div class="col-md-1 text-end">
                                <!--[if BLOCK]><![endif]--><?php if($index > 0 || count($insumos_usados) > 1): ?>
                                    <button type="button" wire:click="eliminarInsumo(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                    <button type="button" wire:click="agregarInsumo" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Agregar Insumo
                    </button>
                </div>
                <div class="lw-modal-footer">

                    <button type="button" class="btn btn-outline-secondary" wire:click="cerrarModalCompletar">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" wire:click="completarOrden" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="completarOrden">
                            <i class="bi bi-check-circle"></i> Completar Orden
                        </span>
                        <span wire:loading wire:target="completarOrden">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- JavaScript: auto-ocultar alertas + logs de depuración -->
    <script>
        // Auto-ocultar mensaje de éxito después de 3 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                    alert.close();
                }, 3000);
            }
        });

        // Log: al abrir el modal desde Livewire
        window.addEventListener('modal-completar-opened', (e) => {
            console.log('[Livewire] Modal Completar abierto para ID:', e.detail?.id);
            // Opcional: hacer scroll al inicio para evitar que el modal quede fuera de vista
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</div> <!-- FIN: ÚNICO ELEMENTO RAÍZ --><?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/mantenimientos.blade.php ENDPATH**/ ?>