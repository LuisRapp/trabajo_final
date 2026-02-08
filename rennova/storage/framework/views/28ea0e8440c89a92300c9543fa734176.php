
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="bi bi-calendar-check text-primary"></i> Programar Mantenimiento
        </h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong><?php echo e($notificacion->titulo); ?></strong>
                                <p class="mb-0 mt-1 small"><?php echo e($notificacion->mensaje); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded p-3 h-100">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-tools"></i> Detalles del Mantenimiento
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <small class="text-muted">Maquinaria:</small>
                                <div class="fw-semibold"><?php echo e($mantenimiento->maquinaria->nombre ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Tipo:</small>
                                <div class="fw-semibold"><?php echo e($mantenimiento->tipoMantenimiento->nombre ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Estado:</small>
                                <div>
                                    <!--[if BLOCK]><![endif]--><?php if($mantenimiento->estado === 'programado'): ?>
                                        <span class="badge bg-info">Programado</span>
                                    <?php elseif($mantenimiento->estado === 'en curso'): ?>
                                        <span class="badge bg-warning">En Curso</span>
                                    <?php elseif($mantenimiento->estado === 'completado'): ?>
                                        <span class="badge bg-success">Completado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(ucfirst($mantenimiento->estado)); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Fecha de Inicio:</small>
                                <div class="fw-semibold"><?php echo e(\Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <form wire:submit.prevent="guardarFecha">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-6">
                        <label for="fechaProgramada" class="form-label fw-semibold">
                            <i class="bi bi-calendar3"></i> Fecha Programada <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="fechaProgramada"
                            class="form-control <?php $__errorArgs = ['fechaProgramada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            wire:model="fechaProgramada"
                            min="<?php echo e($fechaMinima); ?>"
                            max="<?php echo e($fechaMaxima); ?>"
                        >
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fechaProgramada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i>
                            La fecha debe estar dentro del rango permitido: 
                            <strong><?php echo e(\Carbon\Carbon::parse($fechaMinima)->format('d/m/Y')); ?></strong> 
                            a 
                            <strong><?php echo e(\Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y')); ?></strong>
                            (7 días desde la notificación)
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Confirmar y Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/programar-mantenimiento.blade.php ENDPATH**/ ?>