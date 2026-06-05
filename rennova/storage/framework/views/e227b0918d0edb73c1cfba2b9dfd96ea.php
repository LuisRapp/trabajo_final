<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-file-earmark-text"></i> Comprobantes de pago</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="recibosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-recibos', 'editar-recibos'])): ?>
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-recibo" type="button" role="tab" aria-controls="nuevo-recibo" aria-selected="false">
                <i class="bi bi-plus-circle"></i> Nuevo Comprobante
            </button>
            <?php endif; ?>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-recibos" type="button" role="tab" aria-controls="listado-recibos" aria-selected="true">
                <i class="bi bi-list-ul"></i> Listado de Comprobantes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="recibosTabContent">
        <!-- Pestaña 1: Nuevo Recibo (Formulario) -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-recibos', 'editar-recibos'])): ?>
        <div class="tab-pane fade" id="nuevo-recibo" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($recibo_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($recibo_id ? 'Editar Recibo' : 'Nuevo Recibo'); ?></h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                        <select wire:model="id_empleado" class="form-select <?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Seleccione...</option>
                            <?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($empleado->id_empleado); ?>" wire:key="option-<?php echo e($empleado->id_empleado); ?>"><?php echo e($empleado->apellido); ?>, <?php echo e($empleado->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha Emisión <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_emision" class="form-control <?php $__errorArgs = ['fecha_emision'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['fecha_emision'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <input type="text" wire:model="observaciones" class="form-control <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="150" placeholder="Observaciones">
                        <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto Bruto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto_bruto" step="0.1" min="0" class="form-control <?php $__errorArgs = ['monto_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                        </div>
                        <?php $__errorArgs = ['monto_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Descuentos</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="descuentos" step="0.1" min="0" class="form-control <?php $__errorArgs = ['descuentos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                        </div>
                        <?php $__errorArgs = ['descuentos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto Neto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto" step="0.1" min="0" class="form-control bg-light" placeholder="0.00" readonly>
                        </div>
                        <small class="text-muted">Se calcula automáticamente (Bruto - Descuentos)</small>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <?php if($recibo_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-recibos', 'editar-recibos'])): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($recibo_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                    <?php endif; ?>
                </div>
            </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pestaña 2: Listado de Recibos (Tabla) -->
        <div class="tab-pane fade show active" id="listado-recibos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Recibos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por empleado, monto o fecha...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Empleado</th>
                            <th>Fecha Emisión</th>
                            <th>Monto Bruto</th>
                            <th>Descuentos</th>
                            <th>Monto Neto</th>
                            <th>Observaciones</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recibos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recibo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr wire:key="row-<?php echo e($recibo->id_recibo); ?>">
                                <td><span class="badge bg-secondary"><?php echo e($recibo->id_recibo); ?></span></td>
                                <td class="fw-semibold"><?php echo e($recibo->empleado?->apellido ?? 'N/A'); ?>, <?php echo e($recibo->empleado?->nombre ?? ''); ?></td>
                                <td><?php echo e($recibo->fecha_emision ? \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') : 'N/A'); ?></td>
                                <td>$<?php echo e(number_format($recibo->monto_bruto, 2, ',', '.')); ?></td>
                                <td class="text-danger">$<?php echo e(number_format($recibo->descuentos, 2, ',', '.')); ?></td>
                                <td class="text-success fw-semibold">$<?php echo e(number_format($recibo->monto, 2, ',', '.')); ?></td>
                                <td><?php echo e($recibo->observaciones ?? '-'); ?></td>
                                <td>
                                    <?php if($recibo->activo): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-recibos')): ?>
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($recibo->id_recibo); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-recibos')): ?>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($recibo->id_recibo); ?>)" onclick="return confirm('¿Está seguro de eliminar este recibo?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay comprobantes registrados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para cambiar de pestaña al editar/guardar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('reciboGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/recibos.blade.php ENDPATH**/ ?>