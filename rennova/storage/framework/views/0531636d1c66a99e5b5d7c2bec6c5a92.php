<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-box-seam"></i> Insumos</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="insumosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-insumo" type="button" role="tab" aria-controls="nuevo-insumo" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Insumo
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-insumos" type="button" role="tab" aria-controls="listado-insumos" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Insumos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="insumosTabContent">
        <!-- Pestaña 1: Nuevo Insumo (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-insumo" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($insumo_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($insumo_id ? 'Editar Insumo' : 'Nuevo Insumo'); ?></h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Nombre del insumo">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unidad de Medida <span class="text-danger">*</span></label>
                        <select wire:model="id_unidad_medida" class="form-select <?php $__errorArgs = ['id_unidad_medida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Seleccione...</option>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unidad->id_unidad_medida); ?>"><?php echo e($unidad->nombre); ?> (<?php echo e($unidad->abreviatura); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_unidad_medida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Proveedor Principal <span class="text-danger">*</span></label>
                        <select wire:model="id_proveedor" class="form-select <?php $__errorArgs = ['id_proveedor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Seleccione...</option>
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
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea wire:model="descripcion" class="form-control <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Descripción del insumo" rows="1"></textarea>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Nota:</strong> El precio se calcula automáticamente usando el sistema FIFO basado en los lotes de stock. Gestione el stock en el módulo de <strong>Gestión de Stock</strong>.
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <!--[if BLOCK]><![endif]--><?php if($insumo_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($insumo_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Insumos (Tabla) -->
        <div class="tab-pane fade" id="listado-insumos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Insumos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre, descripción, proveedor, unidad o costo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Proveedor</th>
                            <th class="text-end">Stock Actual</th>
                            <th class="text-end">Precio Promedio</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo e($insumo->id_insumo); ?></span></td>
                                <td class="fw-semibold"><?php echo e($insumo->nombre); ?></td>
                                <td><?php echo e($insumo->descripcion ?? 'N/A'); ?></td>
                                <td>
                                    <!--[if BLOCK]><![endif]--><?php if($insumo->unidadMedida): ?>
                                        <?php echo e($insumo->unidadMedida->nombre); ?> <span class="badge bg-info"><?php echo e($insumo->unidadMedida->abreviatura); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td><?php echo e($insumo->proveedor?->razon_social ?? 'N/A'); ?></td>
                                <td class="text-end">
                                    <span class="badge <?php echo e($insumo->stock > 0 ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo e(number_format($insumo->stock, 2)); ?>

                                    </span>
                                </td>
                                <td class="text-end">
                                    <!--[if BLOCK]><![endif]--><?php if($insumo->precio_promedio > 0): ?>
                                        $<?php echo e(number_format($insumo->precio_promedio, 2, ',', '.')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($insumo->id_insumo); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($insumo->id_insumo); ?>)" onclick="return confirm('¿Está seguro de eliminar este insumo?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay insumos registrados.</p>
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
        Livewire.on('insumoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/insumos.blade.php ENDPATH**/ ?>