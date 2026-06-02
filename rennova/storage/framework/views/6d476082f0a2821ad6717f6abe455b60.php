<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-clock-history"></i> Histórico de Roles Laborales</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="historicoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-historico" type="button" role="tab" aria-controls="nuevo-historico" aria-selected="false">
                <i class="bi bi-plus-circle"></i> Nuevo Histórico
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-historicos" type="button" role="tab" aria-controls="listado-historicos" aria-selected="true">
                <i class="bi bi-list-ul"></i> Listado de Históricos
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="historicoTabContent">
        <!-- Pestaña 1: Nuevo Histórico (Formulario) -->
        <div class="tab-pane fade" id="nuevo-historico" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($historico_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($historico_id ? 'Modificar Histórico' : 'Nuevo Histórico'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rol Laboral <span class="text-danger">*</span></label>
                                <select wire:model="rol_laboral_id" class="form-select <?php $__errorArgs = ['rol_laboral_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un rol...</option>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($rol->id_rol_laboral); ?>"><?php echo e($rol->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['rol_laboral_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_inicio" class="form-control <?php $__errorArgs = ['fecha_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_inicio'];
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Precio por Tonelada</label>
                                <input type="number" wire:model="precio_tonelada" step="0.1" min="0" class="form-control <?php $__errorArgs = ['precio_tonelada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                <?php $__errorArgs = ['precio_tonelada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jornal Diario</label>
                                <input type="number" wire:model="jornal_diario" step="0.1" min="0" class="form-control <?php $__errorArgs = ['jornal_diario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                <?php $__errorArgs = ['jornal_diario'];
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Fin</label>
                                <input type="date" wire:model="fecha_fin" class="form-control <?php $__errorArgs = ['fecha_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted">Dejar vacío si el registro está vigente</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Motivo del Cambio</label>
                                <textarea wire:model="motivo_cambio" class="form-control <?php $__errorArgs = ['motivo_cambio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Motivo del cambio de precio" rows="3"></textarea>
                                <?php $__errorArgs = ['motivo_cambio'];
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
                            <?php if($historico_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($historico_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Históricos (Tabla) -->
        <div class="tab-pane fade show active" id="listado-historicos" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Históricos</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por rol, precio, jornal, motivo o fecha...">
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
                                    <th>Rol Laboral</th>
                                    <th>Precio/Ton</th>
                                    <th>Jornal Diario</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Motivo</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $historicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo e($historico->id); ?></span></td>
                                        <td><?php echo e($historico->rolLaboral->nombre ?? 'N/A'); ?></td>
                                        <td class="text-end">$<?php echo e(number_format($historico->precio_tonelada ?? 0, 2)); ?></td>
                                        <td class="text-end">$<?php echo e(number_format($historico->jornal_diario ?? 0, 2)); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($historico->fecha_inicio)->format('d/m/Y')); ?></td>
                                        <td>
                                            <?php if($historico->fecha_fin): ?>
                                                <?php echo e(\Carbon\Carbon::parse($historico->fecha_fin)->format('d/m/Y')); ?>

                                            <?php else: ?>
                                                <span class="badge bg-success">Vigente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($historico->motivo_cambio ? \Illuminate\Support\Str::limit($historico->motivo_cambio, 30) : 'N/A'); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($historico->id); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($historico->id); ?>)" onclick="return confirm('¿Está seguro de eliminar este histórico?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay históricos registrados.</p>
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
        Livewire.on('historicoGuardado', () => {
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
            
            // Detectar si hay un historico_id cargado (modo edición)
            if (component.fingerprint.name === 'historico-roles-laborales') {
                const isEditing = document.querySelector('form input[wire\\:model="historico_id"]')?.value;
                
                if (isEditing) {
                    nuevoTabButton.innerHTML = '<i class="bi bi-pencil-square"></i> Modificar Histórico';
                } else {
                    nuevoTabButton.innerHTML = '<i class="bi bi-plus-circle"></i> Nuevo Histórico';
                }
            }
        });
    });
</script><?php /**PATH D:\trabajo_final\rennova\resources\views\livewire\historico-roles-laborales.blade.php ENDPATH**/ ?>