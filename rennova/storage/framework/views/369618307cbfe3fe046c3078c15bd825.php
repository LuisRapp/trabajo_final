<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Kits de Mantenimiento Preventivo</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div id="kit-success-message" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('kit-success-message');
                if (msg) {
                    msg.classList.remove('show');
                    setTimeout(function() { msg.remove(); }, 150);
                }
            }, 1000);
        </script>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="kitsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-kit" type="button" role="tab" aria-controls="nuevo-kit" aria-selected="true">
                <i class="bi bi-plus-circle"></i> <?php echo e($editando_kit ? 'Editar Kit' : 'Nuevo Kit'); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-kits" type="button" role="tab" aria-controls="listado-kits" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Kits
            </button>
        </li>
    </ul>


    <div class="tab-content" id="kitsTabContent">
        <!-- Pestaña 1: Nuevo Kit (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-kit" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($editando_kit ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($editando_kit ? 'Editar Kit' : 'Configurar Kit'); ?></h5>
                </div>
                <div class="card-body">
                    <!-- Selector de Maquinaria -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label for="maquinaria_select" class="form-label fw-semibold">
                                <i class="bi bi-truck"></i> Maquinaria <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="maquinaria_seleccionada" id="maquinaria_select" class="form-select <?php $__errorArgs = ['maquinaria_seleccionada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" <?php if($editando_kit): ?> disabled <?php endif; ?>>
                                <option value="">Seleccione una maquinaria</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($maq->id_maquinaria); ?>"><?php echo e($maq->modelo); ?> (<?php echo e($maq->tipoMaquinaria ? $maq->tipoMaquinaria->nombre : 'Sin tipo'); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['maquinaria_seleccionada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if(!$maquinaria_seleccionada): ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-arrow-up-circle display-4"></i>
                            <p class="mt-3 mb-0">Seleccione una maquinaria para configurar su kit de mantenimiento preventivo</p>
                        </div>
                    <?php else: ?>
                        <!-- Acciones -->
                        <div class="d-flex gap-2 mb-3">
                            <button wire:click="abrirModalAgregar" type="button" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Agregar Insumo
                            </button>
                            <!--[if BLOCK]><![endif]--><?php if($editando_kit): ?>
                                <button wire:click="limpiarKit" type="button" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar Edición
                                </button>
                            <?php else: ?>
                                <!--[if BLOCK]><![endif]--><?php if($items_count > 0): ?>
                                    <button wire:click="registrarKit" type="button" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Registrar Kit
                                    </button>
                                    <button wire:click="limpiarKit" type="button" class="btn btn-outline-secondary">
                                        <i class="bi bi-trash"></i> Limpiar Todo
                                    </button>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($kit_modificado && !$editando_kit && $items_count > 0): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Hay cambios sin guardar. Haz clic en <strong>Registrar Kit</strong> para confirmar.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!-- Sub-tabs: Insumos e Historial -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="insumos-tab" data-bs-toggle="tab" data-bs-target="#insumos-pane" type="button" role="tab">
                                    <i class="bi bi-list-check"></i> Insumos del Kit
                                    <!--[if BLOCK]><![endif]--><?php if($items_count > 0): ?>
                                        <span class="badge bg-primary"><?php echo e($items_count); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial-pane" type="button" role="tab">
                                    <i class="bi bi-archive"></i> Historial de Bajas
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 p-3">
                            <!-- Insumos actuales -->
                            <div class="tab-pane fade show active" id="insumos-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="8%">ID</th>
                                                <th width="30%">Insumo</th>
                                                <th width="15%">Cant. Requerida</th>
                                                <th width="15%">Stock</th>
                                                <th width="15%">Tipo</th>
                                                <th width="17%" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <?php
                                                    $ins = optional($item->insumo);
                                                    $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary"><?php echo e($item->id_kit ?? $item->id); ?></span></td>
                                                    <td class="fw-semibold"><?php echo e($ins->nombre ?? '—'); ?></td>
                                                    <td><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e(($stock >= $item->cantidad_requerida) ? 'success' : 'danger'); ?>"><?php echo e(number_format($stock, 2)); ?></span>
                                                        <!--[if BLOCK]><![endif]--><?php if($stock < $item->cantidad_requerida): ?>
                                                            <br><small class="text-danger">Faltan <?php echo e(number_format($item->cantidad_requerida - $stock, 2)); ?></small>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                    <td>
                                                        <!--[if BLOCK]><![endif]--><?php if($item->es_obligatorio): ?>
                                                            <span class="badge bg-danger">Obligatorio</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Opcional</span>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button wire:click="abrirModalEditar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="btn btn-outline-primary" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button wire:click="eliminar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="btn btn-outline-danger" onclick="return confirm('¿Dar de baja este insumo del kit?')" title="Dar de baja">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-5">
                                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                        <p class="mb-2 mt-2">No hay insumos configurados para este kit</p>
                                                        <button wire:click="abrirModalAgregar" type="button" class="btn btn-primary btn-sm">
                                                            <i class="bi bi-plus-circle"></i> Agregar Primer Insumo
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </tbody>
                                    </table>
                                </div>

                                <!--[if BLOCK]><![endif]--><?php if($items_count > 0): ?>
                                    <div class="row mt-3 g-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-light border mb-0">
                                                <div class="row text-center">
                                                    <div class="col-3">
                                                        <strong class="d-block text-muted small">Total Insumos</strong>
                                                        <span class="h5 mb-0"><?php echo e($items_count); ?></span>
                                                    </div>
                                                    <div class="col-3">
                                                        <strong class="d-block text-muted small">Obligatorios</strong>
                                                        <span class="h5 mb-0 text-danger"><?php echo e($items_obligatorios); ?></span>
                                                    </div>
                                                    <div class="col-3">
                                                        <strong class="d-block text-muted small">Opcionales</strong>
                                                        <span class="h5 mb-0 text-secondary"><?php echo e($items_opcionales); ?></span>
                                                    </div>
                                                    <div class="col-3">
                                                        <strong class="d-block text-muted small">Stock OK</strong>
                                                        <span class="h5 mb-0 badge bg-<?php echo e(($items_con_stock === $items_count) ? 'success' : 'warning'); ?>"><?php echo e($items_con_stock); ?>/<?php echo e($items_count); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <!-- Historial de bajas -->
                            <div class="tab-pane fade" id="historial-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="8%">ID</th>
                                                <th width="35%">Insumo</th>
                                                <th width="15%">Cant. Requerida</th>
                                                <th width="20%">Fecha de Baja</th>
                                                <th width="22%" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!--[if BLOCK]><![endif]--><?php if($historial->count() > 0): ?>
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="table-warning">
                                                        <td><span class="badge bg-secondary"><?php echo e($item->id_kit ?? $item->id); ?></span></td>
                                                        <td>
                                                            <strong><?php echo e(optional($item->insumo)->nombre ?? '—'); ?></strong>
                                                            <!--[if BLOCK]><![endif]--><?php if($item->es_obligatorio): ?>
                                                                <span class="badge bg-danger ms-2">Obligatorio</span>
                                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        </td>
                                                        <td><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                        <td>
                                                            <i class="bi bi-clock-history text-muted"></i>
                                                            <small><?php echo e($item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '—'); ?></small>
                                                        </td>
                                                        <td class="text-center">
                                                            <button wire:click="restaurar(<?php echo e($item->id_kit ?? $item->id); ?>)" type="button" class="btn btn-success btn-sm" title="Restaurar">
                                                                <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-5">
                                                        <i class="bi bi-archive" style="font-size: 3rem;"></i>
                                                        <p class="mb-0 mt-2">No hay bajas registradas para este kit</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>


        <!-- Pestaña 2: Listado de Kits Registrados -->
        <div class="tab-pane fade" id="listado-kits" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Kits Registrados por Maquinaria</h5>
                </div>
                <div class="card-body">
                    <!--[if BLOCK]><![endif]--><?php if(count($kits_registrados) > 0): ?>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $kits_registrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maqId => $kit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="bi bi-gear-fill text-primary"></i> 
                                            <strong><?php echo e(optional($kit['maquinaria'])->modelo); ?></strong> 
                                            <span class="text-muted">(<?php echo e(optional(optional($kit['maquinaria'])->tipoMaquinaria)->nombre); ?>)</span>
                                        </h6>
                                        <small class="text-muted">ID: <?php echo e(optional($kit['maquinaria'])->id_maquinaria); ?></small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editarKit(<?php echo e($maqId); ?>)" type="button" class="btn btn-outline-primary" onclick="cambiarAPestanaFormulario()" title="Editar kit">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button wire:click="eliminarKit(<?php echo e($maqId); ?>)" type="button" class="btn btn-outline-danger" onclick="return confirm('¿Está seguro de eliminar este kit completo?')" title="Eliminar kit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="badge bg-primary"><?php echo e($kit['total_items']); ?> Insumos</span>
                                        <span class="badge bg-danger"><?php echo e($kit['obligatorios']); ?> Obligatorios</span>
                                        <span class="badge bg-secondary"><?php echo e($kit['opcionales']); ?> Opcionales</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Insumo</th>
                                                    <th>Cantidad</th>
                                                    <th>Stock</th>
                                                    <th>Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $kit['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $ins = optional($item->insumo);
                                                        $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                    ?>
                                                    <tr>
                                                        <td class="fw-semibold"><?php echo e($ins->nombre); ?></td>
                                                        <td><?php echo e(number_format($item->cantidad_requerida, 2)); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo e(($stock >= $item->cantidad_requerida) ? 'success' : 'danger'); ?>">
                                                                <?php echo e(number_format($stock, 2)); ?>

                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $item->es_obligatorio 
                                                                ? '<span class="badge bg-danger">Obligatorio</span>' 
                                                                : '<span class="badge bg-secondary">Opcional</span>'; ?>

                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mb-2 mt-2">No hay kits registrados aún</p>
                            <button class="btn btn-primary" type="button" data-bs-toggle="tab" data-bs-target="#nuevo-kit">
                                <i class="bi bi-plus-circle"></i> Crear Primer Kit
                            </button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar/Editar Insumo -->
    <!--[if BLOCK]><![endif]--><?php if($modal_item): ?>
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-<?php echo e($item_id ? 'pencil-square' : 'plus-circle'); ?>"></i>
                            <?php echo e($item_id ? 'Editar' : 'Agregar'); ?> Insumo al Kit
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="guardar">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                <select wire:model="insumo_id" class="form-select <?php $__errorArgs = ['insumo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccionar insumo...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($insumo->id_insumo); ?>">
                                            <?php echo e($insumo->nombre); ?> (Stock: <?php echo e(number_format($insumo->stock ?? 0, 2)); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['insumo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cantidad Requerida <span class="text-danger">*</span></label>
                                <input type="number" wire:model="cantidad_requerida" class="form-control <?php $__errorArgs = ['cantidad_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" step="0.1" min="0" min="0.01" placeholder="Ej: 10.00">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['cantidad_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="es_obligatorio" class="form-check-input" id="esObligatorioCheck">
                                    <label class="form-check-label" for="esObligatorioCheck">¿Es obligatorio?</label>
                                </div>
                                <small class="text-muted">Los insumos obligatorios deben estar disponibles para aprobar el mantenimiento</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="guardar">
                            <i class="bi bi-save"></i> <?php echo e($item_id ? 'Actualizar' : 'Agregar'); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('kitGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/configuracion-kits.blade.php ENDPATH**/ ?>