<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-boxes"></i> Gestión de Stock (FIFO)</h1>
        <button class="btn btn-primary" wire:click="abrirModal">
            <i class="bi bi-plus-circle"></i> Registrar Entrada
        </button>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-<?php echo e(session('alert-type', 'success')); ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2"><i class="bi bi-archive"></i> Lotes Activos</h6>
                    <h3 class="mb-0 text-primary"><?php echo e($estadisticas['total_lotes']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2"><i class="bi bi-box-seam"></i> Stock Total</h6>
                    <h3 class="mb-0 text-info"><?php echo e(number_format($estadisticas['stock_total'], 2)); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2"><i class="bi bi-currency-dollar"></i> Valor Inventario</h6>
                    <h3 class="mb-0 text-success">$<?php echo e(number_format($estadisticas['valor_inventario'], 2)); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2"><i class="bi bi-exclamation-triangle"></i> Próximos a Agotar</h6>
                    <h3 class="mb-0 <?php echo e($estadisticas['lotes_proximos_agotar'] > 0 ? 'text-warning' : 'text-secondary'); ?>"><?php echo e($estadisticas['lotes_proximos_agotar']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Insumo</label>
                    <select class="form-select" wire:model.live="filtro_insumo">
                        <option value="">Todos los insumos</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($insumo->id_insumo); ?>"><?php echo e($insumo->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Proveedor</label>
                    <select class="form-select" wire:model.live="filtro_proveedor">
                        <option value="">Todos los proveedores</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($proveedor->id_proveedor); ?>"><?php echo e($proveedor->razon_social); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tipo Movimiento</label>
                    <select class="form-select" wire:model.live="filtro_tipo">
                        <option value="">Todos</option>
                        <option value="compra">Compra</option>
                        <option value="ajuste_entrada">Ajuste Entrada</option>
                        <option value="devolucion">Devolución</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Estado</label>
                    <select class="form-select" wire:model.live="filtro_estado">
                        <option value="disponibles">Disponibles</option>
                        <option value="agotados">Agotados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" wire:click="limpiarFiltros">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Desde</label>
                    <input type="date" class="form-control" wire:model.live="filtro_fecha_inicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Hasta</label>
                    <input type="date" class="form-control" wire:model.live="filtro_fecha_fin">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de lotes -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-table"></i> Lotes de Inventario</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID Lote</th>
                            <th>Insumo</th>
                            <th>Proveedor</th>
                            <th>Fecha Compra</th>
                            <th>Tipo</th>
                            <th class="text-end">Cant. Inicial</th>
                            <th class="text-end">Disponible</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Valor Disp.</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($lote->agotado ? 'table-secondary' : ''); ?>">
                                <td><strong><?php echo e($lote->id_lote_inventario); ?></strong></td>
                                <td><?php echo e($lote->insumo->nombre ?? 'N/A'); ?></td>
                                <td><?php echo e($lote->proveedor->razon_social ?? 'N/A'); ?></td>
                                <td><?php echo e($lote->fecha_compra->format('d/m/Y')); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo e($lote->tipo_movimiento === 'compra' ? 'primary' : 'info'); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $lote->tipo_movimiento))); ?>

                                    </span>
                                </td>
                                <td class="text-end"><?php echo e(number_format($lote->cantidad_inicial, 2)); ?></td>
                                <td class="text-end">
                                    <span class="<?php echo e($lote->estaProximoAgotar() ? 'text-warning fw-bold' : ''); ?>">
                                        <?php echo e(number_format($lote->cantidad_disponible, 2)); ?>

                                    </span>
                                </td>
                                <td class="text-end">$<?php echo e(number_format($lote->precio_unitario, 2)); ?></td>
                                <td class="text-end">$<?php echo e(number_format($lote->valor_disponible, 2)); ?></td>
                                <td class="text-center">
                                    <!--[if BLOCK]><![endif]--><?php if($lote->agotado): ?>
                                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Agotado</span>
                                    <?php elseif($lote->estaProximoAgotar()): ?>
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Disponible</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm" wire:click="verDetalle(<?php echo e($lote->id_lote_inventario); ?>)" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> No hay lotes de inventario registrados con los filtros aplicados
                                </td>
                            </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?php echo e($lotes->links()); ?>

        </div>
    </div>

    <!-- Modal: Registrar Entrada -->
    <!--[if BLOCK]><![endif]--><?php if($mostrarModal): ?>
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-box-seam"></i> Registrar Entrada de Stock
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="guardar">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="id_insumo">
                                        <option value="">Seleccione un insumo</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($insumo->id_insumo); ?>">
                                                <?php echo e($insumo->nombre); ?> (Stock: <?php echo e(number_format($insumo->stock ?? 0, 2)); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Proveedor</label>
                                    <select class="form-select <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="id_proveedor">
                                        <option value="">Seleccione un proveedor</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($proveedor->id_proveedor); ?>"><?php echo e($proveedor->razon_social); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="cantidad">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Precio Unitario <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['precio_unitario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="precio_unitario">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['precio_unitario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Costo Total</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="$<?php echo e(number_format(floatval($cantidad ?? 0) * floatval($precio_unitario ?? 0), 2)); ?>" 
                                           disabled readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Número de Factura</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['numero_factura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="numero_factura">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['numero_factura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fecha de Compra <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?php $__errorArgs = ['fecha_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="fecha_compra">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Tipo de Movimiento <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['tipo_movimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="tipo_movimiento">
                                        <option value="">Seleccione un tipo</option>
                                        <option value="compra">Compra</option>
                                        <option value="ajuste_entrada">Ajuste de Entrada</option>
                                        <option value="devolucion">Devolución</option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['tipo_movimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <textarea class="form-control <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              rows="3" wire:model="observaciones" placeholder="Observaciones adicionales..."></textarea>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="guardar">
                            <i class="bi bi-save"></i> Guardar Entrada
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Modal: Detalle de Lote -->
    <!--[if BLOCK]><![endif]--><?php if($loteSeleccionado): ?>
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle"></i> Detalle del Lote #<?php echo e($loteSeleccionado?->id_lote_inventario); ?>

                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarDetalle"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Información del Lote -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-file-text"></i> Información General</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <th width="50%">Insumo:</th>
                                                <td><?php echo e($loteSeleccionado?->insumo?->nombre ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Proveedor:</th>
                                                <td><?php echo e($loteSeleccionado?->proveedor?->razon_social ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Compra:</th>
                                                <td><?php echo e(optional($loteSeleccionado?->fecha_compra)->format('d/m/Y')); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Número Factura:</th>
                                                <td><?php echo e($loteSeleccionado?->numero_factura ?? 'N/A'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Cantidades y Valores</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-3">
                                            <tr>
                                                <th width="50%">Cantidad Inicial:</th>
                                                <td class="text-end"><?php echo e(number_format($loteSeleccionado?->cantidad_inicial ?? 0, 2)); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Cantidad Disponible:</th>
                                                <td class="text-end fw-bold text-success"><?php echo e(number_format($loteSeleccionado?->cantidad_disponible ?? 0, 2)); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Precio Unitario:</th>
                                                <td class="text-end">$<?php echo e(number_format($loteSeleccionado?->precio_unitario ?? 0, 2)); ?></td>
                                            </tr>
                                        </table>
                                        <div>
                                            <label class="form-label fw-semibold">Porcentaje Consumido:</label>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar <?php echo e(($loteSeleccionado?->porcentaje_consumido ?? 0) > 80 ? 'bg-warning' : 'bg-primary'); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo e($loteSeleccionado?->porcentaje_consumido ?? 0); ?>%">
                                                    <?php echo e(number_format($loteSeleccionado?->porcentaje_consumido ?? 0, 1)); ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if(($loteSeleccionado?->observaciones ?? null)): ?>
                            <div class="alert alert-info">
                                <strong><i class="bi bi-chat-left-text"></i> Observaciones:</strong> <?php echo e($loteSeleccionado?->observaciones); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Movimientos</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th class="text-end">Cantidad</th>
                                                <th class="text-end">Precio Unit.</th>
                                                <th class="text-end">Costo Total</th>
                                                <th>Motivo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = ($loteSeleccionado?->movimientos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e(optional(\Carbon\Carbon::parse($mov->fecha))->format('d/m/Y H:i')); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($mov->tipo === 'entrada' ? 'success' : 'danger'); ?>">
                                                            <?php echo e(ucfirst($mov->tipo)); ?>

                                                        </span>
                                                    </td>
                                                    <td class="text-end"><?php echo e(number_format($mov->cantidad, 2)); ?></td>
                                                    <td class="text-end">$<?php echo e(number_format($mov->precio_unitario, 2)); ?></td>
                                                    <td class="text-end">$<?php echo e(number_format($mov->costo_total_movimiento ?? 0, 2)); ?></td>
                                                    <td><?php echo e($mov->motivo ?? '-'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">
                                                        <i class="bi bi-inbox"></i> No hay movimientos registrados
                                                    </td>
                                                </tr>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarDetalle">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/gestion-stock.blade.php ENDPATH**/ ?>