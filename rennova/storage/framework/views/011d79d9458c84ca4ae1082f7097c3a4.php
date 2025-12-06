<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-badge"></i> Choferes</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="choferesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-chofer" type="button" role="tab" aria-controls="nuevo-chofer" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Chofer
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-choferes" type="button" role="tab" aria-controls="listado-choferes" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Choferes
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="choferesTabContent">
        <!-- Pestaña 1: Nuevo Chofer (Formulario) -->
        <div class="tab-pane fade show active" id="nuevo-chofer" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($chofer_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($chofer_id ? 'Modificar Chofer' : 'Nuevo Chofer'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                                <select class="form-select <?php $__errorArgs = ['id_cliente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="id_cliente">
                                    <option value="">Seleccione un cliente...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social ?? $cliente->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_cliente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <small class="text-muted">El chofer estará asociado a este cliente</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Apellido del chofer" wire:model="apellido">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['apellido'];
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Nombre del chofer" wire:model="nombre">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['dni'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="DNI del chofer" wire:model="dni">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dni'];
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Teléfono de contacto" wire:model="telefono">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Dirección del chofer" wire:model="direccion">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['direccion'];
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="estado" wire:model="estado">
                                    <label class="form-check-label" for="estado">
                                        <span class="badge <?php echo e($estado ? 'bg-success' : 'bg-secondary'); ?>"><?php echo e($estado ? 'Activo' : 'Inactivo'); ?></span>
                                    </label>
                                </div>
                                <small class="text-muted">Los choferes inactivos no aparecerán en los formularios de carga</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <!--[if BLOCK]><![endif]--><?php if($chofer_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($chofer_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Choferes (Tabla) -->
        <div class="tab-pane fade" id="listado-choferes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Choferes</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por cliente, apellido, nombre, DNI, teléfono o dirección...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Apellido y Nombre</th>
                                    <th>DNI</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $choferes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo e($c->id_chofer); ?></span></td>
                                        <td><?php echo e($c->cliente?->razon_social ?? $c->cliente?->nombre ?? 'Sin cliente'); ?></td>
                                        <td><?php echo e($c->apellido); ?>, <?php echo e($c->nombre); ?></td>
                                        <td><?php echo e($c->dni); ?></td>
                                        <td><?php echo e($c->telefono ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($c->estado ? 'success' : 'secondary'); ?>">
                                                <?php echo e($c->estado ? 'Activo' : 'Inactivo'); ?>

                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($c->id_chofer); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($c->id_chofer); ?>)" onclick="return confirm('¿Está seguro de eliminar este chofer?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay choferes registrados.</p>
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

<!-- JavaScript para cambiar de pestaña al editar -->
<script>
    function cambiarAPestanaFormulario() {
        // Activar la pestaña del formulario
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        
        // Scroll suave al inicio de la página
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Listener para volver a la pestaña de listado después de guardar
    document.addEventListener('livewire:init', () => {
        Livewire.on('choferGuardado', () => {
            // Cambiar a la pestaña de listado después de guardar
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            
            // Scroll al inicio
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Cambiar título del tab cuando se está editando
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('message.processed', (message, component) => {
            const nuevoTabButton = document.getElementById('nuevo-tab');
            
            // Detectar si hay un chofer_id cargado (modo edición)
            if (component.fingerprint.name === 'choferes') {
                const isEditing = document.querySelector('form input[wire\\:model="chofer_id"]')?.value;
                
                if (isEditing) {
                    nuevoTabButton.innerHTML = '<i class="bi bi-pencil-square"></i> Modificar Chofer';
                } else {
                    nuevoTabButton.innerHTML = '<i class="bi bi-plus-circle"></i> Nuevo Chofer';
                }
            }
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/choferes.blade.php ENDPATH**/ ?>