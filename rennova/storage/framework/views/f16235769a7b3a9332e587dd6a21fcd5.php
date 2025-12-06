<div class="container py-4">
    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="asignacionesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e($mostrar_historial ? 'active' : ''); ?>" 
                    id="historial-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#historial-asignaciones" 
                    type="button" 
                    role="tab"
                    wire:click="$set('mostrar_historial', true)">
                <i class="bi bi-list-ul"></i> Historial de Asignaciones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(!$mostrar_historial ? 'active' : ''); ?>" 
                    id="formulario-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#formulario-asignacion" 
                    type="button" 
                    role="tab"
                    wire:click="$set('mostrar_historial', false)">
                <i class="bi bi-<?php echo e($modo === 'editar' ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                <?php echo e($modo === 'editar' ? 'Modificar Asignación' : 'Nueva Asignación'); ?>

            </button>
        </li>
    </ul>

    <div class="tab-content" id="asignacionesTabContent">
        <!-- Pestaña 1: Historial de Asignaciones -->
        <div class="tab-pane fade <?php echo e($mostrar_historial ? 'show active' : ''); ?>" 
             id="historial-asignaciones" 
             role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Historial de Asignaciones por Lote</h5>
                    <button class="btn btn-primary btn-sm" wire:click="nuevaAsignacion">
                        <i class="bi bi-plus-circle"></i> Nueva Asignación
                    </button>
                </div>
                <div class="card-body">
                    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php if(session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Lote</th>
                                    <th>Estado</th>
                                    <th>Empleados Asignados</th>
                                    <th>Maquinarias Asignadas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong>Lote #<?php echo e($lote->id_lote); ?></strong><br>
                                            <small class="text-muted"><?php echo e($lote->ubicacion); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($lote->estado === 'activo' ? 'success' : ($lote->estado === 'terminado' ? 'secondary' : 'warning')); ?>">
                                                <?php echo e(ucfirst($lote->estado)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <!--[if BLOCK]><![endif]--><?php if($lote->empleados->count() > 0): ?>
                                                <small>
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lote->empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge bg-info me-1"><?php echo e($emp->apellido); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </small>
                                                <br><small class="text-muted">Total: <?php echo e($lote->empleados->count()); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Sin empleados</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td>
                                            <!--[if BLOCK]><![endif]--><?php if($lote->maquinarias->count() > 0): ?>
                                                <small>
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lote->maquinarias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge bg-primary me-1"><?php echo e($maq->modelo); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </small>
                                                <br><small class="text-muted">Total: <?php echo e($lote->maquinarias->count()); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Sin maquinarias</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" 
                                                        wire:click="editarAsignacion(<?php echo e($lote->id_lote); ?>)"
                                                        title="Modificar asignaciones">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!--[if BLOCK]><![endif]--><?php if($lote->estado !== 'terminado'): ?>
                                                    <button class="btn btn-outline-warning" 
                                                            wire:click="liberar(<?php echo e($lote->id_lote); ?>)"
                                                            onclick="return confirm('¿Marcar lote como terminado y liberar recursos?')"
                                                            title="Finalizar y liberar">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <button class="btn btn-outline-danger" 
                                                        wire:click="eliminarAsignacion(<?php echo e($lote->id_lote); ?>)"
                                                        onclick="return confirm('¿Eliminar todas las asignaciones de este lote?')"
                                                        title="Eliminar asignaciones">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay asignaciones registradas.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Formulario de Asignación -->
        <div class="tab-pane fade <?php echo e(!$mostrar_historial ? 'show active' : ''); ?>" 
             id="formulario-asignacion" 
             role="tabpanel">
            <div class="card shadow mb-4" id="formulario-asignacion-card">
                <div class="card-header bg-light d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($modo === 'editar' ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($modo === 'editar' ? 'Modificar Asignación' : 'Nueva Asignación'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php if(session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select class="form-select <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="id_lote">
                                <option value="">Seleccione un lote</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($l->id_lote); ?>">Lote #<?php echo e($l->id_lote); ?> - <?php echo e($l->ubicacion); ?> (<?php echo e($l->estado); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <small class="text-muted">Primero seleccione el Lote para ver y editar sus asignaciones.</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-people-fill"></i> Empleados asignados</strong>
                                    <!--[if BLOCK]><![endif]--><?php if($id_lote): ?>
                                        <span class="badge bg-light text-dark"><?php echo e(count($empleados_seleccionados)); ?> seleccionados</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="card-body">
                                    <!--[if BLOCK]><![endif]--><?php if($id_lote): ?>
                                        <div class="mb-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Buscar empleado..."
                                                   wire:model.live="busqueda_empleado">
                                        </div>
                                        <div style="max-height: 300px; overflow-y: auto;" class="border rounded p-2">
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           value="<?php echo e($emp->id_empleado); ?>" 
                                                           id="emp-<?php echo e($emp->id_empleado); ?>" 
                                                           wire:model.live="empleados_seleccionados">
                                                    <label class="form-check-label w-100" for="emp-<?php echo e($emp->id_empleado); ?>">
                                                        <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?>

                                                        <small class="text-muted">- <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <small class="text-muted">No se encontraron empleados.</small>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle"></i> Seleccione todos los empleados que trabajarán en este lote.
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            <small>Seleccione un Lote para habilitar esta sección.</small>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-truck"></i> Maquinarias asignadas</strong>
                                    <!--[if BLOCK]><![endif]--><?php if($id_lote): ?>
                                        <span class="badge bg-light text-dark"><?php echo e(count($maquinarias_seleccionadas)); ?> seleccionadas</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="card-body">
                                    <!--[if BLOCK]><![endif]--><?php if($id_lote): ?>
                                        <div class="mb-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Buscar maquinaria..."
                                                   wire:model.live="busqueda_maquinaria">
                                        </div>
                                        <div style="max-height: 300px; overflow-y: auto;" class="border rounded p-2">
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->maquinariasFiltrada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           value="<?php echo e($maq->id_maquinaria); ?>" 
                                                           id="maq-<?php echo e($maq->id_maquinaria); ?>" 
                                                           wire:model.live="maquinarias_seleccionadas">
                                                    <label class="form-check-label w-100" for="maq-<?php echo e($maq->id_maquinaria); ?>">
                                                        <?php echo e($maq->modelo); ?>

                                                        <small class="text-muted">- <?php echo e($maq->estado); ?> - <?php echo e($maq->tipoMaquinaria->nombre ?? 'N/A'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <small class="text-muted">No se encontraron maquinarias.</small>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small>
                                                <i class="bi bi-info-circle"></i> Si solo hay una maquinaria asignada al lote, se preseleccionará en el Parte Diario.
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            <small>Seleccione un Lote para habilitar esta sección.</small>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success" 
                                wire:click="guardar" 
                                wire:loading.attr="disabled" 
                                <?php if(!$id_lote): echo 'disabled'; endif; ?>>
                            <i class="bi bi-save"></i> Guardar asignaciones
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelar">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <div wire:loading wire:target="guardar" class="text-muted align-self-center">
                            <i class="bi bi-arrow-repeat"></i> Guardando...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            document.getElementById('formulario-asignacion-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/asignaciones-lote.blade.php ENDPATH**/ ?>