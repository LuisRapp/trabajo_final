<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-cash-coin"></i> Adelantos</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="adelantosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-adelanto" type="button" role="tab" aria-controls="nuevo-adelanto" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Adelanto
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-adelantos" type="button" role="tab" aria-controls="listado-adelantos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Adelantos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="adelantosTabContent">
        <!-- Pestaña 1: Nuevo Adelanto (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-adelanto" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($adelanto_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($adelanto_id ? 'Editar Adelanto' : 'Nuevo Adelanto'); ?></h5>
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
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($empleado->id_empleado); ?>"><?php echo e($empleado->apellido); ?>, <?php echo e($empleado->nombre); ?></option>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="monto" step="0.1" min="0" class="form-control <?php $__errorArgs = ['monto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                        </div>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['monto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha de Adelanto <span class="text-danger">*</span></label>
                        <input type="date" wire:model="fecha_adelanto" class="form-control <?php $__errorArgs = ['fecha_adelanto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_adelanto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <!--[if BLOCK]><![endif]--><?php if($adelanto_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($adelanto_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Adelantos (Tabla) -->
        <div class="tab-pane fade" id="listado-adelantos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Adelantos</h5>
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
                            <th>Monto</th>
                            <th>Fecha Adelanto</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $adelantos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adelanto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo e($adelanto->id_adelanto); ?></span></td>
                                <td class="fw-semibold"><?php echo e($adelanto->empleado?->apellido ?? 'N/A'); ?>, <?php echo e($adelanto->empleado?->nombre ?? ''); ?></td>
                                <td>$<?php echo e(number_format($adelanto->monto, 2, ',', '.')); ?></td>
                                <td><?php echo e($adelanto->fecha_adelanto ? \Carbon\Carbon::parse($adelanto->fecha_adelanto)->format('d/m/Y') : 'N/A'); ?></td>
                                <td>
                                    <!--[if BLOCK]><![endif]--><?php if($adelanto->activo): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($adelanto->id_adelanto); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($adelanto->id_adelanto); ?>)" onclick="return confirm('¿Está seguro de eliminar este adelanto?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay adelantos registrados.</p>
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
        Livewire.on('adelantoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/adelantos.blade.php ENDPATH**/ ?>