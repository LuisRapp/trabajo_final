<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-rulers"></i> Unidades de Medida</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="unidadesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-unidades-medida', 'editar-unidades-medida'])): ?>
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-unidad" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nueva Unidad
            </button>
            <?php endif; ?>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-unidades" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Unidades
            </button>
        </li>
    </ul>

    <div class="tab-content" id="unidadesTabContent">
        <!-- Tab 1: Formulario Nueva Unidad -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-unidades-medida', 'editar-unidades-medida'])): ?>
        <div class="tab-pane fade" id="nuevo-unidad" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($unidad_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($unidad_id ? 'Editar Unidad' : 'Nueva Unidad'); ?></h5>
                </div>
                <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ej: Kilogramo">
                        <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Abreviatura <span class="text-danger">*</span></label>
                        <input type="text" wire:model="abreviatura" class="form-control <?php $__errorArgs = ['abreviatura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ej: kg">
                        <?php $__errorArgs = ['abreviatura'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <?php if($unidad_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-unidades-medida', 'editar-unidades-medida'])): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($unidad_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
        <?php endif; ?>

<!-- Tab 2: Listado de Unidades -->
<div class="tab-pane fade show active" id="listado-unidades" role="tabpanel">
    <div class="card shadow">
        <div class="card-body">
            <!-- Buscador -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre o abreviatura...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Abreviatura</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr wire:key="row-<?php echo e($unidad->id_unidad_medida); ?>">
                                <td><span class="badge bg-secondary"><?php echo e($unidad->id_unidad_medida); ?></span></td>
                                <td><span class="fw-semibold"><?php echo e($unidad->nombre); ?></span></td>
                                <td><span class="badge bg-info"><?php echo e($unidad->abreviatura); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-unidades-medida')): ?>
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($unidad->id_unidad_medida); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-unidades-medida')): ?>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($unidad->id_unidad_medida); ?>)" onclick="return confirm('¿Está seguro de eliminar esta unidad?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No hay unidades registradas.</p>
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

<!-- JavaScript para cambiar entre pestañas -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('unidadGuardada', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/unidades-medida.blade.php ENDPATH**/ ?>