<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-truck"></i> Proveedores</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Pestañas (Tabs) -->
        <ul class="nav nav-tabs mb-4" id="proveedoresTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-proveedor" type="button" role="tab" aria-controls="nuevo-proveedor" aria-selected="true">
                    <i class="bi bi-plus-circle"></i> Nuevo Proveedor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-proveedores" type="button" role="tab" aria-controls="listado-proveedores" aria-selected="false">
                    <i class="bi bi-list-ul"></i> Listado de Proveedores
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="proveedoresTabContent">
            <!-- Pestaña 1: Nuevo Proveedor (Formulario) -->
            <div class="tab-pane fade show active" id="nuevo-proveedor" role="tabpanel" aria-labelledby="nuevo-tab">
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-<?php echo e($proveedor_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor'); ?></h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="guardar">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" wire:model="razon_social" class="form-control <?php $__errorArgs = ['razon_social'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Nombre del proveedor">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['razon_social'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CUIT <span class="text-danger">*</span></label>
                        <input type="text" wire:model="cuit" class="form-control <?php $__errorArgs = ['cuit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="XX-XXXXXXXX-X">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['cuit'];
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
                        <label class="form-label fw-semibold">Dirección</label>
                        <input type="text" wire:model="direccion" class="form-control <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Dirección completa">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" wire:model="telefono" class="form-control <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="+54 9 11 1234-5678">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" wire:model="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="correo@ejemplo.com">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
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
                    <!--[if BLOCK]><![endif]--><?php if($proveedor_id): ?>
                        <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <?php echo e($proveedor_id ? 'Actualizar' : 'Guardar'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
            </div>

            <!-- Pestaña 2: Listado de Proveedores (Tabla) -->
            <div class="tab-pane fade" id="listado-proveedores" role="tabpanel" aria-labelledby="listado-tab">
    <div class="card shadow">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Listado de Proveedores</h5>
                    </div>
        <div class="card-body">
                        <!-- Buscador -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por razón social, CUIT o email...">
                                </div>
                            </div>
                        </div>

            <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Razón Social</th>
                            <th>CUIT</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo e($proveedor->id_proveedor); ?></span></td>
                                <td><span class="fw-semibold"><?php echo e($proveedor->razon_social); ?></span></td>
                                <td><?php echo e($proveedor->cuit); ?></td>
                                <td><?php echo e($proveedor->direccion ?? '-'); ?></td>
                                <td><?php echo e($proveedor->telefono ?? '-'); ?></td>
                                <td><?php echo e($proveedor->email ?? '-'); ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($proveedor->id_proveedor); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($proveedor->id_proveedor); ?>)" onclick="return confirm('¿Está seguro de eliminar este proveedor?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No hay proveedores registrados.</p>
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

<!-- JavaScript para cambiar de pestaña al editar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('proveedorGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
</div>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/proveedores.blade.php ENDPATH**/ ?>