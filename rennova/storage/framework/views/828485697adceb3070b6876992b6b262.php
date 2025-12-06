<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-calculator"></i> Liquidación de Pagos a Empleados
            </h4>
        </div>
        
        <div class="card-body">
            <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo e(session('message')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <?php if(session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if(!$mostrar_liquidacion): ?>
                
                <form wire:submit.prevent="calcularLiquidacion">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_empleado" class="form-label">Empleado *</label>
                                <select wire:model="id_empleado" id="id_empleado" class="form-select <?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un empleado</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($emp->id_empleado); ?>">
                                            <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio *</label>
                                <input type="date" wire:model="fecha_inicio" id="fecha_inicio" class="form-control <?php $__errorArgs = ['fecha_inicio'];
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
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin *</label>
                                <input type="date" wire:model="fecha_fin" id="fecha_fin" class="form-control <?php $__errorArgs = ['fecha_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-calculator"></i> Calcular
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5 class="mb-2">
                                <i class="fas fa-user"></i> 
                                <?php echo e($empleado_seleccionado->apellido); ?>, <?php echo e($empleado_seleccionado->nombre); ?>

                            </h5>
                            <p class="mb-0">
                                <strong>Rol:</strong> <?php echo e($empleado_seleccionado->rolLaboral->nombre ?? 'N/A'); ?> |
                                <strong>Período:</strong> <?php echo e(\Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y')); ?> a <?php echo e(\Carbon\Carbon::parse($fecha_fin)->format('d/m/Y')); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if(!$recibo_generado): ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Detalle del Cálculo</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            <tr>
                                                <td><strong>Días caídos trabajados:</strong></td>
                                                <td class="text-end"><?php echo e($calculo['cantidad_dias_caidos']); ?> días</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jornal diario:</strong></td>
                                                <td class="text-end">$<?php echo e(number_format($calculo['valor_jornal'], 2)); ?></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td><strong>Subtotal jornales:</strong></td>
                                                <td class="text-end"><strong>$<?php echo e(number_format($calculo['total_pagar_jornales'], 2)); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><hr class="my-2"></td>
                                            </tr>
                                       
                                            <tr>
                                                <td><strong>Toneladas producidas:</strong></td>
                                                <td class="text-end"><?php echo e(number_format($calculo['total_peso_toneladas'] ?? 0, 2)); ?> ton</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tarifa por tonelada:</strong></td>
                                                <td class="text-end">$<?php echo e(number_format($calculo['tarifa_fija_por_tonelada'], 2)); ?></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td><strong>Subtotal producción:</strong></td>
                                                <td class="text-end"><strong>$<?php echo e(number_format($calculo['total_pagar_produccion'], 2)); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><hr class="my-2"></td>
                                            </tr>
                                            <tr class="table-success">
                                                <td><strong>TOTAL CALCULADO:</strong></td>
                                                <td class="text-end"><h5 class="mb-0">$<?php echo e(number_format($calculo['total_pagar_final'], 2)); ?></h5></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            
                            <!--[if BLOCK]><![endif]--><?php if(count($adelantos_pendientes) > 0): ?>
                                <div class="card border-warning mt-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-hand-holding-usd"></i> Adelantos Pendientes</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th class="text-end">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $adelantos_pendientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adelanto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e(\Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y')); ?></td>
                                                        <td class="text-end">$<?php echo e(number_format($adelanto->monto, 2)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-warning">
                                                    <th>TOTAL ADELANTOS:</th>
                                                    <th class="text-end">$<?php echo e(number_format($total_adelantos, 2)); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small><i class="fas fa-info-circle"></i> Estos adelantos se descontarán automáticamente y se marcarán como "pagados" al generar el recibo.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-edit"></i> Datos del Recibo (Editable)</h6>
                                </div>
                                <div class="card-body">
                                    <form wire:submit.prevent="generarRecibo">
                                        <div class="mb-3">
                                            <label for="monto_bruto" class="form-label">Monto Bruto *</label>
                                            <input type="number" 
                                                   wire:model.live="monto_bruto" 
                                                   id="monto_bruto" 
                                                   step="0.1" min="0" 
                                                   class="form-control <?php $__errorArgs = ['monto_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['monto_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                            <div class="form-text">Puedes modificar el monto calculado si es necesario</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descuentos" class="form-label">
                                                Descuentos
                                                <!--[if BLOCK]><![endif]--><?php if($total_adelantos > 0): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-hand-holding-usd"></i> Incluye adelantos
                                                    </span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </label>
                                            <input type="number" 
                                                   wire:model.live="descuentos" 
                                                   id="descuentos" 
                                                   step="0.1" min="0" 
                                                   class="form-control <?php $__errorArgs = ['descuentos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['descuentos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                            <div class="form-text">
                                                <!--[if BLOCK]><![endif]--><?php if($total_adelantos > 0): ?>
                                                    Adelantos del período: $<?php echo e(number_format($total_adelantos, 2)); ?> (se marcarán como pagados)
                                                <?php else: ?>
                                                    Otros descuentos: retenciones, etc.
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Monto Neto a Pagar</label>
                                            <div class="alert alert-success mb-0">
                                                <h4 class="mb-0">$<?php echo e(number_format($monto_neto, 2)); ?></h4>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="observaciones" class="form-label">Observaciones</label>
                                            <textarea wire:model="observaciones" 
                                                      id="observaciones" 
                                                      rows="3" 
                                                      class="form-control <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                      maxlength="150"></textarea>
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-check-circle"></i> Generar Recibo
                                            </button>
                                            <button type="button" wire:click="nuevaLiquidacion" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success text-center py-5">
                                <i class="fas fa-check-circle fa-5x mb-3"></i>
                                <h3>¡Recibo generado correctamente!</h3>
                                <p class="lead mb-4">El pago ha sido registrado en el sistema.</p>
                                <button type="button" wire:click="nuevaLiquidacion" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Nueva Liquidación
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/liquidacion-pagos.blade.php ENDPATH**/ ?>