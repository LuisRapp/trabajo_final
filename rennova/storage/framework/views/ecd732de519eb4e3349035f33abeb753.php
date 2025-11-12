<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestión de Mantenimientos</h3>
        </div>
        <div class="card-body">
            
            
            <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo e(session('message')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            
            <?php if(session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            
            
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab_activo === 'ordenes' ? 'active' : ''); ?>" 
                       wire:click="cambiarTab('ordenes')" 
                       href="javascript:void(0)">
                        <i class="bi bi-clipboard-check"></i> Órdenes Activas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab_activo === 'completadas' ? 'active' : ''); ?>" 
                       wire:click="cambiarTab('completadas')" 
                       href="javascript:void(0)">
                        <i class="bi bi-check-circle"></i> Completadas
                    </a>
                </li>
            </ul>
            
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Maquinaria</label>
                    <select wire:model="filtro_maquinaria" class="form-select">
                        <option value="">Todas</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($maq->id); ?>">
                                <?php echo e($maq->modelo); ?> (<?php echo e($maq->tipoMaquinaria->nombre); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label>Tipo</label>
                    <select wire:model="filtro_tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>
                </div>
                
                <!--[if BLOCK]><![endif]--><?php if($tab_activo === 'ordenes'): ?>
                <div class="col-md-2">
                    <label>Estado</label>
                    <select wire:model="filtro_estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="programado">Programado</option>
                        <option value="en curso">En Curso</option>
                    </select>
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                
                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date" wire:model="filtro_fecha_desde" class="form-control">
                </div>
                
                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date" wire:model="filtro_fecha_hasta" class="form-control">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="resetearFiltros" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
            
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Maquinaria</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Toneladas</th>
                            <th>Fecha Creación</th>
                            <!--[if BLOCK]><![endif]--><?php if($tab_activo === 'completadas'): ?>
                                <th>Costo Total</th>
                                <th>Fecha Completado</th>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $ordenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orden): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($orden->id); ?></td>
                            <td>
                                <strong><?php echo e($orden->maquinaria->modelo); ?></strong><br>
                                <small class="text-muted"><?php echo e($orden->maquinaria->tipoMaquinaria->nombre); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($orden->tipo_mantenimiento === 'preventivo' ? 'info' : 'warning'); ?>">
                                    <?php echo e(ucfirst($orden->tipo_mantenimiento)); ?>

                                </span>
                            </td>
                            <td>
                                <?php
                                    $badgeColor = match($orden->estado) {
                                        'programado' => 'secondary',
                                        'en curso' => 'primary',
                                        'completado' => 'success',
                                        default => 'dark'
                                    };
                                ?>
                                <span class="badge bg-<?php echo e($badgeColor); ?>">
                                    <?php echo e(ucfirst($orden->estado)); ?>

                                </span>
                            </td>
                            <td><?php echo e(number_format($orden->toneladas_snapshot ?? 0, 2)); ?></td>
                            <td><?php echo e($orden->created_at->format('d/m/Y H:i')); ?></td>
                            
                            <!--[if BLOCK]><![endif]--><?php if($tab_activo === 'completadas'): ?>
                                <td>$<?php echo e(number_format($orden->costo_total ?? 0, 2)); ?></td>
                                <td><?php echo e($orden->fecha_completado ? $orden->fecha_completado->format('d/m/Y H:i') : '-'); ?></td>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="verDetalle(<?php echo e($orden->id); ?>)" 
                                            class="btn btn-info" 
                                            title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <!--[if BLOCK]><![endif]--><?php if($orden->estado === 'programado'): ?>
                                        <button wire:click="abrirModalAprobar(<?php echo e($orden->id); ?>)" 
                                                class="btn btn-success" 
                                                title="Aprobar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    
                                    <!--[if BLOCK]><![endif]--><?php if($orden->estado === 'en curso'): ?>
                                        <button wire:click="abrirModalCompletar(<?php echo e($orden->id); ?>)" 
                                                class="btn btn-primary" 
                                                title="Completar">
                                            <i class="bi bi-flag-fill"></i>
                                        </button>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="20" class="text-center text-muted">
                                No hay órdenes para mostrar
                            </td>
                        </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    
    <!--[if BLOCK]><![endif]--><?php if($modal_aprobar && $orden_seleccionada): ?>
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Aprobar Orden de Mantenimiento #<?php echo e($orden_seleccionada->id); ?></h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalAprobar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Maquinaria:</strong> <?php echo e($orden_seleccionada->maquinaria->modelo); ?>

                        </div>
                        <div class="col-md-6">
                            <strong>Tipo:</strong> <?php echo e(ucfirst($orden_seleccionada->tipo_mantenimiento)); ?>

                        </div>
                    </div>
                    
                    <!--[if BLOCK]><![endif]--><?php if($verificacion_stock): ?>
                        <h6 class="mt-3">Verificación de Stock:</h6>
                        
                        <!--[if BLOCK]><![endif]--><?php if($verificacion_stock['puede_aprobar']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 
                                Todos los insumos están disponibles
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Stock insuficiente para aprobar
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Requerido</th>
                                    <th>Disponible</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $verificacion_stock['kit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e(in_array($item['insumo_id'], array_column($verificacion_stock['insuficientes'], 'insumo_id')) ? 'table-danger' : 'table-success'); ?>">
                                    <td><?php echo e($item['nombre']); ?></td>
                                    <td><?php echo e($item['cantidad_requerida']); ?></td>
                                    <td><?php echo e($item['stock_disponible']); ?></td>
                                    <td>
                                        <!--[if BLOCK]><![endif]--><?php if($item['stock_disponible'] >= $item['cantidad_requerida']): ?>
                                            <i class="bi bi-check-circle text-success"></i> OK
                                        <?php else: ?>
                                            <i class="bi bi-x-circle text-danger"></i> 
                                            Faltan <?php echo e($item['cantidad_requerida'] - $item['stock_disponible']); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalAprobar">
                        Cancelar
                    </button>
                    <!--[if BLOCK]><![endif]--><?php if($verificacion_stock && $verificacion_stock['puede_aprobar']): ?>
                        <button type="button" class="btn btn-success" wire:click="aprobarOrden">
                            <i class="bi bi-check-lg"></i> Aprobar Orden
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
    
    <!--[if BLOCK]><![endif]--><?php if($modal_completar && $orden_seleccionada): ?>
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Completar Mantenimiento #<?php echo e($orden_seleccionada->id); ?></h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalCompletar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Maquinaria:</strong> <?php echo e($orden_seleccionada->maquinaria->modelo); ?>

                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong> <?php echo e(ucfirst($orden_seleccionada->tipo_mantenimiento)); ?>

                        </div>
                        <div class="col-md-4">
                            <strong>Toneladas:</strong> <?php echo e(number_format($orden_seleccionada->toneladas_snapshot, 2)); ?>

                        </div>
                    </div>
                    
                    <h6>Insumos Utilizados:</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Stock Disponible</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos_usados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <select wire:model="insumos_usados.<?php echo e($index); ?>.insumo_id" 
                                                wire:change="actualizarInsumo(<?php echo e($index); ?>, $event.target.value)"
                                                class="form-select form-select-sm"
                                                <?php if($insumo['es_obligatorio']): ?> disabled <?php endif; ?>>
                                            <option value="">Seleccionar...</option>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos_disponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($ins->id_insumo); ?>"><?php echo e($ins->nombre); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </select>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ["insumos_usados.{$index}.insumo_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger small"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td>
                                        <input type="number" 
                                               wire:model="insumos_usados.<?php echo e($index); ?>.cantidad" 
                                               class="form-control form-control-sm" 
                                               step="0.01" 
                                               min="0">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ["insumos_usados.{$index}.cantidad"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger small"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td>
                                        <?php
                                            $stockDispo = $insumo['stock_disponible'] ?? 0;
                                            $cantidad = $insumo['cantidad'] ?? 0;
                                        ?>
                                        <span class="badge bg-<?php echo e($stockDispo >= $cantidad ? 'success' : 'danger'); ?>">
                                            <?php echo e(number_format($stockDispo, 2)); ?>

                                        </span>
                                        <!--[if BLOCK]><![endif]--><?php if($stockDispo < $cantidad && $cantidad > 0): ?>
                                            <br><small class="text-danger">Faltan <?php echo e(number_format($cantidad - $stockDispo, 2)); ?></small>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td>
                                        <!--[if BLOCK]><![endif]--><?php if(!$insumo['es_obligatorio']): ?>
                                            <button type="button" 
                                                    wire:click="eliminarInsumo(<?php echo e($index); ?>)" 
                                                    class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-info">Requerido</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>
                    
                    <button type="button" wire:click="agregarInsumo" class="btn btn-sm btn-outline-primary mb-3">
                        <i class="bi bi-plus-circle"></i> Agregar Insumo
                    </button>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Costo Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       wire:model="costo_mano_obra" 
                                       class="form-control" 
                                       step="0.01" 
                                       min="0">
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['costo_mano_obra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger small"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalCompletar">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="completarMantenimiento">
                        <i class="bi bi-flag-fill"></i> Completar Mantenimiento
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
    
    <!--[if BLOCK]><![endif]--><?php if($modal_detalle && $detalle_orden): ?>
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalle Orden #<?php echo e($detalle_orden->id); ?></h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalDetalle"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Maquinaria:</strong> <?php echo e($detalle_orden->maquinaria->modelo); ?><br>
                            <strong>Tipo Maquinaria:</strong> <?php echo e($detalle_orden->maquinaria->tipoMaquinaria->nombre); ?>

                        </div>
                        <div class="col-md-6">
                            <strong>Tipo Mantenimiento:</strong> <?php echo e(ucfirst($detalle_orden->tipo_mantenimiento)); ?><br>
                            <strong>Estado:</strong> 
                            <span class="badge bg-<?php echo e($detalle_orden->estado === 'completado' ? 'success' : 'primary'); ?>">
                                <?php echo e(ucfirst($detalle_orden->estado)); ?>

                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Toneladas Snapshot:</strong> <?php echo e(number_format($detalle_orden->toneladas_snapshot, 2)); ?>

                        </div>
                        <div class="col-md-6">
                            <strong>Fecha Creación:</strong> <?php echo e($detalle_orden->created_at->format('d/m/Y H:i')); ?>

                        </div>
                    </div>
                    
                    <!--[if BLOCK]><![endif]--><?php if($detalle_orden->estado === 'completado'): ?>
                        <hr>
                        <h6>Insumos Utilizados:</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $detalle_orden->mantenimientoInsumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->insumo->nombre); ?></td>
                                    <td><?php echo e($item->cantidad); ?></td>
                                    <td>$<?php echo e(number_format($item->costo_unitario, 2)); ?></td>
                                    <td>$<?php echo e(number_format($item->subtotal, 2)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Costo Mano de Obra:</strong> $<?php echo e(number_format($detalle_orden->costo_mano_obra, 2)); ?>

                            </div>
                            <div class="col-md-6">
                                <strong>Costo Total:</strong> 
                                <span class="h5 text-primary">
                                    $<?php echo e(number_format($detalle_orden->costo_total, 2)); ?>

                                </span>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Fecha Completado:</strong> <?php echo e($detalle_orden->fecha_completado->format('d/m/Y H:i')); ?>

                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    
                    <!--[if BLOCK]><![endif]--><?php if($detalle_orden->descripcion): ?>
                        <hr>
                        <strong>Descripción:</strong>
                        <p><?php echo e($detalle_orden->descripcion); ?></p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModalDetalle">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
</div>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/gestion-mantenimientos.blade.php ENDPATH**/ ?>