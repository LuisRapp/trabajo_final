<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-badge"></i> Roles Laborales</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="rolesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-roles-laborales', 'editar-roles-laborales'])): ?>
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-rol" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Rol
            </button>
            <?php endif; ?>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-roles" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Roles
            </button>
        </li>
    </ul>

    <div class="tab-content" id="rolesTabContent">
        <!-- Tab 1: Formulario Nuevo Rol -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-roles-laborales', 'editar-roles-laborales'])): ?>
        <div class="tab-pane fade" id="nuevo-rol" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($rol_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($rol_id ? 'Editar Rol' : 'Nuevo Rol'); ?></h5>
                </div>
                <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" wire:model="nombre" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ej: Operario, Supervisor, Encargado">
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
                    <?php if($rol_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-roles-laborales', 'editar-roles-laborales'])): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($rol_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
        <?php endif; ?>

<!-- Tab 2: Listado de Roles -->
<div class="tab-pane fade show active" id="listado-roles" role="tabpanel">
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
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr wire:key="row-<?php echo e($rol->id_rol_laboral); ?>">
                                <td><span class="badge bg-secondary"><?php echo e($rol->id_rol_laboral); ?></span></td>
                                <td><span class="fw-semibold"><?php echo e($rol->nombre); ?></span></td>
                                <td>
                                    <?php if($rol->activo ?? true): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-roles-laborales')): ?>
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($rol->id_rol_laboral); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-roles-laborales')): ?>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($rol->id_rol_laboral); ?>)" onclick="return confirm('¿Está seguro de eliminar este rol?')" title="Eliminar">
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
                                    <p class="text-muted mt-2">No hay roles registrados.</p>
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
        Livewire.on('rolGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/roles-laborales.blade.php ENDPATH**/ ?>