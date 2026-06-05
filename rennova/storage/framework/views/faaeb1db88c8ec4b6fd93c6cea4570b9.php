<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Tipos de Mantenimiento</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="tiposTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])): ?>
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-tipo" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Tipo
            </button>
            <?php endif; ?>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-tipos" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Tipos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tiposTabContent">
        <!-- Tab 1: Formulario Nuevo Tipo -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])): ?>
        <div class="tab-pane fade" id="nuevo-tipo" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($tipo_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($tipo_id ? 'Editar Tipo' : 'Nuevo Tipo'); ?></h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nombre del Tipo <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ej: Preventivo, Correctivo">
                                <?php $__errorArgs = ['nombre'];
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
                            <?php if($tipo_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'])): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($tipo_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tab 2: Listado de Tipos -->
        <div class="tab-pane fade show active" id="listado-tipos" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr wire:key="row-<?php echo e($tipo->id_tipo_mantenimiento); ?>">
                                        <td><span class="badge bg-secondary"><?php echo e($tipo->id_tipo_mantenimiento); ?></span></td>
                                        <td><span class="fw-semibold"><?php echo e($tipo->nombre); ?></span></td>
                                        <td>
                                            <?php if($tipo->activo): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-tipos-mantenimiento')): ?>
                                                <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($tipo->id_tipo_mantenimiento); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-tipos-mantenimiento')): ?>
                                                <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($tipo->id_tipo_mantenimiento); ?>)" onclick="return confirm('¿Está seguro de dar de baja este tipo?')" title="Dar de baja">
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
                                            <p class="text-muted mt-2">No hay tipos registrados.</p>
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
        Livewire.on('tipoGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/tipo-mantenimiento.blade.php ENDPATH**/ ?>