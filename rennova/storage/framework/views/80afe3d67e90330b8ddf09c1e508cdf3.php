<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tools"></i> Kits de Mantenimiento Preventivo</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="kitsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-kit" type="button" role="tab">
                <i class="bi bi-plus-circle"></i> Nuevo Kit
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-kits" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Listado de Kits
            </button>
        </li>
    </ul>

    <div class="tab-content" id="kitsTabContent">
        <!-- Tab 1: Formulario Nuevo Kit -->
        <div class="tab-pane fade" id="nuevo-kit" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-<?php echo e($kit_id ? 'pencil-square' : 'plus-circle'); ?>"></i> <?php echo e($kit_id ? 'Editar Kit' : 'Nuevo Kit'); ?></h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del Kit <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre_kit" class="form-control <?php $__errorArgs = ['nombre_kit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ej: Mantenimiento 500t - Pauny 230A">
                                <?php $__errorArgs = ['nombre_kit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo de Maquinaria <span class="text-danger">*</span></label>
                                <select wire:model="id_tipo_maquinaria" class="form-select <?php $__errorArgs = ['id_tipo_maquinaria'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un tipo</option>
                                    <?php $__currentLoopData = $tiposMaquinaria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tipo->id_tipo_maquinaria); ?>" wire:key="option-<?php echo e($tipo->id_tipo_maquinaria); ?>"><?php echo e($tipo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['id_tipo_maquinaria'];
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
                            <?php if($kit_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($kit_id ? 'Actualizar Kit' : 'Guardar Kit'); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab 2: Listado de Kits -->
        <div class="tab-pane fade show active" id="listado-kits" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por nombre o tipo de maquinaria...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Kit</th>
                                    <th>Tipo de Maquinaria</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $kits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr wire:key="row-<?php echo e($kit->id_kit_preventivo); ?>">
                                        <td><span class="badge bg-secondary"><?php echo e($kit->id_kit_preventivo); ?></span></td>
                                        <td><span class="fw-semibold"><?php echo e($kit->nombre_kit); ?></span></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="bi bi-gear-wide-connected"></i> <?php echo e($kit->tipoMaquinaria->nombre); ?>

                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($kit->id_kit_preventivo); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($kit->id_kit_preventivo); ?>)" onclick="return confirm('¿Está seguro de eliminar este kit?')" title="Baja Lógica">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="<?php echo e(route('kits.insumos', $kit->id_kit_preventivo)); ?>" class="btn btn-outline-success" title="Configurar Insumos">
                                                    <i class="bi bi-box-seam"></i> Insumos
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No hay kits registrados.</p>
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
        Livewire.on('kitGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/gestion-kit-preventivo.blade.php ENDPATH**/ ?>